<?php

ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.order.OrderCoupon");
ClassLoader::import("application.model.order.OrderedItem");
ClassLoader::import("application.model.order.Shipment");
ClassLoader::import("application.model.order.OrderDiscount");
ClassLoader::import("application.model.delivery.ShipmentDeliveryRate");
ClassLoader::import("application.model.eav.EavAble");

/**
 * Represents customers order - products placed in shopping basket or wish list
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class CustomerOrder extends ActiveRecordModel implements EavAble
{
	public $orderedItems = array();

	//public $shipments = new ARSet();

	private $removedItems = array();

	private $taxes = array();

	private $deliveryZone;

	private $fixedDiscounts = array();

	private $orderDiscounts = array();

	private $discountActions = null;

	private $coupons = null;

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
		$schema->registerField(new ARField("isMultiAddress", ARBool::instance()));
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
		$instance->currency->set(self::getApplication()->getDefaultCurrency());

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
			if (!$this->shipments || !$this->shipments->size())
			{
				$this->shipments = $this->getRelatedRecordSet('Shipment', new ARSelectFilter(), array('UserAddress'));

				// @todo: should be loaded automatically
				foreach ($this->shipments as $shipment)
				{
					if ($shipment->shippingAddress->get())
					{
						$shipment->shippingAddress->get()->load();
					}
				}
			}

			if (!$this->shipments->size() && !$this->isFinalized->get())
			{
				$this->shipments = unserialize($this->shipping->get());
			}

			OrderedItemOption::loadOptionsForItemSet(ARSet::buildFromArray($this->orderedItems));

			foreach ($this->orderedItems as $key => $item)
			{
				if ($item->parent->get())
				{
					$item->parent->get()->registerSubItem($item);
					unset($this->orderedItems[$key]);
				}
			}

			Product::loadAdditionalCategoriesForSet(ARSet::buildFromArray($this->orderedItems)->extractReferencedItemSet('product'));
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
		$this->loadItems();
		$this->getShipments();
		$this->getSpecification();

		if ($this->isExistingRecord())
		{
			$this->fixedDiscounts = $this->getRelatedRecordSet('OrderDiscount')->getData();
		}
	}

	/**
	 *  Add a product to shopping basket
	 */
	public function addProduct(Product $product, $count = 1, $ignoreAvailability = false, Shipment $shipment = null)
	{
		if (0 >= $count)
		{
			$this->removeProduct($product);
		}
		else
		{
			if (!$product->isAvailable() && !$ignoreAvailability)
			{
				throw new ApplicationException('Product is not available (' . $product->getID() . ')');
			}

			$count = $this->validateCount($product, $count);
			$item = OrderedItem::getNewInstance($this, $product, $count);

			if (!$this->isFinalized->get() || !$this->shipments || !$this->shipments->size())
			{
				$this->orderedItems[] = $item;

				if ($shipment)
				{
					$shipment->addItem($item);
				}
			}
			else
			{
				if (!$shipment)
				{
					$shipment = $this->shipments->get(0);
				}

				$shipment->addItem($item);
			}
		}

		$this->resetShipments();

		if (isset($item))
		{
			return $item;
		}
	}

	public function addShipment(Shipment $shipment)
	{
		$this->getShipments()->add($shipment);
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

		if ($orderedItem->shipment->get())
		{
			$orderedItem->shipment->get()->addItem($orderedItem);
		}
	}

	/**
	 *  "Close" the order for modifications and fix its state
	 *
	 *  1) fix current product prices and total (so the total doesn't change if product prices change)
	 *  2) save created shipments
	 *
	 *  @return CustomerOrder New order instance containing wishlist items
	 */
	public function finalize(Currency $currency)
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

		$reserveProducts = self::getApplication()->getConfig()->get('INVENTORY_TRACKING') != 'DISABLE';

		foreach ($this->getShoppingCartItems() as $item)
		{
			$item->priceCurrencyID->set($currency->getID());
			$item->price->set($item->getSubTotal($currency, false) / $item->count->get());
			$item->save();

			// create sub-items for bundled products
			if ($item->product->get()->isBundle())
			{
				foreach ($item->product->get()->getBundledProducts() as $bundled)
				{
					$bundledItem = OrderedItem::getNewInstance($this, $bundled->relatedProduct->get(), 1);
					$bundledItem->parent->set($item);
					$bundledItem->save();
				}
			}

			// reserve products if inventory is enabled
			if ($reserveProducts)
			{
				$item->reserve();
				$item->save();
			}
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

		if ($this->billingAddress->get())
		{
			$billingAddress = clone $this->billingAddress->get();
			$billingAddress->save();
			$this->billingAddress->set($billingAddress);
		}

		// move wish list items to a separate order
		$wishList = CustomerOrder::getNewInstance($this->user->get());
		foreach ($this->getWishListItems() as $item)
		{
			$wishList->addItem($item);
		}
		$wishList->save();

		// set order total
		$this->totalAmount->set($this->getTotal($currency));

		// save shipment taxes
		foreach ($this->shipments as $shipment)
		{
			$shipment->save();
		}

		// save discounts
		foreach ($this->orderDiscounts as $discount)
		{
			$discount->save();
		}

		if (round($this->totalAmount->get(), 2) <= round($this->getPaidAmount(), 2))
		{
			$this->isPaid->set(true);
		}

		$this->dateCompleted->set(new ARSerializableDateTime());

		$this->isFinalized->set(true);

		// @todo: fix order total calculation
		$shipments = $this->shipments;
		unset($this->shipments);

		$this->save();
		self::commit();

		// @todo: see above
		$this->shipments = $shipments;

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
		$items = array($this->orderedItems);

		if ($this->isMultiAddress->get())
		{
			$items = array();
			foreach ($this->getShipments() as $shipment)
			{
				$items[] = $shipment->getItems();
			}
		}

		foreach ($items as $itemSet)
		{
			$byProduct = array();

			foreach ($itemSet as $item)
			{
				// do not merge items that are same product, but different options
				$choiceHash = array();
				foreach ($item->getOptions() as $choice)
				{
					$choiceHash[] = md5($choice->choice->get()->getID() . '_' . $choice->optionText->get());
				}
				$hash = $choiceHash ? '_' . md5(implode('', $choiceHash)) : '';

				$byProduct[$item->product->get()->getID() . $hash][(int)$item->isSavedForLater->get()][] = $item;
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
			if ($this->shipments)
			{
				foreach($this->shipments as $shipment)
				{
					if($shipment->isModified())
					{
						$isModified = true;
						break;
					}
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

			$this->totalAmount->set($this->getTotal($this->currency->get(), true));
		}

		if ($this->isModified() || $isModified)
		{
			$this->shipping->set($this->isFinalized->get() || $this->isMultiAddress->get() ? '' : serialize($this->shipments));
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

	public function setStatus($status)
	{
		$this->status->set($status);
		$this->save();
		$this->updateShipmentStatuses();
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
			if ($shipment->status->get() != $this->status->get())
			{
				$shipment->status->set($this->status->get());
				$shipment->save();
			}
		}

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

	public function getSubTotal(Currency $currency, $applyDiscounts = true)
	{
		$subTotal = 0;
		foreach ($this->orderedItems as $item)
		{
			if (!$item->isSavedForLater->get())
			{
				$subTotal += $item->getSubTotal($currency, false, $applyDiscounts);
			}
		}

		return $subTotal;
	}

	public function getSubTotalBeforeTax(Currency $currency)
	{
		if (!$this->shipments)
		{
			return $this->getSubTotal($currency);
		}

		$subTotal = 0;
		foreach ($this->shipments as $shipment)
		{
			$subTotal += $shipment->getTotalWithoutTax();
		}

		return $subTotal;
	}

	/**
	 *  Get total amount for order, including shipping costs, discounts and taxes
	 */
	public function getTotal(Currency $currency, $recalculateAmount = false)
	{
		if ($this->isFinalized->get() && !$recalculateAmount)
		{
			$this->getTaxes($currency);
			return $currency->convertAmount($this->currency->get(), $this->totalAmount->get());
		}
		else
		{
			$total = $this->calculateTotal($currency);

			if ($discountAmount = $this->getFixedDiscountAmount($currency))
			{
				if ($this->shipments)
				{
					foreach ($this->shipments as $shipment)
					{
						$shipment->applyFixedDiscount($total, $discountAmount);
					}
				}

				$total = $this->calculateTotal($currency, false);

				if (!$this->shipments)
				{
					$total -= $discountAmount;
				}
			}

			if ($total < 0)
			{
				$total = 0;
			}

			return $total;
		}
	}

	public function getFixedDiscountAmount()
	{
		$amount = 0;
		foreach ($this->fixedDiscounts as $discount)
		{
			$amount += $discount->amount->get();
		}

		if (!$this->isFinalized->get())
		{
			foreach ($this->getDiscountActions() as $id => $action)
			{
				if ($action->isOrderDiscount() && $action->isFixedAmount())
				{
					$discount = $this->currency->get()->convertAmount(self::getApplication()->getDefaultCurrency(), $action->amount->get());
					$amount += $discount;
					$this->orderDiscounts[$id] = OrderDiscount::getNewInstance($this);
					$this->orderDiscounts[$id]->amount->set($discount);
					$this->orderDiscounts[$id]->description->set($action->condition->get()->getValueByLang('name'));
				}
			}
		}

		return $amount;
	}

	public function registerFixedDiscount(OrderDiscount $discount)
	{
		$this->fixedDiscounts[$discount->getID()] = $discount;
	}

	/**
	 *	Get full order total, including taxes and shipping, but excluding fixed discounts
	 */
	public function calculateTotal(Currency $currency, $recalculateAmounts = true)
	{
		$total = 0;
		$id = $currency->getID();

		if (!$this->shipments)
		{
			$this->getShipments();
		}

		if ($this->shipments instanceof ARSet && !$this->shipments->size())
		{
			$this->shipments = null;
		}

		if ($this->shipments)
		{
			// @todo: the tax calculation is slightly off when it's calculated for the first time, so it has to be called twice
			$this->getTaxes($currency);
			foreach ($this->shipments as $shipment)
			{
				$total += $shipment->getTotal($recalculateAmounts);
			}
		}
		else
		{
			foreach ($this->getShoppingCartItems() as $item)
			{
				$total += $item->getSubTotal($currency);
			}
			$total += $this->getTaxes($currency);
		}

		return $total;
	}

	public function getCoupons($reload = false)
	{
		if (!$this->getID())
		{
			return new ARSet();
		}

		if ((is_null($this->coupons) || $reload))
		{
			$this->coupons = $this->getRelatedRecordSet('OrderCoupon');
		}

		return $this->coupons;
	}

	public function getDiscounts()
	{
		if ($this->isFinalized->get())
		{
			return $this->getRelatedRecordSet('OrderDiscount');
		}
		else
		{
			return $this->getCalculatedDiscounts();
		}
	}

	public function getCalculatedDiscounts()
	{
		$discounts = new ARSet();
		foreach ($this->getDiscountActions() as $action)
		{
			if ($discount = $action->getOrderDiscount($this))
			{
				$discounts->add($discount);
			}
		}

		return $discounts;
	}

	public function getItemDiscountActions(OrderedItem $item)
	{
		$actions = array();

		foreach ($this->getDiscountActions() as $action)
		{
			if ($action->isItemDiscount() && $action->isItemApplicable($item))
			{
				$actions[] = $action;
			}
		}

		return $actions;
	}

	private function getTaxes(Currency $currency)
	{
		$id = $currency->getID();

		$this->taxes[$id] = array();
		$zone = $this->getDeliveryZone();
		if ($this->shipments)
		{
			foreach ($this->shipments as $shipment)
			{
				if ($shipment->getShippingService())
				{
					$shipmentRates = $zone->getShippingRates($shipment);
					$shipment->setAvailableRates($shipmentRates);
					$shipment->setRateId($shipment->getShippingService()->getID());
				}

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

		return array_sum($this->taxes[$id]);
	}

	public function getTaxAmount()
	{
		return $this->getTaxes($this->currency->get());
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
	public function isOrderable($setErrorMessages = false, $checkFields = false)
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

		// custom fields
		if ($checkFields && (!$this->getSpecification()->isValid($setErrorMessages  ? 'cartValidator' : null)))
		{
			return false;
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
			$item->price->set($item->product->get()->getItemPrice($item, $currency));
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

	public function getDueAmount()
	{
		return $this->getTotal($this->currency->get()) - $this->getPaidAmount();
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
		$array['taxes'] = $taxAmount = array();
		foreach ($this->taxes as $currencyId => $taxes)
		{
			$taxAmount[$currencyId] = 0;
			$array['taxes'][$currencyId] = array();
			$currency = Currency::getInstanceById($currencyId);

			foreach ($taxes as $taxId => $amount)
			{
				$taxAmount[$currencyId] += $amount;
				$array['taxes'][$currencyId][$taxId] = Tax::getInstanceById($taxId)->toArray();
				$array['taxes'][$currencyId][$taxId]['formattedAmount'] = $currency->getFormattedPrice($amount);
			}
		}

		$array['total'] = $total;

		$array['formattedTotal'] = $array['formattedTotalBeforeTax'] = array();
		if (is_array($array['total']))
		{
			foreach ($array['total'] as $id => $amount)
			{
				if (!isset($taxAmount[$id]))
				{
					$taxAmount[$id] = 0;
				}

				$array['formattedTotalBeforeTax'][$id] = $currencies[$id]->getFormattedPrice($amount - $taxAmount[$id]);
				$array['formattedTotal'][$id] = $currencies[$id]->getFormattedPrice($amount);
			}
		}

		// order type
		$array['isShippingRequired'] = (int)$this->isShippingRequired();

		// status
		$array['isReturned'] = (int)$this->isReturned();
		$array['isShipped'] = (int)$this->isShipped();
		$array['isAwaitingShipment'] = (int)$this->isAwaitingShipment();
		$array['isProcessing'] = (int)$this->isProcessing();

		// discounts
		$array['discountAmount'] = 0;
		foreach (array_merge($this->fixedDiscounts, $this->orderDiscounts) as $key => $discount)
		{
			$array['discounts'][$discount->getID() ? $discount->getID() : $key] = $discount->toArray();
			$array['discountAmount'] -= $discount->amount->get();
		}
		$array['formatted_discountAmount'] = $this->currency->get()->getFormattedPrice($array['discountAmount']);

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
		if (!$this->shipments || !$this->shipments->size())
		{
			if ($this->isFinalized->get() || $this->isMultiAddress->get())
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
			}

			$this->shipping->set(serialize($this->shipments));
		}

		return $this->shipments;
	}

	public function getDiscountConditions()
	{
		if ($this->isFinalized->get() || !$this->orderedItems)
		{
			return array();
		}

		ClassLoader::import('application.model.discount.DiscountCondition');
		return DiscountCondition::getOrderDiscountConditions($this);
	}

	public function getDiscountActions($reload = false)
	{
		if (is_null($this->discountActions) || $reload)
		{
			ClassLoader::import('application.model.discount.DiscountAction');
			$this->discountActions = DiscountAction::getByConditions($this->getDiscountConditions());
		}

		return $this->discountActions;
	}

	public function loadItemCategories()
	{
		// load additional categories
		$set = array();
		foreach ($this->getShoppingCartItems() as $item)
		{
			$set[$item->product->get()->getID()][$item->getID()] = $item;
		}

		foreach (ActiveRecordModel::getRecordSet('ProductCategory', new ARSelectFilter(new INCond(new ARFieldHandle('ProductCategory', 'productID'), array_keys($set))), array('Category')) as $additional)
		{
			foreach ($set[$additional->product->get()->getID()] as $item)
			{
				$item->registerAdditionalCategory($additional->category->get());
			}
		}
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
		if (!$this->isFinalized->get() && !$this->isMultiAddress->get())
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
