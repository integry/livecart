<?php

namespace product;

/**
 * Product price class. Prices can be entered in different currencies.
 * Each instance of ProductPrice determines product price in a particular currency.
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>getRecurringProductPeriodPrices
 */
class ProductPrice extends \ActiveRecordModel
{
	const TYPE_GENERAL_PRICE = 0;
	const TYPE_SETUP_PRICE = 1;
	const TYPE_PERIOD_PRICE = 2;

	public $ID;
	public $productID;
	public $currencyID;
	//public $recurringID", "RecurringProductPeriod", "ID", "RecurringProductPeriod;
	public $price;
	public $listPrice;
	public $type;
	public $serializedRules;

	public function initialize()
	{
		$cascade = array(
                'action' => \Phalcon\Mvc\Model\Relation::ACTION_CASCADE
            );
            
		$this->belongsTo('productID', 'product\Product', 'ID', array('foreignKey' => true, 'alias' => 'Product'));
		$this->belongsTo('currencyID', 'Currency', 'ID', array('foreignKey' => true, 'alias' => 'Currency'));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Product $product, \Currency $currency, $recurring = null, $type = 0)
	{
		$instance = new self();
		$instance->product = $product;
		$instance->currency = $currency;
		$instance->type = $type;
		if ($recurring != null)
		{
			$instance->recurring = $recurring;
		}
		return $instance;
	}

