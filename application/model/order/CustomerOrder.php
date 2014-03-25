<?php

namespace order;

use \product\Product;
use \Currency;
use \delivery\DeliveryZone;
use \user\UserAddress;

require_once(dirname(dirname(__file__)) . '/businessrule/interface/BusinessRuleOrderInterface.php');

/**
 * Represents customers order - products placed in shopping basket or wish list
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class CustomerOrder extends \ActiveRecordModel implements \eav\EavAble, \businessrule\BusinessRuleOrderInterface
{
	private $removedItems = array();

	private $taxes = array();

	private $taxDetails = array();

	private $deliveryZone;

	private $taxZone;

	private $fixedDiscounts = array();

	private $orderDiscounts = array();

	private $discountActions = null;

	private $coupons = null;

	private $isOrderable = null;

	private $isProcessingRules;

	private $isRulesProcessed;
	
	private $orderTotal;

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

	public $ID;
//	public $parentID", "CustomerOrder", "ID", "CustomerOrder;
	public $invoiceNumber;
	public $checkoutStep;
	public $dateCreated;
	public $dateCompleted;
	public $dateDue;
	public $startDate; // including (first day of period)
	public $endDate; // including (last day of period)
	public $totalAmount;
	public $capturedAmount;
	public $isMultiAddress;
	public $isFinalized;
	public $isPaid;
	public $isCancelled;
	public $isRecurring;
	public $status;
	public $shipping;
	public $rebillsLeft;
	
	public function initialize()
	{
		$this->belongsTo('currencyID', '\Currency', 'ID', array('alias' => 'Currency'));
		$this->belongsTo('userID', 'user\User', 'ID', array('alias' => 'User'));
		$this->hasOne('eavObjectID', 'eav\EavObject', 'ID', array('alias' => 'EavObject'));
		$this->hasOne('billingAddressID', 'user\UserAddress', 'ID', array('alias' => 'BillingAddress'));
		$this->hasOne('shippingAddressID', 'user\UserAddress', 'ID', array('alias' => 'ShippingAddress'));

        $this->hasMany('ID', '\order\OrderedItem', 'customerOrderID', array('alias' => 'OrderedItems'));
        $this->hasMany('ID', '\order\Shipment', 'orderID', array('alias' => 'Shipments'));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(\user\User $user)
	{
		$instance = new self();
		
		if ($user->getID())
		{
			$instance->user = $user;
		}
		
		$instance->currencyID = 'USD';

		return $instance;
	}

	public static function getInstanceByInvoiceNumber($id, $loadReferencedRecords = false)
	{
		return self::getRecordSet(select(eq(f('CustomerOrder.invoiceNumber'), $id)), $loadReferencedRecords)->shift();
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function loadItems()
	{
		return;
		
		if (!$this->getID())
		{
			return false;
		}

		$this->event('before-load');

		$this->orderedItems = $this->getRelated('OrderedItems');

		$products = $itemSet->extractReferencedItemSet('product');
		ProductPrice::loadPricesForRecordSet($products);

		$parentIDs = $products->extractReferencedItemSet('parent')->getRecordIDs();
		if ($parentIDs)
		{
			ActiveRecordModel::getRecordSet('Product', new ARSelectFilter(new INCond('Product.ID', $parentIDs)), array('Category', 'ProductImage'));
		}

		if ($this->orderedItems)
		{
			if (!$this->shipments || !$this->shipments->count())
			{
				$this->shipments = $this->getRelatedRecordSet('Shipment', new ARSelectFilter(), array('UserAddress', 'ShippingService'));
			}

			if (!$this->shipments->count() && !$this->isFinalized)
			{
				$this->shipments = unserialize($this->shipping);
			}

			OrderedItemOption::loadOptionsForItemSet(ARSet::buildFromArray($this->orderedItems));
			ARSet::buildFromArray($this->orderedItems)->extractReferencedItemSet('product', 'ProductSet')->loadVariations();

			foreach ($this->orderedItems as $key => $item)
			{
				if ($item->parent)
				{
					$item->parent->registerSubItem($item);
					unset($this->orderedItems[$key]);
				}
			}

			Product::loadAdditionalCategoriesForSet(ARSet::buildFromArray($this->orderedItems)->extractReferencedItemSet('product'));
		}

		if (!$this->isFinalized && $this->orderedItems)
		{
			return $this->updateToStock();
		}

		$this->event('after-load');
	}

	public function loadAddresses()
	{
		$billingAddress = $this->billingAddress;
		if ($billingAddress)
		{
			$billingAddress->load(self::LOAD_REFERENCES);
			$billingAddress->getSpecification(); // todo: why EavObject not loaded automaticaly?
		}

		$shippingAddress = $this->shippingAddress;
		if ($shippingAddress)
		{
			$shippingAddress->load(self::LOAD_REFERENCES);
			$shippingAddress->getSpecification();
		}
	}

	public function loadAll()
	{
return;
		$this->loadAddresses();
		$this->loadItems();
		$this->getShipments();
		$this->getSpecification();
		$this->loadDiscounts();
		$this->getPaymentMethod();
	}

	public function loadDiscounts()
	{
		if ($this->getID())
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
		if (!$this->isFinalized)
		{
			foreach ($this->getCoupons() as $coupon)
			{
				if (!$coupon->discountCondition)
				{
					$coupon->discountCondition = DiscountCondition::getInstanceByCoupon($coupon->couponCode);
					$coupon->save();
				}

				if (!$coupon->discountCondition || ($coupon->discountCondition->couponCode != $coupon->couponCode) || !$coupon->isValid())
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
	public function addProduct(\product\Product $product, $count = 1, $ignoreAvailability = false, Shipment $shipment = null)
	{
		$this->save();

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
			
		/*
		$instance = new self();
		$instance->customerOrder = $this;
		$instance->product = $product;
		$instance->count = $count;
		$item = $instance;
		*/
			
			$item = OrderedItem::getNewInstance($this, $product, $count);
