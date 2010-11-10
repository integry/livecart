<?php

ClassLoader::import('application.model.Currency');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.user.UserAddress');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductPrice');
ClassLoader::import('application.model.product.ProductSet');
ClassLoader::import('application.model.order.OrderCoupon');
ClassLoader::import('application.model.order.OrderedItem');
ClassLoader::import('application.model.order.Shipment');
ClassLoader::import('application.model.order.OrderDiscount');
ClassLoader::import('application.model.delivery.ShipmentDeliveryRate');
ClassLoader::import('application.model.eav.EavAble');
ClassLoader::import('application.model.eav.EavObject');
ClassLoader::import('application.model.order.Transaction');
ClassLoader::import('application.model.order.InvoiceNumberGenerator');
ClassLoader::import('application.model.discount.DiscountActionSet');
ClassLoader::import('application.model.businessrule.BusinessRuleController');
ClassLoader::import('application.model.businessrule.BusinessRuleContext');
ClassLoader::import('application.model.businessrule.interface.BusinessRuleOrderInterface');
ClassLoader::import('library.shipping.ShippingRateSet');

/**
 * Represents customers order - products placed in shopping basket or wish list
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class CustomerOrder extends ActiveRecordModel implements EavAble, BusinessRuleOrderInterface
{
	public $orderedItems = array();

	//public $shipments = new ARSet();

	private $removedItems = array();

	private $taxes = array();

	private $deliveryZone;

	private $taxZone;

	private $fixedDiscounts = array();

	private $orderDiscounts = array();

	private $discountActions = null;

	private $coupons = null;

	private $isOrderable = null;

	private $isProcessingRules;

	private $isRulesProcessed;

	private static $isEmptyAllowed = false;

	const STATUS_NEW = 0;
	const STATUS_PROCESSING = 1;
	const STATUS_AWAITING = 2;
	const STATUS_SHIPPED = 3;
	const STATUS_RETURNED = 4;

	const CHECKOUT_CART = 0;
	const CHECKOUT_USER = 1;
	const CHECKOUT_ADDRESS = 2;
	const CHECKOUT_SHIPPING = 3;
	const CHECKOUT_PAY = 4;

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
		$schema->registerField(new ARForeignKeyField("eavObjectID", "eavObject", "ID", 'EavObject', ARInteger::instance()), false);

		$schema->registerField(new ARField("invoiceNumber", ARVarchar::instance(40)));
		$schema->registerField(new ARField("checkoutStep", ARInteger::instance()));
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

		if ($user->getID())
		{
			$instance->setUser($user);
		}

		return $instance;
	}

	public static function getInstanceById($id, $loadData = self::LOAD_DATA, $loadReferencedRecords = false)
	{
		return parent::getInstanceById('CustomerOrder', $id, $loadData, $loadReferencedRecords);
	}

	public static function getInstanceByInvoiceNumber($id, $loadReferencedRecords = false)
	{
		return self::getRecordSet(select(eq(f('CustomerOrder.invoiceNumber'), $id)), $loadReferencedRecords)->shift();
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

		$this->event('before-load');

		$itemSet = $this->getRelatedRecordSet('OrderedItem', new ARSelectFilter(), array('Product', 'Category', 'DefaultImage' => 'ProductImage'));
		$this->orderedItems = $itemSet->getData();
		$products = $itemSet->extractReferencedItemSet('product');
		ProductPrice::loadPricesForRecordSet($products);

		$parentIDs = $products->extractReferencedItemSet('parent')->getRecordIDs();
		if ($parentIDs)
		{
			ActiveRecordModel::getRecordSet('Product', new ARSelectFilter(new INCond(new ARFieldHandle('Product', 'ID'), $parentIDs)), array('Category', 'ProductImage'));
		}

		if ($this->orderedItems)
		{
			if (!$this->shipments || !$this->shipments->size())
			{
				$this->shipments = $this->getRelatedRecordSet('Shipment', new ARSelectFilter(), array('UserAddress', 'ShippingService'));

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
			ARSet::buildFromArray($this->orderedItems)->extractReferencedItemSet('product', 'ProductSet')->loadVariations();

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

		if (!$this->isFinalized->get() && $this->orderedItems)
		{
			return $this->updateToStock();
		}

		$this->event('after-load');
	}

	public function loadAddresses()
	{
		$billingAddress = $this->billingAddress->get();
		if ($billingAddress)
		{
			$billingAddress->load(self::LOAD_REFERENCES);
			$billingAddress->getSpecification(); // todo: why EavObject not loaded automaticaly?
		}

		$shippingAddress = $this->shippingAddress->get();
		if ($shippingAddress)
		{
			$shippingAddress->load(self::LOAD_REFERENCES);
			$shippingAddress->getSpecification();
		}
	}

	public function loadAll()
	{
		$this->loadAddresses();
		$this->loadItems();
		$this->getShipments();
		$this->getSpecification();
		$this->loadDiscounts();
		$this->getPaymentMethod();
	}

	public function loadDiscounts()
	{
		if ($this->isExistingRecord())
		{
			$discounts = array_merge((array)$this->fixedDiscounts, $this->getRelatedRecordSet('OrderDiscount')->getData());
			$this->fixedDiscounts = array();
			foreach ($discounts as $discount)
			{
				$this->fixedDiscounts[$discount->getID()] = $discount;
			}
		}
	}

	public function validateCoupons()
	{
		if (!$this->isFinalized->get())
		{
			foreach ($this->getCoupons() as $coupon)
			{
				if (!$coupon->discountCondition->get())
				{
					$coupon->discountCondition->set(DiscountCondition::getInstanceByCoupon($coupon->couponCode->get()));
					$coupon->save();
				}

				if (!$coupon->discountCondition->get() || ($coupon->discountCondition->get()->couponCode->get() != $coupon->couponCode->get()) || !$coupon->isValid())
				{
					$coupon->delete();
					$this->getCoupons()->removeRecord($coupon);
				}
			}
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
			$this->orderedItems[] = $item;

			if (!$this->isFinalized->get() || !$this->shipments || !$this->shipments->size())
			{
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
		$shipments = $this->getShipments();
		$shipments->removeRecord($shipment);

		$shipment->order->set($this);
		$shipments->add($shipment);

		foreach ($shipment->getItems() as $item)
		{
			$this->addItem($item);
		}
	}

	public function updateCount(OrderedItem $item, $count)
	{
		$item->count->set($this->validateCount($item->getProduct(), $count));
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

		if ($step = $product->fractionalStep->get())
		{
			$count = floor($count / $step) * $step;
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
			if ($item->getProduct()->getID() == $id)
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
				//$item->markAsDeleted();
				unset($this->orderedItems[$key]);
				$this->resetShipments();
				break;
			}
		}
	}

	/**
	 *  Remove a shipment from order (including order items)
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
	 *  Remove a shipment from order, but leave items in order
	 */
	public function unsetShipment(Shipment $removedShipment)
	{
		foreach ($this->shipments as $key => $shipment)
		{
			if ($removedShipment === $shipment)
			{
				$this->shipments->remove($key);
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
	public function finalize($options = array())
	{
		if ($this->isFinalized->get() && empty($options['allowRefinalize']))
		{
			return;
		}

		self::beginTransaction();

		$this->event('before-finalize');

		$currency = $this->getCurrency();
		$this->loadAll();

		foreach ($this->getShipments() as $shipment)
		{
			if ($shipment->isExistingRecord())
			{
				$shipment->deleteRecordSet('ShipmentTax', new ARDeleteFilter());
			}

			$shipment->order->set($this);
			$shipment->save();

			// clone shipping addresses
			if ($shipment->shippingAddress->get())
			{
				$shippingAddress = clone $shipment->shippingAddress->get();
				$shippingAddress->save();
				$shipment->shippingAddress->set($shippingAddress);
			}
		}

		$reserveProducts = self::getApplication()->isInventoryTracking();

		foreach ($this->getShoppingCartItems() as $item)
		{
			// workround for failing tests.
			//if (!empty($options['customPrice']))
			//{
			$item->price->set($item->getSubTotalBeforeTax() / $item->getCount());
			//}

			$item->name->set($item->getProduct()->getParent()->name->get());
			$item->setValueByLang('name', 'sku', $item->getProduct()->sku->get());
			$item->save();

			// create sub-items for bundled products
			if ($item->getProduct()->isBundle())
			{
				foreach ($item->getProduct()->getBundledProducts() as $bundled)
				{
					$bundledItem = OrderedItem::getNewInstance($this, $bundled->relatedProduct->get(), $bundled->getCount());
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

		if (!$this->shippingAddress->get() && $this->user->get() && $this->user->get()->defaultShippingAddress->get() && $this->isShippingRequired())
		{
			$this->shippingAddress->set($this->user->get()->defaultShippingAddress->get()->userAddress->get());
		}

		if (!$this->billingAddress->get() && $this->user->get() && $this->user->get()->defaultBillingAddress->get())
		{
			$this->billingAddress->set($this->user->get()->defaultBillingAddress->get()->userAddress->get());
		}

		// clone billing/shipping addresses
		if (!$this->isFinalized->get())
		{
			foreach (array('billingAddress', 'shippingAddress') as $address)
			{
				if ($this->$address->get())
				{
					$this->$address->get()->load();
					$this->$address->get()->getSpecification();
					$cloned = clone $this->$address->get();
					$cloned->save();
					$cloned->loadEav();
					$this->$address->set($cloned);
				}
			}
		}

		// move wish list items to a separate order
		if ($this->getWishListItems())
		{
			$wishList = CustomerOrder::getNewInstance($this->user->get());
			foreach ($this->getWishListItems() as $item)
			{
				$wishList->addItem($item);
			}
			$wishList->save();
		}
		else
		{
			$wishList = null;
		}

		// set order total
		$this->totalAmount->set($this->getTotal(true));

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

		// @todo: remove the 0.99 multiplicator for currency conversion "tolerance" (a cent going missing when converting amounts between currencies back and forth)
		if ((round($this->totalAmount->get(), 2)  * 0.99) <= round($this->getPaidAmount(), 2))
		{
			$this->isPaid->set(true);
		}

		$this->dateCompleted->set(new ARSerializableDateTime());

		$this->isFinalized->set(true);

		// @todo: fix order total calculation
		$shipments = $this->shipments;
		unset($this->shipments);

		if (!$this->invoiceNumber->get())
		{
			$generator = InvoiceNumberGenerator::getGenerator($this);
			$saved = false;
			while (!$saved)
			{
				try
				{
					$this->invoiceNumber->set($generator->getNumber());
					$this->save();
					$saved = true;
				}
				catch (SQLException $e)
				{
				}
			}
		}

		$this->event('after-finalize');

		self::commit();

		// @todo: see above
		$this->shipments = $shipments;

		// force updating array representation
		$this->resetArrayData();

		return $wishList;
	}

	public function cancel()
	{
		if ($this->isCancelled->get())
		{
			return;
		}

		self::beginTransaction();

		$this->event('before-cancel');

		$this->isCancelled->set(true);

		foreach ($this->shipments as $shipment)
		{
			foreach ($shipment->getItems() as $item)
			{
				$item->unreserve();
				$item->save();
			}
		}

		$this->save();

		$this->event('after-cancel');

		self::commit();
	}

	public function restore()
	{
		if (!$this->isCancelled->get())
		{
			return;
		}

		$this->isCancelled->set(false);

		foreach ($this->shipments as $shipment)
		{
			foreach ($shipment->getItems() as $item)
			{
				$item->reserve();
				$item->save();
			}
		}

		$this->save();
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
		$existing = array();
		foreach ($this->orderedItems as $key => $item)
		{
			foreach ($existing as $eItem)
			{
				if ($item === $eItem)
				{
					unset($this->orderedItems[$key]);
				}
			}

			$existing[] = $item;
		}

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

				$byProduct[$item->getProduct()->getID() . $hash][(int)$item->isSavedForLater->get()][] = $item;
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

	public function setUser(User $user)
	{
		if ($this->user->get() && ($this->user->get()->getID() == $user->getID()))
		{
			return;
		}

		$this->user->set($user);
		$this->setCheckoutStep(self::CHECKOUT_USER);

		foreach (array(array('defaultBillingAddress' => 'billingAddress'),
					   array('defaultShippingAddress' => 'shippingAddress'),
					   array('defaultBillingAddress' => 'shippingAddress'),
					   ) as $pair)
		{
			$userAd = array_shift(array_keys($pair));
			$orderAd = reset($pair);
			if ($user->$userAd->get())
			{
				$user->$userAd->get()->load();
				$this->$orderAd->set($user->$userAd->get()->userAddress->get());
			}
		}

		$this->resetShipments();
		$this->getShipments();
	}

	public function setCheckoutStep($step)
	{
		if ($step <= $this->checkoutStep->get())
		{
			return false;
		}

		$this->checkoutStep->set($step);
		$this->save();
	}

	public static function allowEmpty($allow = true)
	{
		self::$isEmptyAllowed = $allow;
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
			$this->getCurrency();

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
			$this->getCurrency();

			// reorder shipments when cart items are modified
			$this->resetShipments();

			$this->totalAmount->set($this->getTotal(true));
		}
		else
		{
			if (!$this->isShippingRequired())
			{
				//$this->shippingAddress->setNull();
			}
		}

		if ($this->isModified() || $isModified)
		{
			$this->serializeShipments();
		}

		if (!$this->isFinalized->get() && !$this->orderedItems && !$allowEmpty && !self::$isEmptyAllowed)
		{
			$this->delete();
			return false;
		}

		if ($this->user->get())
		{
			$this->user->get()->invalidateSessionCache();
		}

		return parent::save();
	}

	public function serializeShipments()
	{
		$this->shipping->set(($this->isFinalized->get() || $this->isMultiAddress->get()) ? '' : serialize($this->shipments));
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

	public function getSubTotalByCurrency(Currency $currency)
	{
		if ($this->getCurrency()->getID() != $currency->getID())
		{
			$current = $this->getCurrency();
			$this->changeCurrency($currency);
			$subtotal = $this->getSubTotal(false);
			$this->changeCurrency($current);
		}
		else
		{
			return $this->getSubTotal(false);
		}
	}

	public function getSubTotal($applyDiscounts = true)
	{
		$subTotal = 0;
		foreach ($this->orderedItems as $item)
		{
			if (!$item->isSavedForLater->get())
			{
				$subTotal += $item->getSubTotal(false, $applyDiscounts);
			}
		}

		//$subTotal = $this->getCurrency()->round($subTotal);

		return $subTotal;
	}

	public function getSubTotalBeforeTax()
	{
		if (!$this->shipments)
		{
			return $this->getSubTotal();
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
	public function getTotal($recalculateAmount = false)
	{
		if (is_null($this->orderTotal) && $this->isFinalized->get())
		{
			$this->orderTotal = $this->totalAmount->get();
		}

		if (is_null($this->orderTotal) || $recalculateAmount)
		{
			if ($this->isFinalized->get() && !$recalculateAmount)
			{
				$this->getTaxes();
				$total = $this->totalAmount->get();
			}
			else
			{
				$this->reset();
				$this->processBusinessRules();

				$total = $this->calculateTotal();

				if ($discountAmount = $this->getFixedDiscountAmount())
				{
					if ($this->shipments)
					{
						foreach ($this->shipments as $shipment)
						{
							$shipment->applyFixedDiscount($total, $discountAmount);
						}
					}

					$total = $this->calculateTotal(false);

					if (!$this->shipments)
					{
						$total -= $discountAmount;
					}
				}

				if ($total < 0)
				{
					$total = 0;
				}
			}

			$this->orderTotal = $total;
		}

		return $this->getCurrency()->round($this->orderTotal);
	}

	public function reset()
	{
		$this->deliveryZone = null;
		$this->taxZone = null;
		$this->orderTotal = null;
		$this->orderDiscounts = array();

		foreach ($this->getShoppingCartItems() as $item)
		{
			$item->reset();
		}
	}

	public function getFixedDiscountAmount()
	{
		$amount = 0;
		foreach ($this->fixedDiscounts as $discount)
		{
			$amount += $discount->amount->get();
		}

		foreach ($this->orderDiscounts as $discount)
		{
			$amount += $discount->amount->get();
		}

		return $amount;
	}

	public function registerFixedDiscount(OrderDiscount $discount)
	{
		$this->fixedDiscounts[$discount->getID()] = $discount;
	}

	public function registerOrderDiscount(OrderDiscount $discount)
	{
		$this->orderDiscounts[$discount->getID()] = $discount;
	}

	public function getOrderDiscounts()
	{
		return array_merge($this->fixedDiscounts, $this->orderDiscounts);
	}

	/**
	 *	Get full order total, including taxes and shipping, but excluding fixed discounts
	 */
	public function calculateTotal($recalculateAmounts = true)
	{
		$total = 0;

		if ($this->shipments instanceof ARSet && !$this->shipments->size())
		{
			$this->shipments = null;
		}

		if (!$this->shipments)
		{
			$this->getShipments();
		}

		if ($this->shipments)
		{
			// @todo: the tax calculation is slightly off when it's calculated for the first time, so it has to be called twice
			$this->getTaxes();
			foreach ($this->shipments as $shipment)
			{
				$shipment->order->set($this);
				$total += $shipment->getTotal($recalculateAmounts);
			}
		}
		else
		{
			foreach ($this->getShoppingCartItems() as $item)
			{
				$total += $item->getSubTotal(false);
			}

			$total += $this->getTaxes();
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
			$this->coupons = $this->getRelatedRecordSet('OrderCoupon', null, array('DiscountCondition'));
			$this->validateCoupons();
		}

		return $this->coupons;
	}

	public function hasCoupon($code)
	{
		foreach ($this->getCoupons() as $coupon)
		{
			if ($coupon->couponCode->get() == $code)
			{
				return true;
			}
		}
	}

	private function getTaxes()
	{
		$this->taxes = array();
		$zone = $this->getTaxZone();
		if ($this->shipments)
		{
			foreach ($this->shipments as $shipment)
			{
				/*
				if ($shipment->getShippingService())
				{
					$shipment->getAvailableRates();
					$shipment->setRateId($shipment->getShippingService()->getID());
				}
				*/

				foreach ($shipment->getTaxes() as $tax)
				{
					$taxId = ($tax->taxRate->get() && $tax->taxRate->get()->tax->get()) ? $tax->taxRate->get()->tax->get()->getID() : 0;
					if (!isset($this->taxes[$taxId]))
					{
						$this->taxes[$taxId] = 0;
					}

					$this->taxes[$taxId] += $tax->getAmount();
				}
			}
		}

		return array_sum($this->taxes);
	}

	public function getTaxAmount()
	{
		return $this->getTaxes();
	}

	public function getTaxBreakdown()
	{
		$this->getTaxes();
		return $this->taxes;
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
			if (!$item->getProduct()->isDownloadable())
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
		if (!$this->isRulesProcessed)
		{
			$this->processBusinessRules();
		}

		ClassLoader::import('application.model.order.OrderException');

		$app = $this->getApplication();
		$c = $app->getConfig();

		if (!is_null($this->isOrderable) && !$this->isOrderable)
		{
			return false;
		}


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
		$total = $this->getSubTotalByCurrency($this->getApplication()->getDefaultCurrency());

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

		// contains more items than in stock
		if ($this->updateToStock(false))
		{
			return false;
		}

		return true;
	}

	public function setOrderable($isOrderable)
	{
		$this->isOrderable = $isOrderable;
	}

	public function updateToStock($save = true)
	{
		$result = array();

		if (!$this->orderedItems)
		{
			$this->loadItems();
		}

		// remove disabled items
		foreach ($this->getOrderedItems() as $item)
		{
			$product = $item->getProduct();
			if (!$product || (!$product->isEnabled->get() || !$product->getParent()->isEnabled->get()))
			{
				$item->delete();
				$this->removeItem($item);
				$result['delete'][] = $item->toArray();
			}
		}

		if (!self::getApplication()->isInventoryTracking())
		{
			return $result;
		}

		foreach ($this->getOrderedItems() as $item)
		{
			$product = $item->getProduct();

			// previously out-of-stock item now back in stock
			if ((OrderedItem::OUT_OF_STOCK == $item->isSavedForLater->get()) && $product->isAvailable())
			{
				$item->isSavedForLater->set(OrderedItem::CART);
				$result['in'][] = array('id' => $item->getID());
			}

			if (!$product->isBackOrderable->get() && !$item->isSavedForLater->get() && !$product->isBundle())
			{
				if (!$product->isDownloadable() || $product->isInventoryTracked())
				{
					if (($product->stockCount->get() <= 0))
					{
						$item->isSavedForLater->set(OrderedItem::OUT_OF_STOCK);
						$result['out'][] = array('id' => $item->getID());
					}
					else if ($product->stockCount->get() < $item->count->get())
					{
						$count = $item->count->get();
						$item->count->set($product->stockCount->get());
						$result['count'][] = array('id' => $item->getID(), 'from' => $count, 'to' => $item->count->get());
					}
				}
			}
		}

		if ($result && $save)
		{
			$this->save();
		}

		return $result;
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
			$item->price->set($item->getProduct()->getItemPrice($item, true, $currency));
			$item->setItemPrice($item->price->get());
			$item->save();
		}

		$this->save();
	}

	public function getPaidAmount()
	{
		$transactions = $this->getTransactions($this->getPaidTransactionFilter());
		$paid = 0;
		foreach ($transactions as $transaction)
		{
			$paid += $transaction->amount->get();
		}

		return $paid;
	}

	private function getPaidTransactionFilter()
	{
		$filter = new ARSelectFilter(new InCond(new ARFieldHandle('Transaction', 'type'), array(Transaction::TYPE_AUTH, Transaction::TYPE_SALE)));
		$filter->mergeCondition(new NotEqualsCond(new ARFieldHandle('Transaction', 'isVoided'), true));
		return $filter;
	}

	public function getDueAmount()
	{
		return $this->getTotal() - $this->getPaidAmount();
	}

	public function setPaymentMethod($method)
	{
		$this->paymentMethod = $method;
		$this->getDiscountActions();
	}

	public function getPaymentMethod()
	{
		if ($this->isFinalized->get() && is_null($this->paymentMethod))
		{
			foreach ($this->getTransactions($this->getPaidTransactionFilter()) as $transaction)
			{
				if ($transaction->method->get())
				{
					$this->paymentMethod = $transaction->method->get();
					break;
				}

				// offline methods
				else if ($transaction->serializedData->get())
				{
					$array = $transaction->toArray();
					if (!empty($array['serializedData']['handlerID']))
					{
						$this->paymentMethod = $array['serializedData']['handlerID'];
						break;
					}
				}
			}
		}

		if (is_null($this->paymentMethod))
		{
			$this->paymentMethod = '';
		}

		return $this->paymentMethod;
	}

	public function getCurrency()
	{
		if(!$this->currency->get())
		{
			$this->currency->set(self::getApplication()->getDefaultCurrency());
		}

		return $this->currency->get();
	}

	public function getCompletionDate()
	{
		return $this->dateCompleted->get();
	}

	/*####################  Data array transformation ####################*/

	/**
	 *  Creates an array representation of the shopping cart
	 */
	public function toArray($options = array())
	{
		$currency = $this->getCurrency();
		$id = $currency->getID();

		if (is_array($this->orderedItems))
		{
			foreach ($this->orderedItems as $item)
			{
				if (!$item->getProduct()->isPricingLoaded())
				{
					if (!isset($products))
					{
						$products = new ARSet();
					}
					$products->unshift($item->getProduct());
				}
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
		$total[$id] = $this->getTotal();

		// taxes
		$array['taxes'] = $taxAmount = array();
		$taxAmount[$id] = 0;
		$array['taxes'][$id] = array();

		foreach ($this->taxes as $taxId => $amount)
		{
			if ($amount > 0)
			{
				$taxAmount[$id] += $amount;

				$tax = Tax::getInstanceById($taxId)->toArray();
				$tax['amount'] = $amount;
				$tax['formattedAmount'] = $currency->getFormattedPrice($amount);
				$array['taxes'][$id][] = $tax;
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

				$array['formattedTotalBeforeTax'][$id] = $currency->getFormattedPrice($amount - $taxAmount[$id]);
				$array['formattedTotal'][$id] = $currency->getFormattedPrice($amount);
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
		$array['formatted_discountAmount'] = $this->getCurrency()->getFormattedPrice($array['discountAmount']);

		// coupons
		if (!is_null($this->coupons))
		{
			$array['coupons'] = $this->coupons->toArray();
		}

		// payments
		if (isset($options['payments']))
		{
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
		}

		// items subtotal
		$array['itemSubtotal'] = $array['itemDisplayPriceTotal'] = $array['itemSubtotalWithoutTax'] = 0;
		foreach ($this->getOrderedItems() as $item)
		{
			$array['itemSubtotal'] += $item->getSubtotal(true);
			$array['itemSubtotalWithoutTax'] += $item->getSubtotal(false);
			$array['itemDisplayPriceTotal'] += $item->getDisplayPrice($currency) * $item->count->get();
		}

		$array['itemDiscount'] = $array['itemDisplayPriceTotal'] - $array['itemSubtotal'];
		$array['itemDiscountReverse'] = $array['itemDiscount'] * -1;

		// shipping subtotal
		$array['shippingSubtotal'] = null;
		$array['shippingSubtotalWithoutTax'] = null;
		if ($this->shipments)
		{
			foreach ($this->shipments as $shipment)
			{
				$shipmentShipping = $shipment->getShippingTotalWithTax();
				if (!is_null($shipmentShipping))
				{
					$array['shippingSubtotal'] += $shipment->getShippingTotalWithTax();
					$array['shippingSubtotalWithoutTax'] += $shipment->getShippingTotalBeforeTax();
				}
			}
		}

		$array['subtotalBeforeTaxes'] = $array['itemSubtotalWithoutTax'] + $array['shippingSubtotalWithoutTax'];

		foreach (array('amountPaid', 'amountNotCaptured', 'amountDue', 'itemSubtotal', 'shippingSubtotal', 'shippingSubtotalWithoutTax', 'subtotalBeforeTaxes', 'totalAmount', 'itemDiscountReverse', 'itemDiscount', 'itemSubtotalWithoutTax') as $key)
		{
			if (isset($array[$key]))
			{
				$array['formatted_' . $key] = $currency->getFormattedPrice($array[$key]);
			}
		}

		if (!$array['isFinalized'])
		{
			//$this->isRulesProcessed = false;
			$isOrderable = $this->isOrderable();
			if ($isOrderable instanceof OrderException)
			{
				$array['error'] = $isOrderable->toArray();
			}

			$array['isOrderable'] = !($isOrderable instanceof OrderException) && $isOrderable;

			$array['isShippingSelected'] = $this->isShippingSelected();
			$array['isAddressSelected'] = ($this->shippingAddress->get() && $this->billingAddress->get());
		}

		// otherwise left empty on payment page for some reason...
		if ($this->billingAddress->get())
		{
			$array['BillingAddress'] = $this->billingAddress->get()->toArray();
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

	/**
	 * alias for getShoppingCartItems()
	 */
	public function getPurchasedItems()
	{
		return $this->getShoppingCartItems();
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
			if ($item->getProduct()->getID() == $product->getID())
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
			$productIDs[] = $item->getProduct()->getID();
		}

		$products = ActiveRecordModel::getInstanceArray('Product', $productIDs);

		foreach ($this->orderedItems as $item)
		{
			$id = $item->getProduct()->getID();

			if (isset($products[$id]))
			{
				$item->product->set($products[$id]);
			}
			else
			{
				$this->removeProduct($item->getProduct());
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
			if ($this->getID() && ($this->isFinalized->get() || $this->isMultiAddress->get()))
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
					if ($item->getProduct()->isDownloadable())
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
						if ($item->getProduct()->isDownloadable())
						{
							if (!isset($downloadable))
							{
								$downloadable = Shipment::getNewInstance($this);
							}

							$downloadable->addItem($item);
						}
						else if ($item->getProduct()->isSeparateShipment->get())
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

				$this->event('getShipments');

				$this->shipping->set(serialize($this->shipments));
			}
		}

		return $this->shipments;
	}

	public function getDiscountConditions($reload = false)
	{
		if ($reload)
		{
			BusinessRuleController::clearCache();
		}

		if (!$this->getShoppingCartItemCount())
		{
			return array();
		}

		$controller = new BusinessRuleController($this->getBusinessRuleContext());
		return $controller->getValidConditions();
	}

	public function getBusinessRuleContext()
	{
		if (!$this->businessRuleContext)
		{
			$context = new BusinessRuleContext();
			$context->setOrder($this);
			if ($this->user->get())
			{
				$context->setUser($this->user->get());
			}

			$this->businessRuleContext = $context;
		}

		return $this->businessRuleContext;
	}

	public function getDiscountActions($reload = false)
	{
		if ($reload)
		{
			BusinessRuleController::clearCache();
		}

		if (!$this->orderedItems)
		{
			return array();
		}

		$controller = new BusinessRuleController($this->getBusinessRuleContext());
		return $controller->getActions();
	}

	public function processBusinessRules($reload = false)
	{
		if ($this->isFinalized->get())
		{
			return;
		}

		// avoid loops
		if ($this->isProcessingRules)
		{
			return;
		}

		$this->isProcessingRules = true;

		foreach ($this->getShoppingCartItems() as $item)
		{
			$item->reset();
		}

		foreach ($this->getDiscountActions($reload) as $ruleAction)
		{
			if ($ruleAction->isOrderAction())
			{
				$ruleAction->applyToOrder($this);
			}
			else
			{
				foreach ($this->getShoppingCartItems() as $item)
				{
					if ($ruleAction->isItemApplicable($item))
					{
						$ruleAction->applyToItem($item);
					}
				}
			}
		}

		$this->isProcessingRules = false;
		$this->isRulesProcessed = true;
	}

	public function loadItemCategories()
	{
		// load additional categories
		$set = array();
		foreach ($this->getShoppingCartItems() as $item)
		{
			$set[$item->getProduct()->getID()][$item->getID()] = $item;
		}

		foreach (ActiveRecordModel::getRecordSet('ProductCategory', new ARSelectFilter(new INCond(new ARFieldHandle('ProductCategory', 'productID'), array_keys($set))), array('Category')) as $additional)
		{
			foreach ($set[$additional->product->get()->getID()] as $item)
			{
				$item->registerAdditionalCategory($additional->category->get());
			}
		}
	}

	public function getTaxZone($forceReset = false)
	{
		ClassLoader::import("application.model.delivery.DeliveryZone");
		if (!$this->taxZone || $forceReset)
		{
			if ($this->isShippingRequired() && $this->shippingAddress->get())
			{
				$this->taxZone = DeliveryZone::getZoneByAddress($this->shippingAddress->get(), DeliveryZone::TAX_RATES);
			}
			else
			{
				$this->taxZone = DeliveryZone::getDefaultZoneInstance();
			}
		}
		return $this->taxZone;
	}

	public function getDeliveryZone($forceReset = false)
	{
		ClassLoader::import("application.model.delivery.DeliveryZone");
		if (!$this->deliveryZone || $forceReset)
		{
			if ($this->isShippingRequired() && $this->shippingAddress->get())
			{
				$this->deliveryZone = DeliveryZone::getZoneByAddress($this->shippingAddress->get(), DeliveryZone::SHIPPING_RATES);
			}
			else
			{
				$this->deliveryZone = DeliveryZone::getDefaultZoneInstance();
			}
		}
		return $this->deliveryZone;
	}

	public function setDeliveryZone(DeliveryZone $zone)
	{
		$this->deliveryZone = $zone;
	}

	public function setTaxZone(DeliveryZone $zone)
	{
		$this->taxZone = $zone;
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

	public function loadRequestData(Request $request)
	{
		$this->getSpecification()->loadRequestData($request);
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
			$productIds[] = $item->getProduct()->getID();
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

	public function __get($name)
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

	public function __clone()
	{
		parent::__clone();

		$this->isFinalized->set(false);
		$this->isPaid->set(false);
		$this->isCancelled->set(false);
		$this->dateCompleted->set(null);
		$this->invoiceNumber->set(null);

		$original = $this->originalRecord;

		$this->shipments = new ARSet();
		$this->orderedItems = array();

		if ($original->isFinalized->get())
        {
                foreach ($original->getShipments() as $shipment)
                {
                        $cloned = clone $shipment;
                        $cloned->order->set($this);

                        if ($this->isMultiAddress->get())
                        {
                                $this->addShipment($cloned);
                        }
                        else
                        {
                                foreach ($cloned->getItems() as $item)
                                {
                                        $this->addItem($item);
                                }
                        }
                }
        }
        else
        {
                foreach ($original->getOrderedItems() as $item)
                {
                        $this->addItem(clone $item);
                }
        }

		if ($this->isMultiAddress->get())
		{
			$this->save(true);

			foreach ($this->getShipments() as $shipment)
			{
				if ($shipment->shippingAddress->get())
				{
					$shipment->shippingAddress->set($this->getClonedAddress($shipment->shippingAddress->get(), false));
				}

				$shipment->save();

				foreach ($shipment->getItems() as $item)
				{
					$item->shipment->set($shipment);
					$item->save();
				}
			}
		}

		// addresses
		if ($this->billingAddress->get())
		{
			$this->billingAddress->set($this->getClonedAddress($this->billingAddress->get(), true));
		}

		if ($this->shippingAddress->get())
		{
			$this->shippingAddress->set($this->getClonedAddress($this->shippingAddress->get(), false));
		}
		$this->save();
	}

	/**
	 *  Try to match an order address to user address and return ID on success
	 *
	 *  Order addresses are stored in separate records after the order is completed,
	 *  so that the user couldn't change them after finishing the order by editing his address book
	 *
	 *  @todo: why are the address reloads necessary?
	 */
	private function getClonedAddress($address, $isBilling)
	{
		$address = $address->toArray();
		$addressString = $address['compact'];

		$user = $this->user->get();
		$addresses = $isBilling ? $user->getBillingAddressSet() : $user->getShippingAddressSet();

		foreach ($addresses as $address)
		{
			$address->reload();
			$address->userAddress->get()->reload();

			if ($address->userAddress->get()->toString(", ") == $addressString)
			{
				return $address->userAddress->get();
			}
		}

		if ($addresses->size())
		{
			return $addresses->get(0)->userAddress->get();
		}

		return null;
	}

	public function __destruct()
	{
		foreach ($this->orderedItems as $item)
		{
			$item->__destruct();
			$item->destruct();
		}

		$this->orderedItems = array();

		foreach ($this->removedItems as $item)
		{
			$item->__destruct();
			$item->destruct();
		}

		$this->removedItems = array();

		$this->taxes = array();

		if (isset($this->shipments))
		{
			foreach ($this->shipments as $shipment)
			{
				$shipment->__destruct();
				$shipment->destruct();
			}
		}

		$this->shipments = array();

		parent::destruct(array('userID', 'billingAddressID', 'shippingAddressID'));
	}

	public static function getStatusName($status)
	{
		$statuses = array(
							-2 => '_status_canceled',
							-1 => '_awaiting_payment',
							self::STATUS_NEW => '_status_new',
							self::STATUS_AWAITING => '_status_awaiting',
							self::STATUS_SHIPPED => '_status_shipped',
							self::STATUS_RETURNED => '_status_returned',
							self::STATUS_PROCESSING => '_status_processing'
						);

		return isset($statuses[$status]) ? $statuses[$status] : '_status_processing';
	}
}

?>