	public static function getInstance(Product $product, \Currency $currency, $recurring = null, $type = 0)
	{
		$filter = new ARSelectFilter();
		$cond = ProductPrice::query()->where('ProductPrice.productID = :ProductPrice.productID:', array('ProductPrice.productID' => $product->getID()))
					->andWhere('ProductPrice.currencyID = :ProductPrice.currencyID:', array('ProductPrice.currencyID' => $currency->getID()))
					->andWhere('ProductPrice.type = :ProductPrice.type:', array('ProductPrice.type' => $type));
					
		if ($recurring)
		{
			$cond->andWhere('ProductPrice.recurringID = :ProductPrice.recurringID:', array('ProductPrice.recurringID' => $recurring->getID()));
		}

		$set = $cond->execute();
		if ($set->count() > 0)
		{
			$instance = $set->current();
		}
		else
		{
			$instance = self::getNewInstance($product, $currency, $recurring, $type);
		}

		return $instance;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getPrice($applyRounding = true, $includeDiscounts = false)
	{
		$price = $this->price;

		if ($parent = $this->product->parent)
		{
			$parentPrice = $parent->getPricingHandler()->getPrice($this->currency)->getPrice();
			$price = $this->getChildPrice($parentPrice, $price, $this->product->getChildSetting('price'));
		}

		if ($includeDiscounts)
		{
			$price = self::getApplication()->getDisplayTaxPrice($price, $this->product);
			$price = self::getApplication()->getBusinessRuleController()->getProductPrice($this->product, $price);
		}

		if (!$price)
		{
			return null;
		}

		return $applyRounding ? $this->currency->roundPrice($price) : $price;
	}

	private function getChildPrice($parentPrice, $childPriceDiff, $setting)
	{
		if ($setting == Product::CHILD_ADD)
		{
			return $parentPrice + $childPriceDiff;
		}
		else if ($setting == Product::CHILD_SUBSTRACT)
		{
			return $parentPrice - $childPriceDiff;
		}
		else if ((float)$childPriceDiff)
		{
			return $childPriceDiff;
		}
		else
		{
			return $parentPrice;
		}
	}

	public function getItemPrice(OrderedItem $item, $applyRounding = true)
	{
		$price = $this->getPrice($applyRounding);
		$rules = is_array($this->serializedRules) ? $this->serializedRules : unserialize($this->serializedRules);

		if ($parent = $this->product->parent)
		{
			$priceSetting = $this->product->getChildSetting('price');
			$parentPrice = $parent->getPricingHandler()->getPrice($this->currency);

			if (!$rules)
			{
				$rules = unserialize($parentPrice->serializedRules);
			}

			if ($priceSetting !== Product::CHILD_OVERRIDE)
			{
				$price = $this->recalculatePrice();
			}
		}

		if ($price)
		{
			// quantity/group based prices
			if ($rules)
			{
				$user = $item->customerOrder->user;
				$groupID = ($user && $user->userGroup) ? $user->userGroup->getID() : 0;

				foreach (array($groupID, 0) as $group)
				{
					$p = $this->getGroupPrice($item, $group, $rules);

					if (!is_null($p))
					{
						return $p;
					}
				}
			}
		}

		// convert from default currency
		else if ($this->currency->getID() != self::getApplication()->getDefaultCurrencyCode())
		{
			$defaultCurrency = self::getApplication()->getDefaultCurrency();
			$price = $this->convertFromDefaultCurrency($this->product->getItemPrice($item, false, $defaultCurrency));
		}

		if ($price)
		{
			return $applyRounding ? $this->currency->roundPrice($price) : $price;
		}
		else
		{
			return 0;
		}
	}

	private function getGroupPrice(OrderedItem $item, $groupID, $rules)
	{
		$itemCnt = 0;

		// include other variations of the same product?
		if ($parent = $item->getProduct()->parent)
		{
			$order = $item->customerOrder;

			foreach ($order->getShoppingCartItems() as $orderItem)
			{
				if ($orderItem->isVariationDiscountsSummed())
				{
					$orderProduct = $orderItem->product->getParent();
					if ($orderProduct->getID() == $parent->getID())
					{
						$itemCnt += $orderItem->count;
					}
				}
			}
		}

		if (!$itemCnt)
		{
			$itemCnt = $item->count;
		}

		// include past orders
		$dateRange = $item->isPastOrdersInQuantityPrices();
		if (!is_null($dateRange))
		{
			$from = $dateRange ? strtotime('now -' . (int)$dateRange . ' days') : 0;
			$orders = $item->customerOrder->getBusinessRuleContext()->getPastOrdersBetween($from, time());
			$id = $item->getProduct()->getID();

			foreach ($orders as $order)
			{
				foreach ($order->getPurchasedItems() as $i)
				{
					$product = $i->getProduct();

					if ($id == $product['ID'])
					{
						$itemCnt += $i->getCount();
					}
				}
			}
		}

		return self::getProductGroupPrice($groupID, $rules, $itemCnt);
	}

	public function getPriceByGroup($groupID, $itemCnt = 1)
	{
		$rules = @unserialize($this->serializedRules);
		if (!$rules)
		{
			return $this->price;
		}

		$groupPrice = self::getProductGroupPrice($groupID, $rules, $itemCnt);
		return is_null($groupPrice) ? $this->price : $groupPrice;
	}

	public static function getProductGroupPrice($groupID, $rules, $itemCnt)
	{
		if (!is_array($rules))
		{
			return null;
		}

		$found = array();

		foreach ($rules as $quant => $prices)
		{
			if (isset($prices[$groupID]))
			{
				$found[$quant] = $prices[$groupID];
			}
		}

		$quantities = array_keys($found);
		sort($quantities);
		$cnt = count($quantities);

		for ($k = 0; $k < $cnt; $k++)
		{
			if ($quantities[$k] <= $itemCnt && (($k == $cnt - 1) || ($quantities[$k + 1] > $itemCnt)))
			{
				return $found[$quantities[$k]];
			}
		}

		return null;
	}

	public function reCalculatePrice()
	{
		$defaultCurrency = self::getApplication()->getDefaultCurrency();
		return $this->currency->roundPrice($this->convertFromDefaultCurrency($this->product->getPrice($defaultCurrency->getID(), Product::DO_NOT_RECALCULATE_PRICE)));
	}

	private function convertFromDefaultCurrency($price)
	{
		if ($this->currency->rate)
		{
			return $price / $this->currency->rate;
		}
		else
		{
			return 0;
		}
	}

	public function increasePriceByPercent($percentIncrease, $increaseQuantPrices = false)
	{
		return $this->multiplyPrice((100 + $percentIncrease) / 100, $increaseQuantPrices);
	}

	public function multiplyPrice($multiply, $increaseQuantPrices = false)
	{
		$this->price = $this->price * $multiply;

		if ($increaseQuantPrices)
		{
			$rules = unserialize($this->serializedRules);
			if (!$rules)
			{
				return;
			}

			foreach ($rules as &$groups)
			{
				foreach ($groups as &$price)
				{
					$price *= $multiply;
				}
			}

			$this->setRules($rules);
		}
	}

	public function dividePrice($divide, $increaseQuantPrices = false)
	{
		return $this->multiplyPrice(1 / $divide, $increaseQuantPrices);
	}

	public function setPriceRule($quantity, UserGroup $group = null, $price)
	{
		$rules = unserialize($this->serializedRules);
		$rules[$quantity][is_null($group) ? 0 : $group->getID()] = $price;
		$this->setRules($rules);
	}

	public function removePriceRule($quantity, UserGroup $group = null)
	{
		$rules = unserialize($this->serializedRules);
		unset($rules[$quantity][is_null($group) ? 0 : $group->getID()]);
		if (empty($rules[$quantity]))
		{
			unset($rules[$quantity]);
		}
		$this->setRules($rules);
	}

	public function getUserPrices(User $user = null)
	{
		$id = $this->getGroupId($user);
		$rules = is_array($this->serializedRules) ? $this->serializedRules : unserialize($this->serializedRules);
		$found = array();

		if (is_array($rules))
		{
			foreach ($rules as $quant => $prices)
			{
				if (isset($prices[$id]))
				{
					$found[$quant] = $prices[$id];
				}
			}
		}

		if ($id > 0 && !$found)
		{
			return $this->getUserPrices(null);
		}

		return $found;
	}

	private function getGroupId(User $user = null)
	{
		if (!$user)
		{
			return 0;
		}

		return is_null($user->userGroup) ? 0 : $user->userGroup->getID();
	}

	private function setRules($rules)
	{
		ksort($rules);
		$this->serializedRules = serialize($rules);
	}

	public static function calculatePrice(Product $product, \Currency $currency, $basePrice = null)
	{
		if (is_null($basePrice))
		{
			$defaultCurrency = self::getApplication()->getDefaultCurrencyCode();
			$basePrice = $product->getPrice($defaultCurrency, Product::DO_NOT_RECALCULATE_PRICE);
		}

		return self::convertPrice($currency, $basePrice);
	}

	public static function convertPrice(\Currency $currency, $basePrice)
	{
		$rate = (float)$currency->rate;
		if ($rate)
		{
			$price = $basePrice / $rate;
		}
		else
		{
			$price = 0;
		}

		$price = $currency->roundPrice($price);

		return $price;
	}

	/*####################  Instance retrieval ####################*/

	/**
	 * Load product pricing data for a whole array of products at once
	 */
	public static function loadPricesForRecordSetArray(&$productArray, $applyBusinessRules = true)
	{
		$ids = array();
		foreach ($productArray as $key => $product)
	  	{
			$ids[$product['ID']] = $key;
		}

		$prices = self::fetchPriceData(array_keys($ids));

		// sort by product
		$listPrice = $productPrices = $priceRules = array();
		foreach ($prices as $price)
		{
			$productPrices[$price['productID']][$price['currencyID']] = Currency::getInstanceByID($price['currencyID'])->roundPrice($price['price']);
			$listPrices[$price['productID']][$price['currencyID']] = $price['listPrice'];
			$productArray[$ids[$price['productID']]]['priceRules'][$price['currencyID']] = $price['serializedRules'];
			$productArray[$ids[$price['productID']]]['prices'][$price['currencyID']] = $price;
		}

		self::getPricesFromArray($productArray, $productPrices, $ids, false, $applyBusinessRules);
		if (isset($listPrices))
		{
			self::getPricesFromArray($productArray, $listPrices, $ids, true);
		}
	}

	private static function getPricesFromArray(&$productArray, $priceArray, $ids, $listPrice = false, $applyBusinessRules = true)
	{
		$baseCurrency = self::getApplication()->getDefaultCurrencyCode();
		$currencies = self::getApplication()->getCurrencySet();

		$priceField = $listPrice ? 'listPrice' : 'price';
		$formattedPriceField = $listPrice ? 'formattedListPrice' : 'formattedPrice';
		$priceSetting = null;

		foreach ($priceArray as $productId => $prices)
		{
			$product =& $productArray[$ids[$productId]];
			$rules = $product['priceRules'];

			// look for a parent product
			if (!empty($product['parentID']))
			{
				$parent = Product::getInstanceByID($product['parentID']);
				$settings = $product['childSettings'];
				if (isset($settings['price']))
				{
					$priceSetting = $settings['price'];
				}
			}
			else
			{
				$parent = null;
			}

			// apply discounts to display prices
			if (!$listPrice && $applyBusinessRules)
			{
				$ruleController = self::getApplication()->getBusinessRuleController();
				foreach ($prices as $currency => $price)
				{
					$maxPrice = $price;
					$groupPrice = self::getProductGroupPrice($ruleController->getContext()->getUserGroupID(), $rules[$currency], 1);
					$price = is_null($groupPrice) ? $price : $groupPrice;

					$price = self::getApplication()->getDisplayTaxPrice($price, $product);
					$maxPrice = self::getApplication()->getDisplayTaxPrice($maxPrice, $product);

					$prices[$currency] = $price;
					$discountedPrice = $ruleController->getProductPrice($product, $price, $currency);

					if ($discountedPrice != $maxPrice)
					{
						$product['definedListPrices'][$currency] = $maxPrice;
						$prices[$currency] = $discountedPrice;
					}
				}
			}

			$key = 'defined' . ($listPrice ? 'List' : '') . 'Prices';
			if (empty($product[$key]))
			{
				$product[$key] = array();
			}
			$product['original' . $key] = $prices;
			$product[$key] = array_merge($prices, $product[$key]);

			$prices =& $product[$key];

			foreach ($currencies as $id => $currency)
			{
				if (!isset($prices[$id]))
				{
					$prices[$id] = self::convertPrice($currency, isset($prices[$baseCurrency]) ? $prices[$baseCurrency] : 0);
				}
			}

			foreach ($prices as $id => $price)
			{
				if ((0 == (float)$price) && $listPrice)
				{
					continue;
				}

				if ($parent && (($priceSetting != Product::CHILD_OVERRIDE) || !$price))
				{
					$parentPrice = $parent->getPrice($id);
					$price = $parentPrice + ($price * (($priceSetting == Product::CHILD_ADD) ? 1 : -1));
				}

				$product[$priceField . '_' . $id] = $price;
				if (isset($currencies[$id]))
				{
					$product[$formattedPriceField][$id] = $currencies[$id]->getFormattedPrice($price);
				}
			}

			unset($prices);
		}
	}

	private static function fetchPriceData($productIDs)
	{
		if (!$productIDs)
		{
			return array();
		}

		$baseCurrency = self::getApplication()->getDefaultCurrencyCode();

		$filter = new ARSelectFilter(new INCond('ProductPrice.productID', $productIDs));
		$filter->orderBy(new ARExpressionHandle('currencyID = "' . $baseCurrency . '"'), 'DESC');
		return ActiveRecordModel::getRecordSetArray('ProductPrice', $filter);
	}

	/**
	 * Load product pricing data for a whole array of products at once
	 */
	public static function loadPricesForRecordSet(ARSet $products)
	{
		$set = ARSet::buildFromArray($products->getData());
		foreach ($products as $key => $product)
	  	{
			if ($product->parent)
			{
				$set->add($product->parent);
			}
		}

		$ids = array();
		foreach ($set as $key => $product)
	  	{
			$ids[$product->getID()] = $key;
		}

		$priceArray = self::fetchPriceData(array_flip($ids));

		$pricing = array();
		foreach ($priceArray as $price)
		{
			$pricing[$price['productID']][$price['currencyID']] = $price;
		}

		foreach ($pricing as $productID => $productPricing)
		{
			$product = $set->get($ids[$productID]);
			$product->loadPricing($productPricing);
		}
	}

	/**
	 * Get record set of product prices
	 *
	 * @param Product $product
	 *
	 * @return ARSet
	 */
	public static function getProductPricesSet(Product $product)
	{
		// preload currency data (otherwise prices would have to be loaded with referenced records)
		self::getApplication()->getCurrencySet();

		return self::getRecordSet(self::getProductPricesFilter($product));
	}

	/**
	 * Get product prices filter
	 *
	 * @param Product $product
	 *
	 * @return ARSelectFilter
	 */
	private static function getProductPricesFilter(Product $product)
	{

		return new ARSelectFilter(new EqualsCond(new ARFieldHandle(__CLASS__, 'productID'), $product->getID()));
	}

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);
		$currency = Currency::getInstanceByID($array['currencyID']);
		$array['serializedRules'] = @unserialize($array['serializedRules']);