//			var_dump($item->product->getID());
			//$item->save();

			/*
			$items = persist($this->orderedItems);
			$items[] = $item;
			*/
			$this->orderedItems = array($item);

			if (!$this->isFinalized || !$this->shipments || !$this->shipments->count())
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
					$shipment = $this->shipments->shift();
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

		$shipment->order = $this;
		$shipments->add($shipment);

		foreach ($shipment->getItems() as $item)
		{
			$this->addItem($item);
		}
	}

	public function updateCount(OrderedItem $item, $count)
	{
		$item->count = $this->validateCount($item->getProduct(), $count);
	}

	private function validateCount(\product\Product $product, $count)
	{
		if (round($count) != $count && !$product->isFractionalUnit)
		{
			$count = round($count);
		}

		if (0 >= $count)
		{
			$count = 0;
		}
		else if ($product->minimumQuantity > $count)
		{
			$count = $product->minimumQuantity;
		}

		if ($step = $product->fractionalStep)
		{
			$count = floor($count / $step) * $step;
		}

		return $count;
	}

	/**
	 *  Add a product to wish list
	 */
	public function addToWishList(\product\Product $product)
	{
		$item = OrderedItem::getNewInstance($this, $product, 1);
		$item->isSavedForLater = true;
		$this->orderedItems[] = $item;
	}

	/**
	 *  Remove a product (all product items) from shopping basket or wish list
	 */
	public function removeProduct(\product\Product $product)
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
		$orderedItem->delete();
		
		/*
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
		*/
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
					if($this->orderedItems[$i]->shipment && ($this->orderedItems[$i]->shipment === $removedShipment))
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
		$orderedItem->customerOrder = $this;
		$this->orderedItems[] = $orderedItem;

		if ($orderedItem->shipment)
		{
			$orderedItem->shipment->addItem($orderedItem);
		}
	}

	public function isFirstInvoice($recurringItem)
	{
		if (($recurringItem instanceof RecurringItem) == false)
		{
			// echo 'not recurring item!';
			return false;
		}
		$parentID = $this->parentID->getID();
		if (!$parentID)
		{
			return false;
		}

		$d = ActiveRecordModel::getDataBySql('SELECT
			 ri.*
		FROM
			CustomerOrder cu
			INNER JOIN CustomerOrder pcu ON cu.parentID = pcu.id
			INNER JOIN OrderedItem oi ON pcu.ID = oi.customerOrderID
			INNER JOIN RecurringItem ri ON ri.orderedItemID = oi.ID
		WHERE
			cu.parentID='.$parentID.'
	');

	// ??

	// AND ri.ID='.$recurringItem->getID().'
	echo 'recurring id: '.$recurringItem->getID();
	print_r($d);

	// print_r($d);

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
		$rebillsLeft = 0;

		if ($this->isFinalized && empty($options['allowRefinalize']))
		{
			return;
		}

		$this->getDI()->get('db')->begin();

		$this->event('before-finalize');

		$this->loadAll();
		$currency = $this->getCurrency();

		foreach ($this->getShipments() as $shipment)
		{
			if ($shipment->isExistingRecord())
			{
				$shipment->deleteRecordSet('ShipmentTax', new ARDeleteFilter());
			}

			$shipment->order = $this;
			$shipment->save();

			// clone shipping addresses
			if ($shipment->shippingAddress)
			{
				$shippingAddress = clone $shipment->shippingAddress;
				$shippingAddress->save();
				$shipment->shippingAddress = $shippingAddress;
			}
		}

		$reserveProducts = $this->getDI()->get('application')->isInventoryTracking();

		$rebillsLeft = 0;

		$groupedRebillsLeft=array();

		$isFirstOrder = !$this->parentID;

		foreach ($this->getShoppingCartItems() as $item)
		{
			if (empty($options['customPrice']))
			{
				$item->price = $item->getSubTotalBeforeTax() / $item->getCount();
			}

			$item->name = $item->getProduct()->getParent()->name;
			$item->setValueByLang('name', 'sku', $item->getProduct()->sku);
			$item->save();

			// create sub-items for bundled products
			if ($item->getProduct()->isBundle())
			{
				foreach ($item->getProduct()->getBundledProducts() as $bundled)
				{
					$bundledItem = OrderedItem::getNewInstance($this, $bundled->relatedProduct, $bundled->getCount());
					$bundledItem->parent = $item;
					$bundledItem->save();
				}
			}

			// reserve products if inventory is enabled
			if ($reserveProducts)
			{
				$item->reserve();
				$item->save();
			}

			if ($isFirstOrder)
			{
				$ri = RecurringItem::getInstanceByOrderedItem($item);
				if ($ri && $ri->isExistingRecord())
				{
					$rebillCount = $ri->rebillCount;
					// also here recurring item grouping
					$key = sprintf('%s_%s_%s',
						$ri->periodType, $ri->periodLength,
						$rebillCount === null
							? 'NULL'
							: $rebillCount
					);
					if ($rebillCount !== null)
					{
						$groupedRebillsLeft[$key] = $rebillCount;
					}
					else
					{
						$groupedRebillsLeft[$key] = -1; // -1 means infinite rebill count
					}
					$this->isRecurring = true; // orders with at least one recurring billing plan must have isRecurring flag, if already not set.
				}
			}
			else
			{
				$rparentItem = $item->recurringParentID;
				if ($rparentItem)
				{
					$ri = RecurringItem::getInstanceByOrderedItem($rparentItem, true);
					$ri->reload(); // if was bulk update, then cached data are outdated.
					if ($ri && $ri->isExistingRecord())
					{
						// are RecurringItems grouped?
						// probably yes..
						$rebillsLeft = $ri->rebillCount - $ri->processedRebillCount;
					}
				}
			}
		}

		if ($isFirstOrder)
		{
			foreach($groupedRebillsLeft as $value)
			{
				if ($value == -1)
				{
					$rebillsLeft = -1;
					break;
				}
				else
				{
					$rebillsLeft += $value;
				}
			}
		}

		if (!$this->shippingAddress && $this->user && $this->user->defaultShippingAddress && $this->isShippingRequired())
		{
			$this->shippingAddress = $this->user->defaultShippingAddress->userAddress;
		}

		if (!$this->billingAddress && $this->user && $this->user->defaultBillingAddress)
		{
			$this->billingAddress = $this->user->defaultBillingAddress->userAddress;
		}

		// clone billing/shipping addresses
		if (!$this->isFinalized)
		{
			foreach (array('billingAddress', 'shippingAddress') as $address)
			{
				if ($this->$address)
				{
					$this->$address->load();
					$this->$address->getSpecification();
					$cloned = clone $this->$address;
					$cloned->save();
					$cloned->loadEav();
					$this->$address = $cloned;
				}
			}
		}

		// move wish list items to a separate order
		if ($this->getWishListItems())
		{
			$wishList = CustomerOrder::getNewInstance($this->user);
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

		$this->totalAmount = $this->getTotal(true);

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

		$this->setPaidStatus();

		$this->dateCompleted = new ARSerializableDateTime();

		$this->isFinalized = true;

		// @todo: fix order total calculation
		$shipments = $this->shipments;
		unset($this->shipments);

		if (!$this->invoiceNumber)
		{
			$generator = InvoiceNumberGenerator::getGenerator($this);
			$saved = false;
			while (!$saved)
			{
				try
				{
					$this->invoiceNumber = $generator->getNumber();
					$this->save();
					$saved = true;
				}
				catch (SQLException $e)
				{
				}
			}
		}

		if ($this->isRecurring)
		{
			$changed = false;
			if (!strlen($this->startDate))
			{
				$this->startDate = date('Y-m-d H:i:s', time());
				$changed = true;
			}

			if ($rebillsLeft != $this->rebillsLeft)
			{
				$this->rebillsLeft = $rebillsLeft;
				$changed = true;
			}

			if ($changed)
			{
				$this->save();
			}
		}

		$this->event('after-finalize');

		$this->getDI()->get('db')->commit();

		// @todo: see above
		$this->shipments = $shipments;

		return $wishList;
	}

	public function cancel()
	{
		if ($this->isCancelled)
		{
			return;
		}

		$this->getDI()->get('db')->begin();

		$this->event('before-cancel');

		$this->isCancelled = true;

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

		$this->getDI()->get('db')->commit();
	}

	public function restore()
	{
		if (!$this->isCancelled)
		{
			return;
		}

		$this->isCancelled = false;

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
		$this->capturedAmount = $this->capturedAmount + $amount;
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

		if ($this->isMultiAddress)
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
				$product = $item->getProduct();
				// do not merge items that are same product, but different options
				$choiceHash = array();
				foreach ($item->getOptions() as $choice)
				{
					$choiceHash[] = md5($choice->choice->getID() . '_' . $choice->optionText);
				}

				// do not merge items that has same product, but different recurring plans
				if ($product->type == Product::TYPE_RECURRING)
				{
					$recrurringItem = RecurringItem::getInstanceByOrderedItem($item, true);
					$choiceHash[] = $recrurringItem->recurringID->getID().'|';
				}
				$hash = $choiceHash ? '_' . md5(implode('', $choiceHash)) : '';
				$byProduct[$product->getID() . $hash][(int)$item->isSavedForLater][] = $item;
			}

			foreach ($byProduct as $productID => $itemsByStatus)
			{

				foreach ($itemsByStatus as $status => $items)
				{
					if (count($items) > 1)
					{

						$mainItem = array_shift($items);
						$count = $mainItem->count;
						foreach ($items as $item)
						{
							$count += $item->count;
							$this->removeItem($item);
						}
						$mainItem->count = $count;
					}
				}
			}
		}
	}

	public function setUser(User $user)
	{
		if ($this->user && ($this->user->getID() == $user->getID()))
		{
			return;
		}

		$this->user = $user;
		$this->setCheckoutStep(self::CHECKOUT_USER);

		foreach (array(array('defaultBillingAddress' => 'billingAddress'),
					   array('defaultShippingAddress' => 'shippingAddress'),
					   array('defaultBillingAddress' => 'shippingAddress'),
					   ) as $pair)
		{
			$userAd = array_shift(array_keys($pair));
			$orderAd = reset($pair);
			if ($user->$userAd && !$this->$orderAd)
			{
				$user->$userAd->load();
				$this->$orderAd = $user->$userAd->userAddress;
			}
		}

		$this->resetShipments();
		$this->getShipments();
	}

	public function setCheckoutStep($step)
	{
		if ($step <= $this->checkoutStep)
		{
			return false;
		}

		$this->checkoutStep = $step;
		$this->save();
	}

	public static function allowEmpty($allow = true)
	{
		self::$isEmptyAllowed = $allow;
	}

	/*####################  Saving ####################*/

	public function xbeforeSave()
	{
		return;
		if (!$this->orderedItems)
		{
			$this->loadItems();
		}

		// remove zero-count items
		foreach ($this->orderedItems as $item)
		{
			if (!$item->count)
			{
				$this->removeItem($item);
			}
		}

		$isModified = false;

		foreach ($this->orderedItems as $item)
		{
			/*
			if ($item->isDeleted())
			{
				$this->removeItem($item);
			}
			*/
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
				if (true || $item->isModified())
				{
					if (!$this->getID())
					{
						//parent::save();
					}

					/*
					if ($item->save())
					{
						$isModified = true;
					}
					*/
				}

				//$item->markAsLoaded();
			}
		}

		// If shipment is modified
		if ($this->isFinalized)
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

			$this->totalAmount = $this->getTotal(true);
		}
		else
		{
			if (!$this->isShippingRequired())
			{
				//$this->shippingAddress = null;
			}
		}

