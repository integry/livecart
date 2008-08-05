<?php

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.order.Shipment");
ClassLoader::import('application.model.order.OrderedItemOption');

/**
 * Represents a shopping basket item (one or more instances of the same product)
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class OrderedItem extends ActiveRecordModel
{
	protected $optionChoices = array();

	protected $removedChoices = array();

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

		$schema->registerField(new ARField("priceCurrencyID", ARChar::instance(3)));
		$schema->registerField(new ARField("price", ARFloat::instance()));
		$schema->registerField(new ARField("count", ARFloat::instance()));
		$schema->registerField(new ARField("reservedProductCount", ARFloat::instance()));
		$schema->registerField(new ARField("dateAdded", ARDateTime::instance()));
		$schema->registerField(new ARField("isSavedForLater", ARBool::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(CustomerOrder $order, Product $product, $count = 1)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->customerOrder->set($order);
		$instance->product->set($product);
		$instance->count->set($count);

		return $instance;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getSubTotal(Currency $currency, $includeTaxes = true)
	{
		$subTotal = $this->getPrice($currency) * $this->count->get();

		if ($includeTaxes)
		{
			$deliveryZone = $this->customerOrder->get()->getDeliveryZone();
			if ($deliveryZone->isDefault())
			{
				foreach ($deliveryZone->getTaxRates() as $rate)
				{
					$subTotal = $subTotal / (1 + ($rate->rate->get() / 100));
				}
			}
		}

		return $subTotal;
	}

	public function getPrice(Currency $currency, $includeTaxes = true)
	{
		$isFinalized = $this->customerOrder->get()->isFinalized->get();

		$itemCurrency = $this->priceCurrencyID->get() ? Currency::getInstanceById($this->priceCurrencyID->get()) : $currency;

		$price = $isFinalized ? $this->price->get() : $this->getItemPrice($currency, $includeTaxes);

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

			$price += $optionPrice;
		}

		$price = $itemCurrency->convertAmount($currency, $price);

		return $price;
	}

	private function getItemPrice(Currency $currency, $includeTaxes = true)
	{
		$price = $this->product->get()->getPrice($currency->getID());

		if ($includeTaxes)
		{
			$zone = $this->customerOrder->get()->getDeliveryZone();
			if (!$zone->isDefault())
			{
				foreach (DeliveryZone::getDefaultZoneInstance()->getTaxRates() as $rate)
				{
					$price = $price / (1 + ($rate->rate->get() / 100));
				}
			}
		}

		return $price;
	}

	public function reserve()
	{
		$product = $this->product->get();
		$product->reservedCount->set($product->reservedCount->get() + $this->reservedProductCount->get());
	}

	/**
	 *  @todo implement
	 */
	public function unreserve()
	{

	}

	/**
	 *  Determine if the file download period hasn't expired yet
	 *
	 *  @return ProductFile
	 */
	public function isDownloadable(ProductFile $file)
	{
		$orderDate = $this->customerOrder->get()->dateCompleted->get();

		return (abs($orderDate->getDayDifference(new DateTime())) <= $file->allowDownloadDays->get()) ||
				!$file->allowDownloadDays->get();
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
		foreach ($this->getRelatedRecordSet('OrderedItemOption', new ARSelectFilter(), true) as $option)
		{
			$this->optionChoices[$option->choice->get()->option->get()->getID()] = $option;
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

  	/*####################  Saving ####################*/

	protected function insert()
	{
		$this->shipment->setNull();

		$this->priceCurrencyID->set($this->customerOrder->get()->currency->get()->getID());
		if (!$this->price->get())
		{
			$this->price->set($this->product->get()->getPrice($this->priceCurrencyID->get()));
		}

		return parent::insert();
	}

	public function save($forceOperation = null)
	{
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

		// adjust inventory
		$this->product->get()->save();

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

		if (isset($array['priceCurrencyID']))
		{
			$currency = Currency::getInstanceByID($array['priceCurrencyID']);
			$array['itemPrice'] = $this->getPrice($currency);
			$array['itemSubTotal'] = $this->getSubTotal($currency);
			$array['displayPrice'] = $this->getPrice($currency, !$this->customerOrder->get()->getDeliveryZone()->isDefault());
			$array['displaySubTotal'] = $this->getSubTotal($currency, !$this->customerOrder->get()->getDeliveryZone()->isDefault());

			$array['formattedBasePrice'] = $currency->getFormattedPrice($array['price']);
			$array['formattedPrice'] = $currency->getFormattedPrice($array['itemPrice']);
			$array['formattedDisplayPrice'] = $currency->getFormattedPrice($array['displayPrice']);
			$array['formattedDisplaySubTotal'] = $currency->getFormattedPrice($array['displaySubTotal']);
			$array['formattedSubTotal'] = $currency->getFormattedPrice($array['itemSubTotal']);
		}

		$array['options'] = array();
		foreach ($this->optionChoices as $id => $choice)
		{
			$array['options'][$id] = $choice->toArray();
		}

		$this->setArrayData($array);

		return $array;
	}

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);

		$array['itemSubtotal'] = $array['count'] * $array['price'];

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