		if ($array['serializedRules'] && !is_array($array['serializedRules']))
		{
			$array['serializedRules'] = array();
		}

		if ($array['serializedRules'] && is_array($array['serializedRules']))
		{
			$ruleController = self::getApplication()->getBusinessRuleController();
			$quantities = array_keys($array['serializedRules']);
			$nextQuant = array();
			foreach ($quantities as $key => $quant)
			{
				$nextQuant[$quant] = isset($quantities[$key + 1]) ? $quantities[$key + 1] - 1 : null;
			}

			foreach ($array['serializedRules'] as $quantity => $prices)
			{
				foreach ($prices as $group => $price)
				{
					$originalPrice = $currency->roundPrice($price);
					$product = isset($array['Product']) ? $array['Product'] : Product::getInstanceByID($array['productID']);
					$price = $ruleController->getProductPrice($product, $originalPrice);

					$array['quantityPrices'][$group][$quantity] = array(
														'originalPrice' => $originalPrice,
														'price' => $price,
														'originalFormattedPrice' => $currency->getFormattedPrice($originalPrice),
														'formattedPrice' => $currency->getFormattedPrice($price),
														'from' => $quantity,
														'to' => $nextQuant[$quantity]
													);
				}
			}
		}

		return $array;
	}

	public static function getRecurringProductPeriodPrices(RecurringProductPeriod $rpp, $currencyID = null)
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'recurringID'), $rpp->getID()));
		if ($currencyID != null && strlen($currencyID))
		{
			$filter->andWhere(new EqualsCond(new ARFieldHandle(__CLASS__, 'currencyID'), $currencyID ));
		}
		return parent::getRecordSet(__CLASS__, $filter);
	}
}

?>
