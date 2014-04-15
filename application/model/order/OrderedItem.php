<?php

namespace order;

use \product\Product;
use \Currency;
use \product\ProductOption;
use \product\ProductOptionChoice;

/**
 * Represents a shopping basket item (one or more instances of the same product)
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class OrderedItem extends \ActiveRecordModel //MultilingualObject implements BusinessRuleProductInterface
{
	protected $removedChoices = array();

	protected $subItems = null;

	protected $additionalCategories = array();

	protected $isVariationDiscountsSummed = false;

	protected $originalPrice = null;

	protected $itemPrice = null;

	/*
	 *  Possible values for isSavedForLater field
	 */
	const CART = 0;
	const WISHLIST = 1;
	const OUT_OF_STOCK = 2;

	public $ID;
//	public $parentID", "OrderedItem", "ID", "OrderedItem;
//	public $recurringParentID", "OrderedItem", "ID", "OrderedItem;
	public $price;
	public $count;
	public $reservedProductCount;
	public $dateAdded;
	public $isSavedForLater;
	public $name;

	public function initialize()
	{
		$this->belongsTo('customerOrderID', 'order\CustomerOrder', 'ID', array('alias' => 'CustomerOrder'));
		$this->belongsTo('shipmentID', 'order\Shipment', 'ID', array('alias' => 'Shipment'));
		$this->belongsTo('productID', 'product\Product', 'ID', array('alias' => 'Product'));
		$this->hasMany('ID', 'order\OrderedItemOption', 'orderedItemID', array('alias' => 'Options'));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(CustomerOrder $order, \product\Product $product, $count = 1)
	{
		$instance = new self();
		//$instance->customerOrder = $order;
		$instance->product = $product;
		$instance->count = $count;

		if ($order->isFinalized)
		{
			$instance->price = $instance->getItemPrice(false);
		}
		
		return $instance;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getCurrency()
	{
		if (!$this->customerOrder)
		{
			return $this->getDI()->get('application')->getDefaultCurrency();
		}
		else
		{
			return $this->customerOrder->currency;
		}
	}

	public function getSubTotal($includeTaxes = true, $applyDiscounts = true)
	{
		// bundle items do not affect order total - only the parent item has a set price
		/*
		if ($this->getParent())
		{
			return 0;
		}
		*/

		$subTotal = $this->getPrice($includeTaxes, false) * $this->count;

		if ($includeTaxes)
		{
			return $this->getCurrency()->round($subTotal);
		}
		else
		{
			return $subTotal;
		}
	}

	public function getSubTotalBeforeTax()
	{
		return $this->getSubTotal(false, true);
	}

	public function getPrice($includeTaxes = true, $round = true)
	{
		$price = $this->getPriceWithoutTax();

		if ($includeTaxes)
		{
			$price += $this->getPriceTax();
		}

		return $includeTaxes && $round ? $this->getCurrency()->roundPrice($price) : $price;
	}

	public function getPriceWithoutTax()
	{
		if (is_null($this->itemPrice))
		{
			$isFinalized = $this->customerOrder->isFinalized;
			$currency = $this->getCurrency();

			$price = $this->getItemPrice();
			$this->originalPrice = $price;

			foreach ($this->options as $choice)
			{
				if ($isFinalized && false)
				{
					//$optionPrice = $choice->priceDiff;
					$optionPrice = 0;
				}
				else
				{
					$optionPrice = $choice->choice->getPriceDiff($currency);
				}

				$price += $this->reduceBaseTaxes($optionPrice);
			}

			$this->itemPrice = $price;
		}

		return $this->itemPrice;
	}

	public function getOriginalPrice()
	{
		if (is_null($this->originalPrice))
		{
			$this->getPriceWithoutTax();
		}

		return $this->originalPrice;
	}

	public function setItemPrice($price)
	{
		if (!$this->customerOrder->isFinalized || !$this->itemPrice)
		{
			$price = $this->reduceBaseTaxes($price);
			$this->itemPrice = $price;
		}
	}

	/**
	 *  Avoid CustomerOrder::finalize function overriding the item price
	 *  Usually necessary for creating/updating orders via API, etc.
	 */
	public function setCustomPrice($price)
	{
		$this->isCustomPrice = true;
		$this->price = $price;
	}

	public function isCustomPrice()
	{
		return $this->isCustomPrice;
	}

	public function reset()
	{
		$this->itemPrice = null;
	}

	/**
	 *	Total tax amount for all products in line item
	 */
	public function getTaxAmount()
	{
		return $this->getPriceTax() * $this->count;
	}

	/**
	 *	Tax amount for one product
	 */
	private function getPriceTax($price = null)
	{
		if (is_null($price))
		{
			$price = $this->getPriceWithoutTax();
		}

		$basePrice = $price;

		foreach ($this->getTaxRates() as $rate)
		{
			$price = $price * (1 + ($rate->rate / 100));
		}

		return $price - $basePrice;
	}

	public function getTaxRates()
	{
		$class = $this->product->getParent()->taxClass;
		$rates = array();
		foreach ($this->customerOrder->getTaxZone()->getTaxRates() as $rate)
		{
			if ($rate->taxClass === $class)
			{
				$rates[] = $rate;
			}
		}

		return $rates;
	}

	public function getDisplayPrice(Currency $currency, $includeTaxes = true)
	{
		return $this->getPrice($includeTaxes);
	}

	// OrderedItem::getItemPrice() for recurring product return something ..different (setup [+first preriod price]) than value stored price field.
	// OrderedItem.price is used as base value for showing discounts.
	// Changed billing plan or adding item with recurring billing plan has nothing to do with discount, therefore there is need to update base price.
	public function updateBasePriceToCalculatedPrice()
	{
		$this->price = $this->getPrice(true);
		$this->save();
	}

	/**
	 *	Get price without taxes
	 */
	public function getItemPrice()
	{
		$order = $this->customerOrder;
		$isFinalized = $order->isFinalized;
		$product = $this->product;
		$price = 0;

		if ($product->type == Product::TYPE_RECURRING)
		{
			if ($order->parentID == null)
			{
				$recurringItem = RecurringItem::getInstanceByOrderedItem($this);
				$recurringBillingType = ActiveRecordModel::getApplication()->getConfig()->get('RECURRING_BILLING_TYPE');
				if ($recurringItem)
				{
					$price = $recurringItem->setupPrice;
					if ($recurringBillingType == 'RECURRING_BILLING_TYPE_PRE_PAY')
					{
						$price += $recurringItem->periodPrice; // pre pay, add price from first period
					}
				}
			}
			else // order is invoice for some other order
			{
				$recurringParent = $this->recurringParentID;
				if ($recurringParent)
				{
					$recurringItem = RecurringItem::getInstanceByOrderedItem($recurringParent);
					if ($recurringItem)
					{
						$price += $recurringItem->periodPrice;
					}
				}
			}
		}
		else
		{
			$price = $isFinalized ? $this->price : $this->product->getItemPrice($this);
		}

		if (!$isFinalized)
		{
			$price = $this->reduceBaseTaxes($price);
		}

		return $price;
	}

	public function reduceBaseTaxes($price, $product = null)
	{
		return $price;
		
		$product = $product ? $product : $this->product;
		if (!is_array($product))
		{
			$class = $product->getParent()->taxClass;
		}
		else
		{
			$product = empty($product['Parent']) ? $product : $product['Parent'];
			$class = empty($product['taxClassID']) ? null: TaxClass::getInstanceByID($product['taxClassID']);
		}

		foreach (DeliveryZone::getDefaultZoneInstance()->getTaxRates() as $rate)
		{
			if ($rate->taxClass === $class)
			{
				$price = $price / (1 + ($rate->rate / 100));
			}
		}

		return $price;
	}

	public function reserve($unreserve = false, Product $product = null)
	{
		$product = is_null($product) ? $this->product : $product;
		if (!$product->isBundle())
		{
			if ($product->isInventoryTracked() && !(!$unreserve && $this->reservedProductCount))
			{
				$this->reservedProductCount = $unreserve ? 0 : $this->count;
				$multiplier = $unreserve ? -1 : 1;
				$product->stockCount = $product->stockCount - ($this->count * $multiplier);
				$product->reservedCount = $product->reservedCount + ($this->count * $multiplier);
				$product->save();

				$this->event('reserve');
			}
		}
		else
		{
			foreach ($this->getSubItems() as $item)
			{
				if ($unreserve)
				{
					$item->unreserve();
				}
				else
				{
					$item->reserve();
				}
			}
		}
	}

	/**
	 * Release reserved products back to inventory
	 */
	public function unreserve()
	{
		if ($this->reservedProductCount > 0)
		{
			$this->reserve(true);
		}
	}

	/**
	 * Remove reserved products from inventory (i.e. the products are shipped)
	 */
	public function removeFromInventory()
	{
		$product = $this->product;
		if (!$product->isBundle())
		{
			$product->reservedCount = $product->reservedCount - $this->reservedProductCount;
			$this->reservedProductCount = 0;
			$this->event('removeFromInventory');
		}
		else
		{
			foreach ($this->getSubItems() as $item)
			{
				$item->removeFromInventory();
			}
		}
	}

	/**
	 *  Determine if the file download period hasn't expired yet
	 *
	 *  @return ProductFile
	 */
	public function isDownloadable(\product\ProductFile $file)
	{
		$allow = true;
		if ($file->allowDownloadDays)
		{
			$orderDate = $this->customerOrder->dateCompleted;
			if (!((abs($orderDate->getDayDifference(new DateTime())) <= $file->allowDownloadDays) ||
				!$file->allowDownloadDays))
			{
				$allow = false;
			}
		}

		if ($file->allowDownloadCount)
		{
			$orderFile = OrderedFile::getInstance($this, $file);
			if ($orderFile->timesDownloaded > $file->allowDownloadCount + 1)
			{
				$allow = false;
			}
		}

		return $allow;
	}

	private function isFinalized()
	{
		return $this->customerOrder->isFinalized;
	}

	public function removeOption(\product\ProductOption $option)
	{
		foreach ($this->optionChoices as $key => $ch)
		{
			if ($ch->choice->option->getID() == $option->getID())
			{
				$this->removeOptionChoice($ch);
			}
		}
	}

	public function removeOptionChoice(\product\ProductOptionChoice $choice)
	{
		foreach ($this->optionChoices as $key => $ch)
		{
			if ($ch->choice->getID() == $choice->getID())
			{
				$this->removedChoices[] = $ch;
				unset($this->optionChoices[$key]);

				if ($this->isFinalized())
				{
					$this->price = $this->price - $this->reduceBaseTaxes($choice->priceDiff);
				}
			}
		}
	}

	public function addOption(\product\ProductOption $option)
	{
		return $this->addOptionChoice($option->defaultChoice);
	}

	public function addOptionChoice(\product\ProductOptionChoice $choice)
	{
		foreach ($this->optionChoices as $key => $ch)
		{
			// already added?
			if ($ch->choice->getID() == $choice->getID())
			{
				return $ch;
			}

			// other choice from the same option - needs removal
			if ($ch->choice->option->getID() == $choice->option->getID())
			{
				$this->removeOptionChoice($ch->choice);
			}
		}

		$choice = OrderedItemOption::getNewInstance($this, $choice);

		$this->optionChoices[$choice->choice->option->getID()] = $choice;

		if ($this->isFinalized())
		{
			$choice->updatePriceDiff();
			$this->price = $this->price + $this->reduceBaseTaxes($choice->priceDiff);
		}

		return $choice;
	}

	public function loadOption(OrderedItemOption $option)
	{
		$this->optionChoices[$option->choice->option->getID()] = $option;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function loadOptions()
	{
		foreach ($this->getRelatedRecordSet('OrderedItemOption', new ARSelectFilter(), array('ProductOptionChoice')) as $option)
		{
			$this->optionChoices[$option->choice->option->getID()] = $option;
		}

		if ($this->product->parent)
		{
			$this->product->parent->load();
		}
	}

	public function getOptionChoice(\product\ProductOption $option)
	{
		foreach ($this->optionChoices as $choice)
		{
			if ($choice->choice->option->getID() == $option->getID())
			{
				return $choice;
			}
		}
	}

	public function getCount()
	{
		return $this->count;
	}
	
	public function setCount($count)
	{
		$this->count = $count;
	}

	public function loadData($item)
	{
		$this->setCount($item['count']);
		
		$existing = $this->options;
		if (!empty($item['options']))
		{
			foreach ($item['options'] as $id => $option)
			{
				$found = false;
				foreach ($existing as $opt)
				{
					if ($opt->choice->productOption->getID() == $id)
					{
						$found = true;
						break;
					}
				}
				
				if (!$found)
				{
					$productOption = ProductOption::getInstanceByID($id);
					$opt = OrderedItemOption::getNewInstance($this, $productOption->choices->getFirst());
				}
				
				$opt->loadData($option);
				$opt->orderedItem = $this;
				$opt->orderedItemID = $this->getID();
				
				if ($opt->getID())
				{
					$opt->save();
				}
				
				$this->options = [$opt];
			}
		}
		
		//var_dump($this->options->toArray());
	}

	public function getSubItems()
	{
		if (!$this->product->isBundle())
		{
			return null;
		}

		if (is_null($this->subItems))
		{
			$this->subItems = $this->getRelatedRecordSet('OrderedItem', new ARSelectFilter(), array('Product'));
		}

		return $this->subItems;
	}

	public function registerSubItem(OrderedItem $item)
	{
		if (is_null($this->subItems))
		{
			$this->subItems = new ARSet();
		}

		$id = $item->getID();
		foreach ($this->subItems as $subItem)
		{
			if ($subItem->getID() == $id)
			{
				return false;
			}
		}

		$this->subItems->add($item);
	}

	public function registerAdditionalCategory(Category $category)
	{
		$this->additionalCategories[$category->getID()] = $category;
	}

	public function getAdditionalCategories()
	{
		return $this->additionalCategories;
	}

	/**
	 *	Include other variations of the same parent product when determining the quantity price level
	 */
	public function setSumVariationDiscounts($sum = true)
	{
		$this->isVariationDiscountsSummed = $sum;
	}

	public function isVariationDiscountsSummed()
	{
		return $this->isVariationDiscountsSummed;
	}

	/**
	 *	Include past orders in quantity prices
	 */
	public function setPastOrdersInQuantityPrices($dateRange)
	{
		if (!$dateRange)
		{
			$dateRange = '';
		}

		$this->pastOrdersInQuantityPrices = $dateRange;
	}

	public function isPastOrdersInQuantityPrices()
	{
		return $this->pastOrdersInQuantityPrices;
	}

  	/*####################  Saving ####################*/

	public function xbeforeCreate()
	{
		return;
		
		if ($this->shipment && !$this->shipment->isExistingRecord())
		{
			$this->shipment = null;
		}

		if (!$this->price)
		{
			$this->price = $this->product->getItemPrice($this);
		}


	}

	public function xbeforeSave()
	{
		var_dump('saving');
		return;
		
		// update inventory
		$shipment = $this->shipment;
		if (!$shipment && $this->parent)
		{
			$shipment = $this->parent->shipment;
		}

		$order = $this->customerOrder;

		if ($shipment && $order->isFinalized && !$order->isCancelled && self::getApplication()->isInventoryTracking())
		{
			$product = $this->product;

			// changed product (usually a different variation)
			if ($this->hasChanged('product'))
			{
				// unreserve original item
				if ($orig = $this->product->getInitialValue())
				{
					if (is_string($orig))
					{
						$orig = Product::getInstanceById($orig, true);
					}

					$this->reserve(true, $orig);
					$orig->save();
				}

				// reserve new item
				$this->reserve();
			}

			if (($this->reservedProductCount > 0) && ($shipment->status == Shipment::STATUS_SHIPPED))
			{
				$this->removeFromInventory();
			}
			else if (0 == $this->reservedProductCount)
			{
				if ($shipment->status == Shipment::STATUS_RETURNED)
				{
					$this->reservedProductCount = $this->count;
					$product->reservedCount = $product->reservedCount + $this->count;
				}
				else
				{
					$this->reserve();
				}
			}
			else if ($this->hasChanged('count'))
			{
				$delta = $this->count - $this->reservedProductCount;
				$this->reservedProductCount = $this->count;
				$product->reservedCount = $product->reservedCount + $delta;
				$product->stockCount = $product->stockCount - $delta;
			}
		}
	}

	public function xafterSave()
	{
		return;
		// save options
		foreach ($this->removedChoices as $rem)
		{
			$rem->delete();
		}

		foreach ($this->optionChoices as $choice)
		{
			$choice->save();
		}

		// save sub-items for bundles
		if ($this->product->isBundle())
		{
			foreach ($this->getSubItems() as $item)
			{
				$item->save();
			}
		}

		$this->product->save();
		$this->subItems = null;
	}

	public function beforeSave()
	{
		$this->price = $this->product->getItemPrice($this);
	}

	public function afterSave()
	{
		//var_dump($this->productID);
		/*
		if (!$this->price)
		{
			$this->price = $this->product->getItemPrice($this);
			$this->save();
		}
		*/
	}

	/*####################  Data array transformation ####################*/

	public function toArray()
	{
		$order = $this->customerOrder;
		if (!$order)
		{
			$order = new CustomerOrder();
		}
		
		$array = parent::toArray();
		$array['Product'] = $this->product->toArray();
	
		$array['priceCurrencyID'] = $this->getCurrency()->getID();
		$isTaxIncludedInPrice = $order->getTaxZone()->isTaxIncludedInPrice();

		if (isset($array['price']))
		{
			$currency = $this->getCurrency();

			$array['itemBasePrice'] = (float)self::getApplication()->getDisplayTaxPrice($array['price'], isset($array['Product']) ? $array['Product'] : array());;
			$array['displayPrice'] = (float)$this->getDisplayPrice($currency, $isTaxIncludedInPrice);
			$array['displayPriceWithoutTax'] = (float)$this->getItemPrice();
			$array['displaySubTotalWithoutTax'] = (float)$this->getSubTotalBeforeTax();
			$array['displaySubTotal'] = (float)$this->getSubTotal($isTaxIncludedInPrice);
			$array['itemPrice'] = $array['displaySubTotal'] / $array['count'];

			$isTaxIncludedInPrice = $isTaxIncludedInPrice || (($array['itemPrice'] != $array['itemBasePrice']) && ($array['itemPrice'] == $this->getSubTotal(false)) && ($array['itemBasePrice'] == $this->getSubTotal(true)));

			// display price changed by tax exclusion
			if (((string)$array['itemPrice'] != (string)$array['itemBasePrice']) && ((string)$array['itemPrice'] == (string)($this->getSubTotal(false) / $array['count'])) && ((string)$array['itemBasePrice'] == (string)($this->getSubTotal(true) / $array['count'])))
			{
				$array['itemPrice'] = $array['itemBasePrice'];
			}

			// kind of a workaround for completed orders that have default zone taxes, could be a better fix
			if (((string)($this->getSubTotal(false) / $array['count']) == (string)$array['itemBasePrice']) && ((string)($this->getSubTotal(true) / $array['count']) == (string)$array['itemPrice']))
			{
				$array['itemBasePrice'] = $array['itemPrice'];
			}

			if ($this->options && !$this->customerOrder->isFinalized)
			{
				foreach ($this->options as $choice)
				{
					$array['itemBasePrice'] += $choice->choice->getPriceDiff($this->getCurrency());
				}
			}

			$array['formattedBasePrice'] = $currency->getFormattedPrice($array['itemBasePrice']);
			$array['formattedPrice'] = $currency->getFormattedPrice($array['itemPrice']);
			$array['formattedDisplayPrice'] = $currency->getFormattedPrice($array['displayPrice']);
			$array['formattedDisplaySubTotal'] = $currency->getFormattedPrice($array['displaySubTotal']);
			$array['formattedPriceWithoutTax'] = $currency->getFormattedPrice($array['displayPriceWithoutTax']);
			$array['formattedSubTotalWithoutTax'] = $currency->getFormattedPrice($array['displaySubTotalWithoutTax']);
		}

		$array['options'] = array();
		foreach ($this->options as $choice)
		{
			$array['options'][$choice->choice->optionID] = $choice->toArray();
		}

		return $array;

		$array['subItems'] = array();
		if ($this->subItems)
		{
			foreach ($this->subItems as $subItem)
			{
				$array['subItems'][] = $subItem->toArray();
			}
		}
		if ($array && is_array($array) && array_key_exists('Product',$array) && array_key_exists('type', $array['Product']) && $array['Product']['type'] == Product::TYPE_RECURRING)
		{
			$ritemArray = RecurringItem::getRecordSetArrayByOrderedItem($this);
			if (count($ritemArray)) // should be 1 or 0
			{
				$array['recurringID'] = $ritemArray[0]['recurringID'];
			}
		}

		return $array;
	}

	/*####################  Get related objects ####################*/

	/**
	 *  @return ProductFile
	 */
	public function getFileByID($id)
	{
		$f = query::query()->where('ProductFile.ID = :ProductFile.ID:', array('ProductFile.ID' => $id));
		$f->andWhere('ProductFile.productID = :ProductFile.productID:', array('ProductFile.productID' => $this->product->getID()));
		$s = ActiveRecordModel::getRecordSet('ProductFile', $f);
		if (!$s->count())
		{
			return false;
		}
		else
		{
			return $s->shift();
		}
	}

	public function serialize()
	{
		$this->markAsLoaded();
		return parent::serialize(array('customerOrderID', 'shipmentID', 'productID'));
	}

/*
	public function delete()
	{
		$ri = RecurringItem::getInstanceByOrderedItem($this);
		if ($ri)
		{
			$ri->delete();
		}
		parent::delete();
	}
*/

	/*
	public function __clone()
	{
		parent::__clone();

		foreach ($this->optionChoices as $key => $option)
		{
			$newOpt = clone $option;
			$newOpt->orderedItem = $this;
			$newOpt->choice->setAsModified();
			$this->optionChoices[$key] = $newOpt;
		}
	}
	*/
}

?>
