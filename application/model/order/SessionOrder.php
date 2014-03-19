<?php

namespace order;

/**
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class SessionOrder
{
	private static $instance = null;

	/**
	 * Get CustomerOrder instance from session
	 *
	 * @return CustomerOrder
	 */
	public static function getorderBy()
	{
		if (self::$instance)
		{
			return self::$instance;
		}

		$session = new Session();

		$id = $session->get('CustomerOrder');
		if ($id)
		{
			try
			{
				$instance = CustomerOrder::getInstanceById($id, true);

				if (!$instance->getOrderedItems())
				{
					$instance->loadItems();
				}

				$instance->isSyncedToSession = true;
			}
			catch (ARNotFoundException $e)
			{
				unset($instance);
			}
		}

		if (!isset($instance))
		{
			$userId = $this->sessionUser->getUser()->getID();

			// get the last unfinalized order by this user
			if ($userId > 0)
			{
				$f = query::query()->where('CustomerOrder.userID = :CustomerOrder.userID:', array('CustomerOrder.userID' => $userId));
				$f->andWhere(new NotEqualsCond('CustomerOrder.isFinalized', true));
				$f->orderBy('CustomerOrder.ID', 'DESC');
				$f->limit(1);
				$orders = ActiveRecordModel::getRecordSet('CustomerOrder', $f);
				if ($orders->size())
				{
					$instance = $orders->get(0);
				}
			}
		}

		if (!isset($instance))
		{
			$instance = CustomerOrder::getNewInstance(User::getNewInstance(0));
			$instance->user->set(NULL);
		}

		if (!$instance->user && $this->sessionUser->getUser()->getID() > 0)
		{
			$instance->setUser($this->sessionUser->getUser());
			$instance->save();
		}

		if ($instance->isFinalized)
		{
			$session->unsetValue('CustomerOrder');
			return self::getorderBy();
		}

		// fixes issue when trying to add OrderedItem to unsaved(without ID) CustomerOrder.
		// ~ but i don't know if returning unsaved CustomerOrder is expected behaviour.
		if ($instance->isExistingRecord() == false)
		{
			$instance->save(true);
		}
		self::orderBy($instance);
		return $instance;
	}

	public static function orderBy(CustomerOrder $order)
	{
		$session = new Session();
		$session->set('CustomerOrder', $order->getID());

		$currency = $order->getCurrency();
		$currID = $currency->getID();

		$total = $order->getTotal();
		$orderArray = array('total' => array($currID => $total));
		$orderArray['formattedTotal'] = array($currID => $currency->getFormattedPrice($orderArray['total'][$currID]));
		$orderArray['basketCount'] = $order->getShoppingCartItemCount();
		$orderArray['currencyID'] = $currID;

		$isOrderable = $order->isOrderable();
		$orderArray['isOrderable'] = is_bool($isOrderable) ? $isOrderable : false;

		$items = array();
		foreach ($order->getPurchasedItems() as $item)
		{
			$items[] = $item->toArray();
		}

		$orderArray['items'] = new RuleOrderContainer($items);
		$orderArray['items']->setCoupons($order->getCoupons());
		$orderArray['items']->setTotal($total);

		$session->set('orderData', $orderArray);

		self::$instance = $order;
	}

	public static function getOrderItems()
	{
		return array();
		
		$session = new Session();
		$data = $session->get('orderData');
		if (isset($data['items']))
		{
			return $data['items'];
		}
	}

	public static function getOrderData()
	{
		self::orderBy(self::getorderBy());
		$session = new Session();
		return $session->get('orderData');
	}

	public static function getEstimateAddress()
	{
		$session = new Session();

		if ($address = $session->get('shippingEstimateAddress'))
		{
			return unserialize($address);
		}
		else
		{
			$order = self::getorderBy();
			if ($order->shippingAddress)
			{
				return $order->shippingAddress;
			}

			$user = $order->user;
			if ($user && !$user->isAnonymous())
			{
				$user->load(true);
				foreach (array('defaultShippingAddress', 'defaultBillingAddress') as $key)
				{
					if ($address = $user->$key)
					{
						$address->load(array('UserAddress'));
						$address->userAddress->load();
						return $address->userAddress;
					}
				}
			}
		}

		return self::getDefaultEstimateAddress();
	}

	public static function getDefaultEstimateAddress()
	{
		$config = ActiveRecordModel::getApplication()->getConfig();
		$address = UserAddress::getNewInstance();
		$address->countryID->set($config->get('DEF_COUNTRY'));

		if ($state = $config->get('DEF_STATE'))
		{
			$address->state->set(State::getInstanceByID($config->get('DEF_STATE')));
		}

		return $address;
	}

	public static function setEstimateAddress(UserAddress $address)
	{
		$order = self::getorderBy();
		$estimateAddress = clone $address;
		$estimateAddress->removeSpecification();
		$session = new Session();
		$session->set('shippingEstimateAddress', $estimateAddress);
	}

	public static function save(CustomerOrder $order)
	{
		// mark shipment data as modified - to force saving
		$order->getShipments();

		$order->save();
		self::orderBy($order);
	}

	public static function destroy()
	{
		$session = new Session();
		$session->unsetValue('CustomerOrder');
		$session->unsetValue('orderData');
	}
}

?>