/*
		if ($this->isModified() || $isModified)
		{
			$this->serializeShipments();
		}
*/
		if (!$this->isFinalized && !$this->orderedItems && !$allowEmpty && !self::$isEmptyAllowed)
		{
			$this->delete();
			return false;
		}

		if ($this->user)
		{
			$this->user->invalidateSessionCache();
		}
	}

	public function serializeShipments()
	{
		$this->shipping = ($this->isFinalized || $this->isMultiAddress) ? '' : serialize($this->shipments);
	}

	public function setStatus($status)
	{
		$this->status = $status;
		$this->save();
		$this->updateShipmentStatuses();
	}

	public function updateShipmentStatuses()
	{
		$filter = new ARSelectFilter();
		$filter->setCondition('Shipment.orderID = :Shipment.orderID:', array('Shipment.orderID' => $this->getID()));

		if(!$this->isReturned())
		{
			$filter->andWhere(new NotEqualsCond('Shipment.status', self::STATUS_SHIPPED));
		}

		// get shipments for which the status will be changed
		$shipments = ActiveRecordModel::getRecordSet('Shipment', $filter, Shipment::LOAD_REFERENCES);
		foreach ($shipments as $key => $shipment)
		{
			if ($shipment->status != $this->status)
			{
				$shipment->status = $this->status;
				$shipment->save();
			}
		}

		return $shipments;
	}

	public function updateStatusFromShipments($creatingNewRecord = false)
	{
		$status = $this->calculateStatusFromShipmensts($creatingNewRecord);

		if($this->status != $status)
		{
			$this->status = $status;
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
				$lowestStatus = $shipment->status;
			}
			else if($lowestStatus != $shipment->status)
			{
				$lowestStatus = Shipment::STATUS_PROCESSING;
			}
		}

		if(!is_null($lowestStatus) && $lowestStatus != $this->status)
		{
			return $lowestStatus;
		}

		return $this->status;
	}

	public function getSubTotalByCurrency(Currency $currency)
	{
		if ($this->getCurrency()->getID() != $currency->getID())
		{
			$current = $this->getCurrency();
			$this->changeCurrency($currency, false);
			$subtotal = $this->getSubTotal(false);
			$this->changeCurrency($current, false);
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
			if (!$item->isSavedForLater)
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
		if (is_null($this->orderTotal) && $this->isFinalized)
		{
			$this->orderTotal = $this->totalAmount;
		}

		if (is_null($this->orderTotal) || $recalculateAmount)
		{
			if ($this->isFinalized && !$recalculateAmount)
			{
				$this->getTaxes();
				$total = $this->totalAmount;
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
			$amount += $discount->amount;
		}

		foreach ($this->orderDiscounts as $discount)
		{
			$amount += $discount->amount;
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

		if ($this->shipments && $this->shipments->count())
		{
			// @todo: the tax calculation is slightly off when it's calculated for the first time, so it has to be called twice
			$this->getTaxes();
			foreach ($this->shipments as $shipment)
			{
				$shipment->order = $this;
				$total += $shipment->getTotal($recalculateAmounts);
				//echo '['.$shipment->getID() , ' ('.$shipment->getTotal($recalculateAmounts).')] ';
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
		return array();
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
			if ($coupon->couponCode == $code)
			{
				return true;
			}
		}
	}

	private function getTaxes()
	{
		$this->taxes = $this->taxDetails = array();

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
					$taxId = ($tax->taxRate && $tax->taxRate->tax) ? $tax->taxRate->tax->getID() : 0;
					if (!isset($this->taxes[$taxId]))
					{
						$this->taxes[$taxId] = 0;
					}

					$this->taxes[$taxId] += $tax->getAmount();

					// in case the tax has different rates (tax classes)
					$rateId = $tax->taxRate->getID();
					if (!isset($this->taxDetails[$rateId]))
					{
						$this->taxDetails[$rateId] = array('amount' => 0, 'rate' => $tax->taxRate->toArray());
					}

					$this->taxDetails[$rateId]['amount'] += $tax->getAmount();
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
		return $this->status == self::STATUS_PROCESSING;
	}

	public function isAwaitingShipment()
	{
		return $this->status == self::STATUS_AWAITING;
	}

	public function isShipped()
	{
		return $this->status == self::STATUS_SHIPPED;
	}

	public function isReturned()
	{
		return $this->status == self::STATUS_RETURNED;
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
		$selected = $this->shipments ? $this->shipments->count() : 0;

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


		$app = $this->getDI()->get('application');
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
		$total = $this->getSubTotalByCurrency($this->getDI()->get('application')->getDefaultCurrency());

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
			if (!$product || (!$product->isEnabled || !$product->getParent()->isEnabled))
			{
				$item->delete();
				$this->removeItem($item);
				$result['delete'][] = $item->toArray();
			}
		}

		if (!$this->getDI()->get('application')->isInventoryTracking())
		{
			return $result;
		}

		foreach ($this->getOrderedItems() as $item)
		{
			$product = $item->getProduct();

			// previously out-of-stock item now back in stock
			if ((OrderedItem::OUT_OF_STOCK == $item->isSavedForLater) && $product->isAvailable())
			{
				$item->isSavedForLater = OrderedItem::CART;
				$result['in'][] = array('id' => $item->getID());
			}

			if (!$product->isBackOrderable && !$item->isSavedForLater && !$product->isBundle())
			{
				if ($product->isInventoryTracked())
				{
					if (($product->stockCount <= 0))
					{
						$item->isSavedForLater = OrderedItem::OUT_OF_STOCK;
						$result['out'][] = array('id' => $item->getID());
					}
					else if ($product->stockCount < $item->count)
					{
						$count = $item->count;
						$item->count = $product->stockCount;
						$result['count'][] = array('id' => $item->getID(), 'from' => $count, 'to' => $item->count);
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

	public function changeCurrency(Currency $currency, $save = true)
	{
		$this->currency = $currency;
		foreach ($this->getOrderedItems() as $item)
		{
			$item->price = $item->getProduct()->getItemPrice($item, true, $currency);
			$item->setItemPrice($item->price);

			if ($save)
			{
				$item->save();
			}
		}

		if ($save)
		{
			$this->save();
		}

		// otherwise old currency price "sticks" for this request
		ActiveRecord::clearArrayData();
	}

	public function getPaidAmount()
	{
		$transactions = $this->getTransactions($this->getPaidTransactionFilter());
		$paid = 0;
		foreach ($transactions as $transaction)
		{
			$paid += $transaction->amount;
		}

		return $paid;
	}

	private function getPaidTransactionFilter()
	{
		$filter = new ARSelectFilter(new InCond('Transaction.type', array(Transaction::TYPE_AUTH, Transaction::TYPE_SALE)));
		$filter->andWhere(new NotEqualsCond('Transaction.isVoided', true));
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
		if ($this->isFinalized && is_null($this->paymentMethod))
		{
			foreach ($this->getTransactions($this->getPaidTransactionFilter()) as $transaction)
			{
				if ($transaction->method)
				{
					$this->paymentMethod = $transaction->method;
					break;
				}

				// offline methods
				else if ($transaction->serializedData)
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
		if(!$this->currency)
		{
			$this->currency = $this->getDI()->get('application')->getDefaultCurrency();
		}

		return $this->currency;
	}

	public function getCompletionDate()
	{
		return $this->dateCompleted;
	}

	/*####################  Data array transformation ####################*/

	/**
	 *  Creates an array representation of the shopping cart
	 */
	public function toArray()
	{
		$array = parent::toArray();
		
		foreach ($this->orderedItems as $item)
		{
			$array['OrderedItems'][] = $item->toArray();
		}
		
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

		$array['taxAmount'] = $taxAmount[$id];

		foreach ($this->taxDetails as &$taxRate)
		{
			$taxRate['formattedAmount'] = $currency->getFormattedPrice($taxRate['amount']);
		}
		$array['taxDetails'] = $this->taxDetails;

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
			$array['itemDisplayPriceTotal'] += $item->getDisplayPrice($currency) * $item->count;
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

		// discounts
		$array['discountAmount'] = 0;
		foreach (array_merge($this->fixedDiscounts, $this->orderDiscounts) as $key => $discount)
		{
			$array['discounts'][$discount->getID() ? $discount->getID() : $key] = $discount->toArray();
			$array['discountAmount'] -= $discount->amount;
		}

		// percentage discount applied to finalized order
		if (!$array['discountAmount'] && $array['isFinalized'] && ($array['subtotalBeforeTaxes'] > $array['totalAmount']))
		{
			$array['discountAmount'] = $array['totalAmount'] - $array['subtotalBeforeTaxes'] - $array['taxAmount'];
		}

		$array['formatted_discountAmount'] = $this->getCurrency()->getFormattedPrice($array['discountAmount']);

		// coupons
		if (!is_null($this->coupons))
		{
			$array['coupons'] = $this->coupons->toArray();
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
			$array['isAddressSelected'] = ($this->shippingAddress && $this->billingAddress);
		}

		// otherwise left empty on payment page for some reason...
		if ($this->billingAddress)
		{
			$array['BillingAddress'] = $this->billingAddress->toArray();
		}

		$array['isLocalPickup'] = $this->isLocalPickup();
		
		if (!empty($this->paymentMethod))
		{
			$array['paymentMethod'] = $this->paymentMethod;
		}
		
		if (!empty($array['paymentMethod']))
		{
			$array['paymentMethodName'] = (substr($array['paymentMethod'], 0, 7) == 'OFFLINE') ? OfflineTransactionHandler::getMethodName($array['paymentMethod']) : $this->getDI()->get('application')->translate($array['paymentMethod']);
		}

		return $array;
	}

	/*####################  Get related objects ####################*/

	public function getShoppingCartItems()
	{
		$items = array();

		foreach ($this->orderedItems as $item)
		{
			if (!$item->isSavedForLater)
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
			if ($item->isSavedForLater)
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
			$count += $item->count;
		}

		return $count;
	}

	public function getWishListItemCount()
	{
		return count($this->getWishListItems());
	}

	public function getItemsByProduct(\product\Product $product)
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
				$item->product = $products[$id];
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
		if (!$this->shipments || !$this->shipments->count())
		{
			if ($this->getID() && ($this->isFinalized || $this->isMultiAddress))
			{
				$this->loadItems();

				$filter = query::query()->where('Shipment.orderID = :Shipment.orderID:', array('Shipment.orderID' => $this->getID()));
				$filter->orderBy('Shipment.status');

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
				if (!$this->shipments || !$this->shipments->count())
				{
					$this->shipments = array();

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
						else if ($item->getProduct()->isSeparateShipment)
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

					if (isset($downloadable))
					{
						$this->shipments->unshift($downloadable);
					}
				}

				$this->event('getShipments');

				$this->shipping = serialize($this->shipments);
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
		if (empty($this->businessRuleContext))
		{
			$context = new \businessrule\BusinessRuleContext($this->getDI());
			$context->setOrder($this);
			if ($this->user)
			{
				$context->setUser($this->user);
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

		$controller = new \businessrule\BusinessRuleController($this->getBusinessRuleContext());
		return $controller->getActions();
	}

	public function processBusinessRules($reload = false)
	{
		if ($this->isFinalized)
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
				$ruleAction->applyToorderBy($this);
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

		foreach (ActiveRecordModel::getRecordSet('ProductCategory', new ARSelectFilter(new INCond('ProductCategory.productID', array_keys($set))), array('Category')) as $additional)
		{
			foreach ($set[$additional->product->getID()] as $item)
			{
				$item->registerAdditionalCategory($additional->category);
			}
		}
	}

	public function getTaxZone($forceReset = false)
	{
		if (!$this->taxZone || $forceReset)
		{
			if ($this->isShippingRequired() && $this->shippingAddress)
			{
				$this->taxZone = DeliveryZone::getZoneByAddress($this->shippingAddress, DeliveryZone::TAX_RATES);
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
				if (!$this->deliveryZone || $forceReset)
		{
			if ($this->isShippingRequired() && $this->shippingAddress)
			{
				$this->deliveryZone = DeliveryZone::getZoneByAddress($this->shippingAddress, DeliveryZone::SHIPPING_RATES);
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

		foreach ($this->shipments as $shipment)
		{
			$shipment->setTaxZone($zone);
		}
	}

	/**
	 *  Return all transactions that are related to this order
	 */
	public function getTransactions(ARSelectFilter $filter = null)
	{
				if (is_null($filter))
		{
			$filter = new ARSelectFilter();
		}
		$filter->orderBy('Transaction.ID', 'ASC');
		return $this->getRelatedRecordSet('Transaction', $filter);
	}

	public function getNotes()
	{
		$f = new ARSelectFilter();
		$f->orderBy('OrderNote.ID', 'DESC');
		return $this->getRelatedRecordSet('OrderNote', $f, OrderNote::LOAD_REFERENCES);
	}

	public function resetShipments()
	{
		if (!$this->isFinalized && !$this->isMultiAddress)
		{
			$this->shipments = array();
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

	public function loadRequestData(\Phalcon\Http\Request $request)
	{
		//$this->getSpecification()->loadRequestData($request);
		$order = $request->getJson('order');
		$items = $order['OrderedItems'];
		foreach ($this->orderedItems as $item)
		{
			$found = false;
			foreach ($items as $key => $reqItem)
			{
				if ($reqItem['ID'] == $item->getID())
				{
					$found = true;
					$item->setCount($reqItem['count']);
					$item->save();
					unset($items[$key]);
					break;
				}
			}
			
			if (!$found)
			{
				$this->removeItem($item);
			}
		}
		
		// add new items
		foreach ($items as $reqItem)
		{
			
		}
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

	public function setPaidStatus()
	{
		// @todo: remove the 0.99 multiplicator for currency conversion "tolerance" (a cent going missing when converting amounts between currencies back and forth)
		if ((round($this->totalAmount, 2)  * 0.99) <= round($this->getPaidAmount(), 2))
		{
			$this->isPaid = true;
		}
	}

/*
	public function __clone()
	{
		parent::__clone();

		$this->isFinalized = false;
		$this->isPaid = false;
		$this->isCancelled = false;
		$this->dateCompleted = null;
		$this->invoiceNumber = null;

		$original = $this->originalRecord;

		$this->shipments = new ARSet();
		$this->orderedItems = array();

		if ($original->isFinalized)
        {
                foreach ($original->getShipments() as $shipment)
                {
                        $cloned = clone $shipment;
                        $cloned->order = $this;

                        if ($this->isMultiAddress)
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

		if ($this->isMultiAddress)
		{
			$this->save(true);

			foreach ($this->getShipments() as $shipment)
			{
				if ($shipment->shippingAddress)
				{
					$shipment->shippingAddress = $this->getClonedAddress($shipment->shippingAddress, false);
				}

				$shipment->save();

				foreach ($shipment->getItems() as $item)
				{
					$item->shipment = $shipment;
					$item->save();
				}
			}
		}

		// addresses
		if ($this->billingAddress)
		{
			$this->billingAddress = $this->getClonedAddress($this->billingAddress, true);
		}

		if ($this->shippingAddress)
		{
			$this->shippingAddress = $this->getClonedAddress($this->shippingAddress, false);
		}
		$this->save();
	}
*?
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

		$user = $this->user;
		$addresses = $isBilling ? $user->getBillingAddressSet() : $user->getShippingAddressSet();

		foreach ($addresses as $address)
		{
			$address->reload();
			$address->userAddress->reload();

			if ($address->userAddress->toString(", ") == $addressString)
			{
				return $address->userAddress;
			}
		}

		if ($addresses->count())
		{
			return $addresses->shift()->userAddress;
		}

		return null;
	}

/*
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
*/
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

	public static function hasRecurringorderBy()
	{

		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'isRecurring'), 1));
		return (bool)ActiveRecordModel::getRecordCount(__CLASS__, $filter);
	}

	public static function findOrdersWithRecurringPeriodEndingToday($today=null)
	{
		return self::getRecurringOrders(
			self::getRecurringPeriodsEndingTodayArray($today)
		);
	}

	public function getNextRebillDate($today = null)
	{
		$ts = null;
		if($today == null)
		{
			$today = 'CURRENT_DATE';
		}
		else if(is_numeric($today))
		{
			$ts = $today;
		}
		else
		{
			$ts = strtotime($today);
		}
		if ($ts)
		{
			$today = '0x'.bin2hex(date('Y-m-d', $ts));
		}

		$data = ActiveRecordModel::getDataBySql('
			SELECT
				ri.*,
				COALESCE(lastInvoiceOrder.ID, co.ID) as CustomerOrderID,
				COALESCE(lastInvoiceOrder.startDate, co.startDate) as startDate,
				IF(
					lastInvoiceOrder.ID IS NULL,'.
					/* main order does not have endDate, because with different recurring periods it has more than one ending date. calculate end date from period start + recurringlan period length - 1 day (end date is 'including') */
					self::sqlForEndDate() .',
					lastInvoiceOrder.endDate
				) as endDate,
				IF(
					lastInvoiceOrder.ID IS NOT NULL,'.
					self::sqlForAddedDate('lastInvoiceOrder.startDate').' ,'.
					self::sqlForAddedDate('co.startDate').'
				) AS addedDate,
				ri.ID as recurringItemID,
				ri.periodType,
				ri.periodLength,
				ri.lastInvoiceID,
				lastInvoiceOrder.parentID
			FROM
				RecurringItem ri
				INNER JOIN OrderedItem oi ON ri.orderedItemId = oi.ID
				INNER JOIN CustomerOrder co ON oi.customerOrderID = co.ID
				INNER JOIN RecurringProductPeriod rpp ON ri.recurringID = rpp.ID
				LEFT JOIN CustomerOrder lastInvoiceOrder ON lastInvoiceOrder.ID = ri.lastInvoiceID
			WHERE
				(
					co.ID = '.$this->getID().'
						OR
					co.parentID = '.$this->getID().'
				)
				AND
				(
					ri.rebillCount IS NULL
						OR
					ri.rebillCount > IF(ri.processedRebillCount IS NULL , 0, ri.processedRebillCount)
				)
		');

		$date = null;
		$locale = $this->getDI()->get('application')->getLocale();
		if (count($data) > 0)
		{
			$row = $data[0]; // if more??
			$date = $locale->getFormattedTime(strtotime(date('Y-m-d',strtotime('+1 day', strtotime($row['endDate'])))));
		}
		return $date;
	}

	public static function getRecurringPeriodsEndingTodayArray($today=null)
	{
		$ts = null;
		if($today == null)
		{
			$today = 'CURRENT_DATE';
		}
		else if(is_numeric($today))
		{
			$ts = $today;
		}
		else
		{
			$ts = strtotime($today);
		}
		if ($ts)
		{
			$today = '0x'.bin2hex(date('Y-m-d', $ts));
		}

		$data = ActiveRecordModel::getDataBySql(
		'
			SELECT
				COALESCE(lastInvoiceOrder.ID, co.ID) as CustomerOrderID,
				COALESCE(lastInvoiceOrder.startDate, co.startDate) as startDate,
				IF(
					lastInvoiceOrder.ID IS NULL,'.
					/* main order does not have endDate, because with different recurring periods it has more than one ending date. calculate end date from period start + recurringlan period length - 1 day (end date is 'including') */
					self::sqlForEndDate() .',
					lastInvoiceOrder.endDate
				) as endDate,
				IF(
					lastInvoiceOrder.ID IS NOT NULL,'.
					self::sqlForAddedDate('lastInvoiceOrder.startDate').' ,'.
					self::sqlForAddedDate('co.startDate').'
				) AS addedDate,
				ri.ID as recurringItemID,
				ri.periodType,
				ri.periodLength,
				ri.lastInvoiceID,
				lastInvoiceOrder.parentID
			FROM
				RecurringItem ri
				INNER JOIN OrderedItem oi ON ri.orderedItemId = oi.ID
				INNER JOIN CustomerOrder co ON oi.customerOrderID = co.ID
				INNER JOIN RecurringProductPeriod rpp ON ri.recurringID = rpp.ID
				LEFT JOIN CustomerOrder lastInvoiceOrder ON lastInvoiceOrder.ID = ri.lastInvoiceID
			WHERE
				(
					ri.rebillCount IS NULL
						OR
					ri.rebillCount > IF(ri.processedRebillCount IS NULL , 0, ri.processedRebillCount)
				)
			/* GROUP BY co.ID, ri.periodType, ri.periodLength */
			HAVING
			(
				TO_DAYS(addedDate) = TO_DAYS('.$today.')
			)
		');

		$customerOrderIDs = array();
		foreach($data as $row)
		{
			//if (date('Y-m-d', $ts) == '2010-01-13')
			// print_r($row);
			$customerOrderIDs[$row['CustomerOrderID']][] = $row['recurringItemID'];
		}
		return $customerOrderIDs;
	}

	public static function getRecurringOrders($IDs)
	{
		$customerOrderIDs = array();
		foreach($IDs as $orderID=>$recurringIDs)
		{
			$customerOrderIDs[] = $orderID;
		}
		$filter = new ARSelectFilter();
		$filter->andWhere(new InCond(new ARFieldHandle(__CLASS__, 'ID'), $customerOrderIDs));
		return ActiveRecordModel::getRecordSet('CustomerOrder', $filter);
	}

	public static function generateRecurringInvoices($forDate = null)
	{
		$generatedInvoiceIDs = array(); // return only ids for generated invoice, because 1000+ CustomerOrder instances are expensive
		if($forDate == null)
		{
			$ts = time();
		}
		else if(is_numeric($forDate))
		{
			$ts = $forDate;
		}
		else
		{
			$ts = strtotime($forDate);
		}
		$count = 0;
		$orderIDrecurringItemIDMapping = self::getRecurringPeriodsEndingTodayArray($ts);
		$orders = self::getRecurringOrders($orderIDrecurringItemIDMapping);
		if ($orders->count() == 0)
		{
			return $generatedInvoiceIDs;
		}

		/*
		echo "date: ", date('Y-m-d', $ts), "\n";
		print_r($orderIDrecurringItemIDMapping);
		echo "\n";
		*/

		$config = $this->getDI()->get('application')->getConfig();
		$daysDue = $config->get('RECURRING_BILLING_PAYMENT_DUE_DATE_DAYS');
		$daysBefore = $config->get('RECURRING_BILLING_GENERATE_INVOICE');

		foreach($orders as $order)
		{
			$mainOrder = $order;
			$foundOrderID = $order->getID(); // order found with sql, this id is used in customerOrder+recurringItem mapping as key.
			$parent = $order->parentID;
			if ($parent)
			{
				$mainOrder = $parent;
			}
			else
			{
				// echo 'generating from first invoice (could contain multiple plans merged!)';
			}

			$mainOrderID = $mainOrder->getID();
			$mainOrder->isRecurring = true;
			$mainOrder->save();

			// group by recurring period type, length, (rebill count?)
			$groupedRecurringItems = array();
			foreach ($mainOrder->getOrderedItems() as $item)
			{
				$recurringItem = RecurringItem::getInstanceByOrderedItem($item);
				$recurringItemID = $recurringItem->getID();
				if (isset($orderIDrecurringItemIDMapping[$foundOrderID]) &&
					in_array($recurringItemID, $orderIDrecurringItemIDMapping[$foundOrderID])
				) {
					$rpp = $recurringItem->recurringID;
					$groupedRecurringItems[
						sprintf('%s_%s_%s',$rpp->periodType, $rpp->periodLength, $rpp->rebillCount === null ? 'NULL' : $rpp->rebillCount )
					][] = array('item'=>$item, 'recurringItem'=>$recurringItem);
				}
			}

			foreach ($groupedRecurringItems as $itemGroups)
			{

				$recurringItemIDs = array();

				$newOrder = clone $mainOrder;
				$newOrder->parentID = $mainOrder;
				$newOrder->isFinalized = false;
				$newOrder->invoiceNumber = $newOrder->getCalculatedRecurringInvoiceNumber();
				$newOrder->dateDue = date('Y-m-d H:i:s', strtotime('+'. ($daysBefore + $daysDue) .' day', $ts));

				$newOrder->isRecurring = true;
				$newOrder->save(true); // order must be saved for setting recurringItem lastInvoiceID.

				// !! don't save order while it dont have any item or order will be deleted.
				foreach($newOrder->getOrderedItems() as $itemToRemove)
				{
					$newOrder->removeItem($itemToRemove);
				}
				foreach($newOrder->getShipments() as $shipment)
				{
					//echo '{Shipment ID:'.$shipment->getID().'}';
					$newOrder->removeShipment($shipment);
				}

				foreach ($itemGroups as $itemGroup)
				{
					$item = $itemGroup['item'];
					$recurringItem = $itemGroup['recurringItem'];
					$newOrderShipment = Shipment::getNewInstance($newOrder); // ~ should have multiple shipments?
					$recurringItem->saveLastInvoice($newOrder);
					$periodLength = $recurringItem->periodLength;
					$periodType = $recurringItem->periodType;
					$recurringItemIDs[] = $recurringItem->getID(); // collect IDs for batch processedRebillCount update.
					$clone = clone $item;
					$clone->recurringParentID = $item;
					$clone->shipmentID = null;
					$clone->save();
					$newOrderShipment->addItem($clone);
					$newOrder->addShipment($newOrderShipment);
				}

				if (count($itemGroups) > 0 )
				{
					$newOrder->updateStartAndEndDates($order,
						array_map(array(__CLASS__, '_filterRecurringItems'), $itemGroups)
					);
				}

				if ($newOrder->isExistingRecord())
				{
					$generatedInvoiceIDs[] = $newOrder->getID();
				}

				if (count($recurringItemIDs))
				{
					// nedd to be done before CustomerOrder::finalize() because finalize() also updates CustomerOrder.rebillsLeft field.
					// therefore can't really do batch for all, need to do for every order. still, can reuse batch method.
					RecurringItem::batchIncreaseProcessedRebillCount($recurringItemIDs);
				}

				$newOrder->save();
				$newOrder->finalize();
			}
		}


		return $generatedInvoiceIDs;
	}

	public function updateStartAndEndDates(CustomerOrder $previousOrder, $recurringItems)
	{
		// calling this more than once for same record will end with catastrophe.
		// echo "\n--------------------------\n";
		foreach ($recurringItems as $recurringItem)
		{

			$recurringItemID = $recurringItem->getID(); // anyone should work, because $recurringItems should be grouped by type and length. ID is required for getting period length in calendar days (done with sql).

			break;
			// echo $recurringItem->getID(), "\n";
		}

		// find end date (first order does not have endDate)
		//    add one day = start date,
		// find 2 end date periods
		//    new end date.
		$data = ActiveRecordModel::getDataBySql(
		//echo (
		'SELECT
			TIMESTAMP(DATE( /*remove hh:mm:ss */
				ADDDATE(
					IF(TO_DAYS(co.endDate) IS NULL,'.
						self::sqlForEndDate('co.startDate') .',
						co.endDate
					),
				INTERVAL 1 DAY)
			)) AS startDate,
			'.self::sqlForEndDate('co.startDate', 2).' as endDate
			FROM
				CustomerOrder co
				INNER JOIN RecurringItem ri ON ri.ID='.$recurringItemID.'
			WHERE
				co.ID = '.$previousOrder->getID()
		);

		//

		if ($data && count($data) == 1)
		{
			$data = array_shift($data);
			$this->startDate = $data['startDate'];
			$this->endDate = $data['endDate'];

			return true;
		}
		return false;
	}

	public function _filterRecurringItems($item) // used as array_map callback
	{
		return $item['recurringItem'];
	}

	public function getCalculatedRecurringInvoiceNumber()
	{
		$parent = $this->parentID;
		if (!$parent)
		{
			return $this->invoiceNumber;
		}
		$filter = new ARSelectFilter();
		$filter->andWhere(new EqualsCond(new ARFieldHandle(__CLASS__,'parentID'), $parent->getID()));
		$filter->orderBy(new ARFieldHandle(__CLASS__, 'dateCreated'), 'ASC');
		$filter->orderBy(new ARFieldHandle(__CLASS__, 'ID'), 'ASC');

		$rs = ActiveRecordModel::getRecordSet(__CLASS__, $filter);
		$count = 1;
		foreach ($rs as $invoiceOrder)
		{
			$invoiceOrderID = $invoiceOrder->getID();
			if ($invoiceOrderID == $this->getID())
			{
				return $parent->invoiceNumber.'-'.$count;
			}
			$count++;
		}
		return $parent->invoiceNumber.'-'.$count; // for last unsaved order
	}

	private static function sqlForAddedDate($field)
	{
		$generateInvoiceDays = (int)$this->getDI()->get('application')->getConfig()->get('RECURRING_BILLING_GENERATE_INVOICE');
		if (!is_numeric($generateInvoiceDays) || $generateInvoiceDays < 0)
		{
			$generateInvoiceDays = 3;
		}
		$mapping = self::recurringProductPeriodToSQLConstant();

		$chunks = array('CASE');
		foreach ($mapping as $value => $intervalConst)
		{
			$chunks[] = 'WHEN ri.periodType = '.$value .' THEN
				SUBDATE(ADDDATE('.$field.', INTERVAL ri.periodLength '.$intervalConst.'), INTERVAL '.$generateInvoiceDays.' DAY)';
		}
		$chunks[] = 'END';

		return implode("\n", $chunks);
	}

	private function sqlForEndDate($field='co.startDate', $periodCount=1)
	{
		$mapping = self::recurringProductPeriodToSQLConstant();

		$chunks = array('CASE');
		foreach ($mapping as $value => $intervalConst)
		{
			$chunks[] = 'WHEN ri.periodType = '.$value .' THEN
				ADDTIME(TIMESTAMP(DATE(
					SUBDATE(ADDDATE('.$field.', INTERVAL ri.periodLength * '.$periodCount.' '.$intervalConst.'), INTERVAL 1 DAY)
				)), \'23:59:59\')';
		}
		$chunks[] = 'END';
		return implode("\n", $chunks);
	}


	private function recurringProductPeriodToSQLConstant()
	{
		return array(
			RecurringProductPeriod::TYPE_PERIOD_DAY => 'DAY',
			RecurringProductPeriod::TYPE_PERIOD_WEEK => 'WEEK',
			RecurringProductPeriod::TYPE_PERIOD_MONTH => 'MONTH',
			RecurringProductPeriod::TYPE_PERIOD_YEAR => 'YEAR'
		);
	}

	public function cancelFurtherRebills()
	{

		return ;
		$id = $this->getID();
		$userID = $this->userID->getID();
		$update = new ARUpdateFilter();
		$update->setCondition(
			new OrChainCondition(array(
				new AndChainCondition(array(
					'CustomerOrder.ID = :CustomerOrder.ID:', array('CustomerOrder.ID' => $id),
					'CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $userID)
				)),
				new AndChainCondition(array(
					'CustomerOrder.parentID = :CustomerOrder.parentID:', array('CustomerOrder.parentID' => $id),
					'CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $userID)
				))
			))
		);
		$update->addModifier('rebillsLeft', '0');
		ActiveRecord::updateRecordSet('CustomerOrder', $update);
	}

	public function cancelRecurring($currencyID = 'USD')
	{
		// ~
		// getTransaction()
		$this->loadAll();
		$transaction = new LiveCartTransaction($this, Currency::getValidInstanceById($currencyID));
		// ~
		$expressInstance = ExpressCheckout::getInstanceByorderBy($this);
		$handler = $expressInstance->getHandler($transaction);
		$status = $handler->cancelRecurring();

		return $status;
	}

	private $canUserCancelRebillsResult = null;

	private $getSubscriptionStatusResult = null;

	public function canUserCancelRebills($forceRecheck = false)
	{
		if ($forceRecheck || $this->canUserCancelRebillsResult === null)
		{
			$this->canUserCancelRebillsResult = $this->canUserCancelRebillsImpl($forceRecheck);
		}
		return $this->canUserCancelRebillsResult;
	}

	private function canUserCancelRebillsImpl($forceRecheck)
	{
		if (false == $this->getDI()->get('application')->getConfig()->get('ALLOW_USER_TO_CANCEL_RECURRING_REBILLS', false))
		{
			return false; // forbidden in configuration.
		}
		return (boolean) $this->getSubscriptionStatus();
	}

	public function getSubscriptionStatus($forceRecheck = false) // 0 - inactive, 1 - active, 2 - ..
	{
		if ($forceRecheck || $this->getSubscriptionStatusResult === null)
		{
			$this->getSubscriptionStatusResult = $this->getSubscriptionStatusImpl($forceRecheck);
		}
		return $this->getSubscriptionStatusResult;
	}

	const INACTIVE_SUBSCRIPTION = 0;
	const ACTIVE_SUBSCRIPTION = 1;

	private function getSubscriptionStatusImpl($forceRecheck)
	{
		if ($this->isRecurring == false)
		{
			return false; // not even a recurring order.
		}

		$rebillsLeft = $this->rebillsLeft;
		if ($rebillsLeft == -1 || $rebillsLeft > 0) // rebill forever or at least one more rebill
		{
			return self::ACTIVE_SUBSCRIPTION;
		}

		// if order is invoice
		$filter = new ARSelectFilter();
		$filter->setCondition(
			new EqualsCond(
				new ARFieldHandle(__CLASS__, 'parentID'), $this->getID()
			)
		);
		$filter->addField('(SELECT SUM(IF(CustomerOrder.rebillsLeft >= 0, CustomerOrder.rebillsLeft, 0 )))','','rebillsLeft');
		$filter->addField('(SELECT SUM(IF(CustomerOrder.rebillsLeft = -1, 1, 0 )))','','isInfinite');
		$filter->setGrouping(new ARFieldHandle(__CLASS__, 'parentID'));
		$data = ActiveRecordModel::getRecordSetArray(__CLASS__, $filter);

		if (count($data) == 1 && ($data[0]['isInfinite'] > 0 || $data[0]['rebillsLeft'] > 0))
		{
			return self::ACTIVE_SUBSCRIPTION;
		}

		return false;
	}

    public function isLocalPickup()
    {
        $this->loadAll();
        $shipments = $this->getShipments();
        // what if no shipments? false? (default value for ShippingService.isLocalPickup)
        if (0 == count($shipments))
        {
            return false;
        }
        foreach ($shipments as $shipment)
        {
            // $shipment->getShippingService(); does not work when customer is repeating order ($shipment is not saved yet)
            // but with toArray() works for unsaved.
            $shipmentArray = $shipment->toArray();
            if (!isset($shipmentArray, $shipmentArray['ShippingService'], $shipmentArray['ShippingService']['isLocalPickup'])
                    || $shipmentArray['ShippingService']['isLocalPickup'] == false)
            {
                return false;
            }
        }
        return true;
    }
}

?>
