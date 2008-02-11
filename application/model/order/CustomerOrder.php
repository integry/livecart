<?php

ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderedItem");
ClassLoader::import("application.model.order.Shipment");
ClassLoader::import("application.model.delivery.ShipmentDeliveryRate");

/**
 * Represents customers order - products placed in shopping basket or wish list
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class CustomerOrder extends ActiveRecordModel
{
	public $orderedItems = array();

	//public $shipments = new ARSet();

	private $removedItems = array();

	private $taxes = array();

	private $deliveryZone;

	const STATUS_NEW = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_AWAITING = 2;
	const STATUS_SHIPPED = 3;
	const STATUS_RETURNED = 4;

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
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", "User", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shippingAddressID", "shippingAddress", "ID", 'UserAddress', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("billingAddressID", "billingAddress", "ID", 'UserAddress', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("currencyID", "currency", "ID", 'Currency', ARChar::instance(3)));

		$schema->registerField(new ARField("dateCreated", ARDateTime::instance()));
		$schema->registerField(new ARField("dateCompleted", ARDateTime::instance()));
		$schema->registerField(new ARField("totalAmount", ARFloat::instance()));
		$schema->registerField(new ARField("capturedAmount", ARFloat::instance()));
		$schema->registerField(new ARField("isFinalized", ARBool::instance()));
		$schema->registerField(new ARField("isPaid", ARBool::instance()));
		$schema->registerField(new ARField("isCancelled", ARBool::instance()));
		$schema->registerField(new ARField("status", ARInteger::instance()));
		$schema->registerField(new ARField("shipping", ARText::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(User $user)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->user->set($user);

		return $instance;
	}

	public static function getInstanceById($id, $loadData = self::LOAD_DATA, $loadReferencedRecords = false)
	{
		return parent::getInstanceById('CustomerOrder', $id, $loadData, $loadReferencedRecords);
	}

	/**
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function loadItems()
	{
		if (!$this->isExistingRecord())
		{
			return false;
		}

		$this->orderedItems = $this->getRelatedRecordSet('OrderedItem', new ARSelectFilter(), array('Product', 'Category', 'DefaultImage' => 'ProductImage'))->getData();

		if ($this->orderedItems)
		{
			$this->shipments = $this->getRelatedRecordSet('Shipment', new ARSelectFilter(), self::LOAD_REFERENCES);

			if (!$this->shipments->size() && !$this->isFinalized->get())
			{
				$this->shipments = unserialize($this->shipping->get());
			}

			// load applied product option choices
			$ids = array();
			foreach ($this->orderedItems as $key => $item)
			{
				$ids[] = $item->getID();
			}

			$f = new ARSelectFilter(new INCond(new ARFieldHandle('OrderedItemOption', 'orderedItemID'), $ids));
			foreach (ActiveRecordModel::getRecordSet('OrderedItemOption', $f, array('Option' => 'ProductOption', 'Choice' => 'ProductOptionChoice'/*, 'DefaultChoice' => 'ProductOptionChoice'*/)) as $itemOption)
			{
				$itemOption->orderedItem->get()->loadOption($itemOption);
			}
		}
	}

	public function loadAddresses()
	{
		if ($this->billingAddress->get())
		{
			$this->billingAddress->get()->load(self::LOAD_REFERENCES);
		}

		if ($this->shippingAddress->get())
		{
			$this->shippingAddress->get()->load(self::LOAD_REFERENCES);
		}
	}

	public function loadAll()
	{
		$this->loadAddresses();
		$this->getShipments();
	}

	/**
	 *  Add a product to shopping basket
	 */
	public function addProduct(Product $product, $count)
	{
		if (0 >= $count)
		{
			$this->removeProduct($product);
		}
		else
		{
			if (!$product->isAvailable())
			{
				throw new ApplicationException('Product is not available (' . $product->getID() . ')');
			}

			$count = $this->validateCount($product, $count);
			$item = OrderedItem::getNewInstance($this, $product, $count);
			$this->orderedItems[] = $item;
		}

		$this->resetShipments();

		if (isset($item))
		{
			return $item;
		}
	}

	public function updateCount(OrderedItem $item, $count)
	{
		$item->count->set($this->validateCount($item->product->get(), $count));
	}

	private function validateCount(Product $product, $count)
	{
		if (round($count) != $count && !$product->isFractionalUnit->get())
		{
			$count = round($count);
		}

		if (0 >= $count)
		{
			$count = 0;
		}
		else if ($product->minimumQuantity->get() > $count)
		{
			$count = $product->minimumQuantity->get();
		}

		return $count;
	}

	/**
	 *  Add a product to wish list
	 */
	public function addToWishList(Product $product)
	{
		$item = OrderedItem::getNewInstance($this, $product, 1);
		$item->isSavedForLater->set(true);
		$this->orderedItems[] = $item;
	}

	/**
	 *  Remove a product (all product items) from shopping basket or wish list
	 */
	public function removeProduct(Product $product)
	{
		$id = $product->getID();

		foreach ($this->orderedItems as $key => $item)
		{
			if ($item->product->get()->getID() == $id)
			{
				$this->removeItem($item);
			}
		}
	}

	/**
	 *  Remove an item from shopping basket or wish list
	 */
	public function removeItem(OrderedItem $orderedItem)
	{
		foreach ($this->orderedItems as $key => $item)
		{
			if ($item === $orderedItem)
			{
				$this->removedItems[] = $item;
				$item->markAsDeleted();
				unset($this->orderedItems[$key]);
				$this->resetShipments();
				break;
			}
		}
	}

	/**
	 *  Remove a shipment from order
	 */
	public function removeShipment(Shipment $removedShipment)
	{
		foreach ($this->shipments as $key => $shipment)
		{
			if ($removedShipment === $shipment)
			{
				for($i = 0; $i < count($this->orderedItems); $i++)
				{
					if($this->orderedItems[$i]->shipment->get() && ($this->orderedItems[$i]->shipment->get() === $removedShipment))
					{
						$this->removeItem($this->orderedItems[$i]);
					}
				}

				$this->shipments->remove($key);

				$this->resetShipments();
				break;
			}
		}
	}

	/**
	 *  Move an item to a different order
	 */
	public function moveItem(OrderedItem $orderedItem, CustomerOrder $order)
	{
		foreach ($this->orderedItems as $key => $item)
		{
			if ($item === $orderedItem)
			{
				unset($this->orderedItems[$key]);
				$order->addItem($item);

				$this->resetShipments();
				$order->resetShipments();
			}
		}
	}

	/**
	 *  Add new ordered item
	 */
	public function addItem(OrderedItem $orderedItem)
	{
		$orderedItem->customerOrder->set($this);
		$this->orderedItems[] = $orderedItem;
	}

	/**
	 *  "Close" the order for modifications and fix its state
	 *
	 *  1) fix current product prices and total (so the total doesn't change if product prices change)
	 *  2) save created shipments
	 *
	 *  @return CustomerOrder New order instance containing wishlist items
	 */
	public function finalize(Currency $currency, $reserveProducts = null)
	{
		self::beginTransaction();

		$this->currency->set($currency);

		$this->loadAll();
		foreach ($this->getShipments() as $shipment)
		{
			$shipment->amountCurrencyID->set($currency);
			$shipment->order->set($this);
			$shipment->save();
		}

		if (!is_null($reserveProducts))
		{
			$reserveProducts = ($c->get('INVENTORY_TRACKING') != 'DISABLE');
		}

		foreach ($this->getShoppingCartItems() as $item)
		{
			// reserve products if inventory is enabled
			if ($reserveProducts)
			{
				$item->reserve();
			}

			$item->priceCurrencyID->set($currency->getID());
			$item->price->set($item->product->get()->getPrice($currency->getID()));
			$item->save();
		}

		if (!$this->shippingAddress->get() && $this->user->get()->defaultShippingAddress->get())
		{
			$this->shippingAddress->set($this->user->get()->defaultShippingAddress->get()->userAddress->get());
		}

		if (!$this->billingAddress->get() && $this->user->get()->defaultBillingAddress->get())
		{
			$this->billingAddress->set($this->user->get()->defaultBillingAddress->get()->userAddress->get());
		}

		// clone billing/shipping addresses
		if ($this->shippingAddress->get())
		{
			$shippingAddress = clone $this->shippingAddress->get();
			$shippingAddress->save();
			$this->shippingAddress->set($shippingAddress);
		}

		$billingAddress = clone $this->billingAddress->get();
		$billingAddress->save();
		$this->billingAddress->set($billingAddress);

		// move wish list items to a separate order
		$wishList = CustomerOrder::getNewInstance($this->user->get());
		foreach ($this->getWishListItems() as $item)
		{
			$wishList->addItem($item);
		}
		$wishList->save();

		// set order total
		$this->totalAmount->set($this->getTotal($currency));

		if ($this->totalAmount->get() <= $this->getPaidAmount())
		{
			$this->isPaid->set(true);
		}

		$this->isFinalized->set(true);
		$this->dateCompleted->set(new ARSerializableDateTime());
		$this->save();

		self::commit();

		return $wishList;
	}

	public function addCapturedAmount($amount)
	{
		$this->capturedAmount->set($this->capturedAmount->get() + $amount);
	}

	/**
	 *  Merge OrderedItem instances of the same product into one instance
	 */
	public function mergeItems()
	{
		$byProduct = array();

		foreach ($this->orderedItems as $item)
		{
			$byProduct[$item->product->get()->getID()][(int)$item->isSavedForLater->get()][] = $item;
		}

		foreach ($byProduct as $productID => $itemsByStatus)
		{
			foreach ($itemsByStatus as $status => $items)
			{
				if (count($items) > 1)
				{
					$mainItem = array_shift($items);
					$count = $mainItem->count->get();

					foreach ($items as $item)
					{
						$count += $item->count->get();
						$this->removeItem($item);
					}

					$mainItem->count->set($count);
				}
			}
		}
	}

	/*####################  Saving ####################*/

	public function save($allowEmpty = false)
	{
		if (!$this->orderedItems)
		{
			$this->loadItems();
		}

		// remove zero-count items
		foreach ($this->orderedItems as $item)
		{
			if (!$item->count->get())
			{
				$this->removeItem($item);
			}
		}

		$isModified = false;

		foreach ($this->orderedItems as $item)
		{
			if ($item->isDeleted())
			{
				$this->removeItem($item);
			}
		}

		// delete removed items
		if ($this->removedItems)
		{
			foreach ($this->removedItems as $item)
			{
				$item->delete();
				$isModified = true;
			}

			$this->removedItems = array();
			$this->resetShipments();
		}

		if ($this->orderedItems)
		{
			if(!$this->currency->get())
			{
				$this->currency->set(self::getApplication()->getDefaultCurrency());
			}

			foreach ($this->orderedItems as $item)
			{
				if ($item->isModified())
				{
					if (!$this->isExistingRecord())
					{
						parent::save();
					}

					if ($item->save())
					{
						$isModified = true;
					}
				}

				$item->markAsLoaded();
			}
		}

		// If shipment is modified
		if ($this->isFinalized->get())
		{
			$count = 0;

			foreach($this->shipments as $shipment)
			{
				if($shipment->isModified())
				{
					$isModified = true;
					break;
				}
			}
		}

		if ($isModified)
		{
			if (!$this->currency->get())
			{
				$this->currency->set(self::getApplication()->getDefaultCurrency());
			}

			// reorder shipments when cart items are modified
			$this->resetShipments();

			$this->totalAmount->set($this->calculateTotal($this->currency->get()));
		}

		if ($this->isModified() || $isModified)
		{
			$this->shipping->set(serialize($this->shipments));
		}

		if (!$this->isFinalized->get() && !$this->orderedItems && !$allowEmpty)
		{
			$this->delete();
			return false;
		}

		return parent::save();
	}

	public function update($force = false)
	{
		return parent::update($force);
	}

	public function updateShipmentStatuses()
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle('Shipment', 'orderID'), $this->getID()));

		if(!$this->isReturned())
		{
			$filter->mergeCondition(new NotEqualsCond(new ARFieldHandle('Shipment', 'status'), self::STATUS_SHIPPED));
		}

		// get shipments for which the status will be changed
		$shipments = ActiveRecordModel::getRecordSet('Shipment', $filter, Shipment::LOAD_REFERENCES);
		foreach ($shipments as $key => $shipment)
		{
			if ($shipment->status->get() == $this->status->get())
			{
				$shipments->remove($key);
			}

			$shipment->status->set($this->status->get());
		}

		$update = new ARUpdateFilter();
		$update->setCondition($filter->getCondition());
		$update->addModifier('Shipment.status', $this->status->get());

		ActiveRecordModel::updateRecordSet('Shipment', $update);

		return $shipments;
	}

	public function updateStatusFromShipments($creatingNewRecord = false)
	{
		$status = $this->calculateStatusFromShipmensts($creatingNewRecord);

		if($this->status->get() != $status)
		{
			$this->status->set($status);
		}
	}

	public function calculateStatusFromShipmensts($creatingNewRecord = false)
	{
		$lowestStatus = null;
		$isNew = true;
		$countShipments = 0;
		$haveShipped = false;
		foreach($this->getShipments() as $shipment)
		{
			if(!$shipment->isShippable() && count($shipment->getItems()) > 0) continue;

			if(is_null($lowestStatus))
			{
				$lowestStatus = $shipment->status->get();
			}
			else if($lowestStatus != $shipment->status->get())
			{
				$lowestStatus = Shipment::STATUS_PROCESSING;
			}
		}

		if(!is_null($lowestStatus) && $lowestStatus != $this->status->get())
		{
			return $lowestStatus;
		}

		return $this->status->get();
	}

	public function getSubTotal(Currency $currency)
	{
		$subTotal = 0;
		foreach ($this->orderedItems as $item)
		{
			if (!$item->isSavedForLater->get())
			{
				$subTotal += $item->getSubTotal($currency);
			}
		}

		return $subTotal;
	}

	/**
	 *  Get total amount for order, including shipping costs
	 */
	public function getTotal(Currency $currency)
	{
		if ($this->isFinalized->get())
		{
			return $currency->convertAmount($this->currency->get(), $this->totalAmount->get());
		}
		else
		{
			return $this->calculateTotal($currency);
		}
	}

	public function calculateTotal(Currency $currency)
	{
		$total = 0;
		$id = $currency->getID();

		if ($this->shipments instanceof ARSet && !$this->shipments->size())
		{
			$this->shipments = null;
		}

		if ($this->shipments)
		{
			$this->taxes[$id] = array();
			$zone = $this->getDeliveryZone();
			foreach ($this->shipments as $shipment)
			{
				if($shipment->getShippingService())
				{
					$shipmentRates = $zone->getShippingRates($shipment);
					$shipment->setAvailableRates($shipmentRates);
					$shipment->setRateId($shipment->getShippingService()->getID());
				}

				$total += $shipment->getTotal($currency);

				foreach ($shipment->getTaxes() as $tax)
				{
					$taxId = ($tax->taxRate->get() && $tax->taxRate->get()->tax->get()) ? $tax->taxRate->get()->tax->get()->getID() : 0;
					if (!isset($this->taxes[$id][$taxId]))
					{
						$this->taxes[$id][$taxId] = 0;
					}

					$this->taxes[$id][$taxId] += $tax->getAmountByCurrency($currency);
				}
			}
		}
		else
		{
			foreach ($this->getShoppingCartItems() as $item)
			{
				$total += $item->getSubTotal($currency);
			}
		}

		return round($total, 2);
	}

	public function isProcessing()
	{
		return $this->status->get() == self::STATUS_PROCESSING;
	}

	public function isAwaitingShipment()
	{
		return $this->status->get() == self::STATUS_AWAITING;
	}

	public function isShipped()
	{
		return $this->status->get() == self::STATUS_SHIPPED;
	}

	public function isReturned()
	{
		return $this->status->get() == self::STATUS_RETURNED;
	}

	/**
	 * No shipping is required for orders consisting of downloadable items only
	 */
	public function isShippingRequired()
	{
		foreach ($this->getShoppingCartItems() as $item)
		{
			if (!$item->product->get()->isDownloadable())
			{
				return true;
			}
		}

		return false;
	}

	public function isShippingSelected()
	{
		$selected = $this->shipments ? $this->shipments->size() : 0;

		if (!$this->shipments)
		{
			return false;
		}

		foreach ($this->shipments as $shipment)
		{
			if (!$shipment->getSelectedRate())
			{
				$selected = false;
			}
		}

		return $selected;
	}

	/**
	 *  Determines if the order matches defined requirements/constraints (min/max total, etc.)
	 */
	public function isOrderable()
	{
		ClassLoader::import('application.model.order.OrderException');

		$app = $this->getApplication();
		$c = $app->getConfig();

		// check product quantity
		$maxQuant = $c->get('MAX_QUANT');
		$minQuant = $c->get('MIN_QUANT');
		$quant = $this->getShoppingCartItemCount();

		if (!$quant)
		{
			return false;
		}

		if ($maxQuant && ($quant > $maxQuant))
		{
			return new OrderException(OrderException::MAX_QUANT, $quant, $maxQuant, $app);
		}

		if ($minQuant && ($quant < $minQuant))
		{
			return new OrderException(OrderException::MIN_QUANT, $quant, $minQuant, $app);
		}

		// check order total
		$maxTotal = $c->get('MAX_TOTAL');
		$minTotal = $c->get('MIN_TOTAL');
		$total = $this->getSubTotal($this->getApplication()->getDefaultCurrency());

		if ($maxTotal && ($total > $maxTotal))
		{
			return new OrderException(OrderException::MAX_TOTAL, $total, $maxTotal, $app);
		}

		if ($minTotal && ($total < $minTotal))
		{
			return new OrderException(OrderException::MIN_TOTAL, $total, $minTotal, $app);
		}

		return true;
	}

	/**
	 *  Merge two orders into one
	 */
	public function merge(CustomerOrder $order)
	{
		foreach ($order->getOrderedItems() as $item)
		{
			$order->moveItem($item, $this);
		}

		$this->mergeItems();
	}

	public function changeCurrency(Currency $currency)
	{
		$this->currency->set($currency);
		foreach ($this->getOrderedItems() as $item)
		{
			$item->price->set($item->product->get()->getPrice($currency));
			$item->priceCurrencyID->set($currency->getID());
			$item->save();
		}

		$this->save();
	}

	public function getPaidAmount()
	{
		ClassLoader::import('application.model.order.Transaction');
		$filter = new ARSelectFilter(new InCond(new ARFieldHandle('Transaction', 'type'), array(Transaction::TYPE_AUTH, Transaction::TYPE_SALE)));
		$filter->mergeCondition(new NotEqualsCond(new ARFieldHandle('Transaction', 'isVoided'), true));

		$transactions = $this->getTransactions($filter);
		$paid = 0;
		foreach ($transactions as $transaction)
		{
			$paid += $transaction->amount->get();
		}

		return $paid;
	}

	/*####################  Data array transformation ####################*/

	/**
	 *  Creates an array representation of the shopping cart
	 */
	public function toArray($options = array())
	{
		if (is_array($this->orderedItems))
		{
			foreach ($this->orderedItems as $item)
			{
				if (!$item->product->get()->isPricingLoaded())
				{
					if (!isset($products))
					{
						$products = new ARSet();
					}
					$products->unshift($item->product->get());
				}
			}

			if (isset($products))
			{
				ProductPrice::loadPricesForRecordSet($products);
			}
		}

		$array = parent::toArray();

		$array['cartItems'] = array();
		$array['wishListItems'] = array();

		if (is_array($this->orderedItems))
		{
			foreach ($this->orderedItems as $item)
			{
				if ($item->isSavedForLater->get())
				{
					$array['wishListItems'][] = $item->toArray();
				}
				else
				{
					$array['cartItems'][] = $item->toArray();
				}
			}
		}

		$array['basketCount'] = $this->getShoppingCartItemCount();
		$array['wishListCount'] = $this->getWishListItemCount();

		// shipments
		$array['shipments'] = array();
		if ($this->shipments)
		{
			foreach ($this->shipments as $shipment)
			{
				if (count($shipment->getItems()))
				{
					$array['shipments'][] = $shipment->toArray();
				}
			}
		}

		// total for all currencies
		$total = array();
		$currencies = self::getApplication()->getCurrencySet();

		if (is_array($currencies))
		{
			foreach ($currencies as $id => $currency)
			{
				$total[$id] = $this->getTotal($currency);
			}
		}

		// taxes
		$array['taxes'] = array();
		foreach ($this->taxes as $currencyId => $taxes)
		{
			$array['taxes'][$currencyId] = array();
			$currency = Currency::getInstanceById($currencyId);

			foreach ($taxes as $taxId => $amount)
			{
				$array['taxes'][$currencyId][$taxId] = Tax::getInstanceById($taxId)->toArray();
				$array['taxes'][$currencyId][$taxId]['formattedAmount'] = $currency->getFormattedPrice($amount);
			}
		}

		$array['total'] = $total;

		$array['formattedTotal'] = array();
		if (is_array($array['total']))
		{
			foreach ($array['total'] as $id => $amount)
			{
				$array['formattedTotal'][$id] = $currencies[$id]->getFormattedPrice($amount);
			}
		}

		// order type
		$array['isShippingRequired'] = (int)$this->isShippingRequired();

		// status
		$array['isReturned'] = (int)$this->isReturned();;
		$array['isShipped'] = (int)$this->isShipped();
		$array['isAwaitingShipment'] = (int)$this->isAwaitingShipment();
		$array['isProcessing'] = (int)$this->isProcessing();

		// payments
		if (isset($options['payments']))
		{
			$currency = $this->currency->get();
			$array['amountPaid'] = $this->getPaidAmount();

			$array['amountNotCaptured'] = $array['amountPaid'] - $array['capturedAmount'];
			if ($array['amountNotCaptured'] < 0)
			{
				$array['amountNotCaptured'] = 0;
			}

			$array['amountDue'] = $array['totalAmount'] - $array['amountPaid'];
			if ($array['amountDue'] < 0)
			{
				$array['amountDue'] = 0;
			}

			// items subtotal
			$array['itemSubtotal'] = 0;
			foreach ($this->getOrderedItems() as $item)
			{
				$array['itemSubtotal'] += $item->getSubtotal($currency);
			}

			// shipping subtotal
			$array['shippingSubtotal'] = 0;
			foreach ($this->shipments as $shipment)
			{
				$array['shippingSubtotal'] += $shipment->shippingAmount->get();
			}

			$array['subtotalBeforeTaxes'] = $array['itemSubtotal'] + $array['shippingSubtotal'];

			foreach (array('amountPaid', 'amountNotCaptured', 'amountDue', 'itemSubtotal', 'shippingSubtotal', 'subtotalBeforeTaxes', 'totalAmount') as $key)
			{
				$array['formatted_' . $key] = $currency->getFormattedPrice($array[$key]);
			}
		}

		if (!$array['isFinalized'])
		{
			$isOrderable = $this->isOrderable();
			if ($isOrderable instanceof OrderException)
			{
				$array['error'] = $isOrderable->toArray();
			}

			$array['isOrderable'] = !($isOrderable instanceof OrderException) && $isOrderable;

			$array['isShippingSelected'] = $this->isShippingSelected();
			$array['isShippingSelected'] = $this->isShippingSelected();
			$array['isAddressSelected'] = ($this->shippingAddress->get() && $this->billingAddress->get());
		}

		$this->setArrayData($array);

		return $array;
	}

	/*####################  Get related objects ####################*/

	public function getShoppingCartItems()
	{
		$items = array();

		foreach ($this->orderedItems as $item)
		{
			if (!$item->isSavedForLater->get())
			{
				$items[] = $item;
			}
		}

		return $items;
	}

	public function getWishListItems()
	{
		$items = array();
		foreach ($this->orderedItems as $item)
		{
			if ($item->isSavedForLater->get())
			{
				$items[] = $item;
			}
		}

		return $items;
	}

	public function getOrderedItems()
	{
		return $this->orderedItems;
	}

	public function getShoppingCartItemCount()
	{
		$count = 0;

		foreach ($this->getShoppingCartItems() as $item)
		{
			$count += $item->count->get();
		}

		return $count;
	}

	public function getWishListItemCount()
	{
		return count($this->getWishListItems());
	}

	public function getItemsByProduct(Product $product)
	{
		$items = array();
		foreach ($this->orderedItems as $item)
		{
			if ($item->product->get()->getID() == $product->getID())
			{
				$items[] = $item;
			}
		}

		return $items;
	}

	/**
	 *  Return OrderedItem instance by ID
	 */
	public function getItemByID($id)
	{
		foreach ($this->orderedItems as $item)
		{
			if ($item->getID() == $id)
			{
				return $item;
			}
		}
	}

	/**
	 *  Loads ordered item/product info from database
	 */
	public function loadItemData()
	{
		$productIDs = array();

		foreach ($this->orderedItems as $item)
		{
			$productIDs[] = $item->product->get()->getID();
		}

		$products = ActiveRecordModel::getInstanceArray('Product', $productIDs);

		foreach ($this->orderedItems as $item)
		{
			$id = $item->product->get()->getID();

			if (isset($products[$id]))
			{
				$item->product->set($products[$id]);
			}
			else
			{
				$this->removeProduct($item->product->get());
			}
		}
	}

	/**
	 *  Separate items into shipments (if any item needs to be shipped separately)
	 *
	 *  @return Shipment[]
	 */
	public function getShipments()
	{
		if ($this->isFinalized->get())
		{
			$this->loadItems();

			$filter = new ARSelectFilter(new EqualsCond(new ARFieldHandle('Shipment', 'orderID'), $this->getID()));
			$filter->setOrder(new ARFieldHandle('Shipment', 'status'));

			$this->shipments = $this->getRelatedRecordSet('Shipment', $filter, array('ShippingService'));
			foreach($this->shipments as $shipment)
			{
				$shipment->loadItems();
			}

			/*
			// get downloadable items
			foreach ($this->getShoppingCartItems() as $item)
			{
				if ($item->product->get()->isDownloadable())
				{
					if (!isset($downloadable))
					{
						$downloadable = Shipment::getNewInstance($this);
						$this->shipments->add($downloadable);
					}

					$downloadable->addItem($item);
				}
			}
			*/
		}
		else
		{
			if (!$this->shipments || !$this->shipments->size())
			{
				ClassLoader::import("application.model.order.Shipment");

				$this->shipments = new ARSet();

				foreach ($this->getShoppingCartItems() as $item)
				{
					if ($item->product->get()->isDownloadable())
					{
						if (!isset($downloadable))
						{
							$downloadable = Shipment::getNewInstance($this);
						}

						$downloadable->addItem($item);
					}
					else if ($item->product->get()->isSeparateShipment->get())
					{
						$shipment = Shipment::getNewInstance($this);
						$shipment->addItem($item);
						$this->shipments->add($shipment);
					}
					else
					{
						if (!isset($main))
						{
							$main = Shipment::getNewInstance($this);
						}
						$main->addItem($item);
					}
				}

				if (isset($main))
				{
					$this->shipments->unshift($main);
				}

				if (isset($downloadable))
				{
					$this->shipments->unshift($downloadable);
				}
			}


			$this->shipping->set(serialize($this->shipments));
		}

		return $this->shipments;
	}

	public function getDeliveryZone()
	{
		ClassLoader::import("application.model.delivery.DeliveryZone");

		if (!$this->deliveryZone)
		{
			if ($this->isShippingRequired() && $this->shippingAddress->get())
			{
				$this->deliveryZone = DeliveryZone::getZoneByAddress($this->shippingAddress->get());
			}
			else
			{
				$this->deliveryZone = DeliveryZone::getDefaultZoneInstance();
			}
		}

		return $this->deliveryZone;
	}

	/**
	 *  Return all transactions that are related to this order
	 */
	public function getTransactions(ARSelectFilter $filter = null)
	{
		ClassLoader::import('application.model.order.Transaction');
		if (is_null($filter))
		{
			$filter = new ARSelectFilter();
		}
		$filter->setOrder(new ARFieldHandle('Transaction', 'ID'), 'ASC');
		return $this->getRelatedRecordSet('Transaction', $filter);
	}

	public function getNotes()
	{
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('OrderNote', 'ID'), 'DESC');
		return $this->getRelatedRecordSet('OrderNote', $f, OrderNote::LOAD_REFERENCES);
	}

	public function resetShipments()
	{
		if (!$this->isFinalized->get())
		{
			$this->shipments = new ARSet();
		}
	}

	public function getDownloadShipment($createNew = true)
	{
		// look for a shipment that only contains downloadable items
		foreach($this->getShipments() as $shipment)
		{
			if (!$shipment->isShippable())
			{
				return $shipment;
			}
		}

		// look for an empty shipment
		foreach($this->getShipments() as $shipment)
		{
			if (!count($shipment->getItems()))
			{
				return $shipment;
			}
		}

		if ($createNew)
		{
			$shipment = Shipment::getNewInstance($this);
			$shipment->amountCurrency->set($this->currency->get());
			$shipment->save(true);

			$this->shipments->add($shipment);

			return $shipment;
		}
	}

	public function countShippableShipments()
	{
		// Caclulate number of shippable shipments
		$shippableCount = 0;
		foreach($this->getShipments() as $shipment)
		{
			if($shipment->isShippable())
			{
				$shippableCount++;
			}
		}

		return $shippableCount;
	}

	public function serialize()
	{
		return parent::serialize(array('userID'), array('orderedItems', 'shipments'));
	}

	public function unserialize($serialized)
	{
		parent::unserialize($serialized);

		// load products
		$productIds = array();
		foreach ($this->orderedItems as $item)
		{
			$productIds[] = $item->product->get()->getID();
		}

		$products = ActiveRecordModel::getInstanceArray('Product', $productIds, Product::LOAD_REFERENCES);

		// load product prices
		$set = new ARSet();
		foreach ($products as $product)
		{
			$set->add($product);
		}

		ProductPrice::loadPricesForRecordSet($set);
	}

	protected function __get($name)
	{
		switch ($name)
		{
			case 'shipments':
				$this->shipments = new ARSet();
				return $this->shipments;
			break;

			default:
			break;
		}
	}

	public function __destruct()
	{
		foreach ($this->orderedItems as $item)
		{
			$item->__destruct();
		}

		$this->orderedItems = array();

		foreach ($this->removedItems as $item)
		{
			$item->__destruct();
		}

		$this->removedItems = array();

		$this->taxes = array();

		if (isset($this->shipments))
		{
			foreach ($this->shipments as $shipment)
			{
				$shipment->__destruct();
			}
		}

		$this->shipments = array();

		parent::destruct(array('userID', 'billingAddressID', 'shippingAddressID'));
	}
}

?>
