<?php

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.order.Shipment");
ClassLoader::import("application.model.order.OrderedFile");
ClassLoader::import('application.model.order.OrderedItemOption');
ClassLoader::import('application.model.delivery.DeliveryZone');
ClassLoader::import('application.model.businessrule.interface.BusinessRuleProductInterface');
ClassLoader::import("application.model.system.MultilingualObject");

/**
 * Represents a shopping basket item (one or more instances of the same product)
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class OrderedItem extends MultilingualObject implements BusinessRuleProductInterface
{
	protected $optionChoices = array();

	protected $removedChoices = array();

	protected $subItems = null;

	protected $additionalCategories = array();

	protected $isVariationDiscountsSummed = false;

	protected $originalPrice = null;

	/*
	 *  Possible values for isSavedForLater field
	 */
	const CART = 0;
	const WISHLIST = 1;
	const OUT_OF_STOCK = 2;

	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("customerOrderID", "CustomerOrder", "ID", "CustomerOrder", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shipmentID", "Shipment", "ID", "Shipment", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("parentID", "OrderedItem", "ID", "OrderedItem", ARInteger::instance()));

		$schema->registerField(new ARField("price", ARFloat::instance()));
		$schema->registerField(new ARField("count", ARFloat::instance()));
		$schema->registerField(new ARField("reservedProductCount", ARFloat::instance()));
		$schema->registerField(new ARField("dateAdded", ARDateTime::instance()));
		$schema->registerField(new ARField("isSavedForLater", ARInteger::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(CustomerOrder $order, Product $product, $count = 1)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->customerOrder->set($order);
		$instance->product->set($product);
		$instance->count->set($count);

		if ($order->isFinalized->get())
		{
			$instance->price->set($instance->getItemPrice(false));
		}

		return $instance;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getCurrency()
	{
		return $this->customerOrder->get()->getCurrency();
	}

	public function getSubTotal($includeTaxes = true, $applyDiscounts = true)
	{
		$currency = $this->getCurrency();

		// bundle items do not affect order total - only the parent item has a set price
		if ($this->parent->get())
		{
			return 0;
		}

		$subTotal = $this->getPrice($includeTaxes, false) * $this->count->get();

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
			$isFinalized = $this->customerOrder->get()->isFinalized->get();
			$currency = $this->getCurrency();

			$price = $this->getItemPrice();
			$this->originalPrice = $price;

			foreach ($this->optionChoices as $choice)
			{
				if ($isFinalized)
				{
					$optionPrice = $choice->priceDiff->get();
				}
				else
				{
					$optionPrice = $choice->choice->get()->getPriceDiff($currency->getID());
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
		if (!$this->customerOrder->get()->isFinalized->get() || !$this->itemPrice)
		{
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
		$this->price->set($price);
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
		return $this->getPriceTax() * $this->count->get();
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
			$price = $price * (1 + ($rate->rate->get() / 100));
		}

		return $price - $basePrice;
	}

	public function getTaxRates()
	{
		$class = $this->getProduct()->getParent()->taxClass->get();
		$rates = array();
		foreach ($this->customerOrder->get()->getDeliveryZone()->getTaxRates() as $rate)
		{
			if ($rate->taxClass->get() === $class)
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

	/**
	 *	Get price without taxes
	 */
	public function getItemPrice()
	{
		$isFinalized = $this->customerOrder->get()->isFinalized->get();
		$price = $isFinalized ? $this->price->get() : $this->getProduct()->getItemPrice($this);

		if (!$isFinalized)
		{
			$price = $this->reduceBaseTaxes($price);
		}

		return $price;
	}

	public function reduceBaseTaxes($price, $product = null)
	{
		$product = $product ? $product : $this->getProduct();
		if (!is_array($product))
		{
			$class = $product->getParent()->taxClass->get();
		}
		else
		{
			$product = empty($product['Parent']) ? $product : $product['Parent'];
			$class = empty($product['taxClassID']) ? null: ActiveRecordModel::getInstanceById('TaxClass', $product['taxClassID']);
		}

		foreach (DeliveryZone::getDefaultZoneInstance()->getTaxRates() as $rate)
		{
			if ($rate->taxClass->get() === $class)
			{
				$price = $price / (1 + ($rate->rate->get() / 100));
			}
		}

		return $price;
	}

	public function reserve($unreserve = false, Product $product = null)
	{
		$product = is_null($product) ? $this->getProduct() : $product;
		if (!$product->isBundle())
		{
			if ($product->isInventoryTracked() && !(!$unreserve && $this->reservedProductCount->get()))
			{
				$this->reservedProductCount->set($unreserve ? 0 : $this->count->get());
				$multiplier = $unreserve ? -1 : 1;
				$product->stockCount->set($product->stockCount->get() - ($this->count->get() * $multiplier));
				$product->reservedCount->set($product->reservedCount->get() + ($this->count->get() * $multiplier));
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
		if ($this->reservedProductCount->get() > 0)
		{
			$this->reserve(true);
		}
	}

	/**
	 * Remove reserved products from inventory (i.e. the products are shipped)
	 */
	public function removeFromInventory()
	{
		$product = $this->getProduct();
		if (!$product->isBundle())
		{
			$product->reservedCount->set($product->reservedCount->get() - $this->reservedProductCount->get());
			$this->reservedProductCount->set(0);
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
	public function isDownloadable(ProductFile $file)
	{
		$allow = true;
		if ($file->allowDownloadDays->get())
		{
			$orderDate = $this->customerOrder->get()->dateCompleted->get();
			if (!((abs($orderDate->getDayDifference(new DateTime())) <= $file->allowDownloadDays->get()) ||
				!$file->allowDownloadDays->get()))
			{
				$allow = false;
			}
		}

		if ($file->allowDownloadCount->get())
		{
			$orderFile = OrderedFile::getInstance($this, $file);
			if ($orderFile->timesDownloaded->get() > $file->allowDownloadCount->get() + 1)
			{
				$allow = false;
			}
		}

		return $allow;
	}

	public function removeOption(ProductOption $option)
	{
		foreach ($this->optionChoices as $key => $ch)
		{
			if ($ch->choice->get()->option->get()->getID() == $option->getID())
			{
				$this->removedChoices[] = $ch;
				unset($this->optionChoices[$key]);
			}
		}
	}

	public function removeOptionChoice(ProductOptionChoice $choice)
	{
		foreach ($this->optionChoices as $key => $ch)
		{
			if ($ch->choice->get()->getID() == $choice->getID())
			{
				$this->removedChoices[] = $ch;
				unset($this->optionChoices[$key]);
			}
		}
	}

	public function addOption(ProductOption $option)
	{
		return $this->addOptionChoice($option->defaultChoice->get());
	}

	public function addOptionChoice(ProductOptionChoice $choice)
	{
		if (!$choice->isLoaded())
		{
			$choice->load();
		}

		foreach ($this->optionChoices as $key => $ch)
		{
			// already added?
			if ($ch->choice->get()->getID() == $choice->getID())
			{
				return $ch;
			}

			// other choice from the same option - needs removal
			if ($ch->choice->get()->option->get()->getID() == $choice->option->get()->getID())
			{
				$this->removedChoices[] = $ch;
				unset($this->optionChoices[$key]);
			}
		}

		$choice = OrderedItemOption::getNewInstance($this, $choice);

		$this->optionChoices[$choice->choice->get()->option->get()->getID()] = $choice;

		return $choice;
	}

	public function loadOption(OrderedItemOption $option)
	{
		$this->optionChoices[$option->choice->get()->option->get()->getID()] = $option;
	}

	public function getOptions()
	{
		return $this->optionChoices;
	}

	public function loadOptions()
	{
		foreach ($this->getRelatedRecordSet('OrderedItemOption', new ARSelectFilter(), array('ProductOptionChoice')) as $option)
		{
			$this->optionChoices[$option->choice->get()->option->get()->getID()] = $option;
		}

		if ($this->getProduct()->parent->get())
		{
			$this->getProduct()->parent->get()->load();
		}
	}

	public function getOptionChoice(ProductOption $option)
	{
		foreach ($this->optionChoices as $choice)
		{
			if ($choice->choice->get()->option->get()->getID() == $option->getID())
			{
				return $choice;
			}
		}
	}

	public function getProduct()
	{
		$product = $this->product->get();
		if (!$product)
		{
			$product = ActiveRecordModel::getNewInstance('Product');
			$product->setID(0);
			$product->markAsLoaded();
		}

		return $product;
	}

	public function getCount()
	{
		return $this->count->get();
	}

	public function getSubItems()
	{
		if (!$this->getProduct()->isBundle())
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

	protected function insert()
	{
		if ($this->shipment->get() && !$this->shipment->get()->isExistingRecord())
		{
			$this->shipment->setNull();
		}

		if (!$this->price->get())
		{
			$this->price->set($this->getProduct()->getItemPrice($this));
		}

		return parent::insert();
	}

	public function save($forceOperation = null)
	{
		// update inventory
		$shipment = $this->shipment->get();
		if (!$shipment && $this->parent->get())
		{
			$shipment = $this->parent->get()->shipment->get();
		}

		$order = $this->customerOrder->get();

		if ($shipment && $order->isFinalized->get() && !$order->isCancelled->get() && self::getApplication()->isInventoryTracking())
		{
			$product = $this->getProduct();

			// changed product (usually a different variation)
			if ($this->product->isModified())
			{
				// unreserve original item
				if ($orig = $this->product->getInitialValue())
				{
					$this->reserve(true, $orig);
					$orig->save();
				}

				// reserve new item
				$this->reserve();
			}

			if (($this->reservedProductCount->get() > 0) && ($shipment->status->get() == Shipment::STATUS_SHIPPED))
			{
				$this->removeFromInventory();
			}
			else if (0 == $this->reservedProductCount->get())
			{
				if ($shipment->status->get() == Shipment::STATUS_RETURNED)
				{
					$this->reservedProductCount->set($this->count->get());
					$product->reservedCount->set($product->reservedCount->get() + $this->count->get());
				}
				else
				{
					$this->reserve();
				}
			}
			else if ($this->count->isModified())
			{
				$delta = $this->count->get() - $this->reservedProductCount->get();
				$this->reservedProductCount->set($this->count->get());
				$product->reservedCount->set($product->reservedCount->get() + $delta);
				$product->stockCount->set($product->stockCount->get() - $delta);
			}
		}

		$ret = parent::save($forceOperation);

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
		if ($this->getProduct()->isBundle())
		{
			foreach ($this->getSubItems() as $item)
			{
				$item->save();
			}
		}

		$this->getProduct()->save();
		$this->subItems = null;

		return $ret;
	}

	protected function update()
	{
		if (is_null($this->shipment->get()) || !$this->shipment->get()->getID())
		{
			$this->shipment->setNull(false);
			$this->shipment->resetModifiedStatus();
		}

		if ($this->isModified())
		{
			$user = $this->customerOrder->get()->user->get();
			if ($user)
			{
				$user->load();
			}

			if ($this->price->isNull())
			{
				$this->price->set($this->getProduct()->getItemPrice($this));
			}

			return parent::update();
		}
		else
		{
			return false;
		}
	}

	/*####################  Data array transformation ####################*/

	public function toArray()
	{
		$array = parent::toArray();
		$array['priceCurrencyID'] = $this->getCurrency()->getID();
		$isTaxIncludedInPrice = $this->customerOrder->get()->getDeliveryZone()->isTaxIncludedInPrice();

		if (isset($array['price']))
		{
			$currency = $this->getCurrency();

			$array['itemBasePrice'] = self::getApplication()->getDisplayTaxPrice($array['price'], isset($array['Product']) ? $array['Product'] : array());;
			$array['displayPrice'] = $this->getDisplayPrice($currency, $isTaxIncludedInPrice);
			$array['displaySubTotal'] = $this->getSubTotal($isTaxIncludedInPrice);
			$array['itemPrice'] = $array['displaySubTotal'] / $array['count'];

			//var_dump($isTaxIncludedInPrice, $array['displayPrice'], $array['itemPrice'], $array['itemBasePrice'], '----');
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

			if ($this->optionChoices)
			{
				foreach ($this->optionChoices as $choice)
				{
					$array['itemBasePrice'] += $choice->choice->get()->getPriceDiff($this->getCurrency());
				}
			}

			$array['formattedBasePrice'] = $currency->getFormattedPrice($array['itemBasePrice']);
			$array['formattedPrice'] = $currency->getFormattedPrice($array['itemPrice']);
			$array['formattedDisplayPrice'] = $currency->getFormattedPrice($array['displayPrice']);
			$array['formattedDisplaySubTotal'] = $currency->getFormattedPrice($array['displaySubTotal']);
		}

		$array['options'] = array();
		foreach ($this->optionChoices as $id => $choice)
		{
			$array['options'][$id] = $choice->toArray();
		}

		$array['subItems'] = array();
		if ($this->subItems)
		{
			foreach ($this->subItems as $subItem)
			{
				$array['subItems'][] = $subItem->toArray();
			}
		}

		$this->setArrayData($array);

		return $array;
	}

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);

		// deleted product
		if (!isset($array['Product']) && isset($array['name_lang']))
		{
			$array['Product']['name'] = $array['name'];
			$array['Product']['name_lang'] = $array['name_lang'];
			$array['Product']['nameData'] = $array['nameData'];
			if (isset($array['nameData']['sku']))
			{
				$array['Product']['sku'] = $array['nameData']['sku'];
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
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('ProductFile', 'ID'), $id));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('ProductFile', 'productID'), $this->product->get()->getID()));
		$s = ActiveRecordModel::getRecordSet('ProductFile', $f);
		if (!$s->size())
		{
			return false;
		}
		else
		{
			return $s->get(0);
		}
	}

	public function serialize()
	{
		$this->markAsLoaded();
		return parent::serialize(array('customerOrderID', 'shipmentID', 'productID'));
	}

	public function __destruct()
	{
		parent::destruct(array('productID', 'shipmentID'));
	}

	public function __clone()
	{
		parent::__clone();

		foreach ($this->optionChoices as $key => $option)
		{
			$newOpt = clone $option;
			$newOpt->orderedItem->set($this);
			$newOpt->choice->setAsModified();
			$this->optionChoices[$key] = $newOpt;
		}
	}
}

?>
