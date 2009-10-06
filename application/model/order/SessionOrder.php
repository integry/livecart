<?php

ClassLoader::import('application.model.user.SessionUser');
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.user.UserAddress');
ClassLoader::import('application.model.businessrule.RuleProductContainer');

/**
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class SessionOrder
{
	/**
	 * Get CustomerOrder instance from session
	 *
	 * @return CustomerOrder
	 */
	public static function getOrder()
	{
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
			$userId = SessionUser::getUser()->getID();

			// get the last unfinalized order by this user
			if ($userId > 0)
			{
				$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $userId));
				$f->mergeCondition(new NotEqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
				$f->setOrder(new ARFieldHandle('CustomerOrder', 'ID'), 'DESC');
				$f->setLimit(1);
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

		if (!$instance->user->get() && SessionUser::getUser()->getID() > 0)
		{
			$instance->setUser(SessionUser::getUser());
			$instance->save();
		}

		if ($instance->isFinalized->get())
		{
			$session->unsetValue('CustomerOrder');
			return self::getOrder();
		}

		self::setOrder($instance);

		return $instance;
	}

	public static function setOrder(CustomerOrder $order)
	{
		$session = new Session();
		$session->set('CustomerOrder', $order->getID());

		$currency = $order->getCurrency();
		$currID = $currency->getID();

		$orderArray = array('total' => array($currID => $order->getTotal()));
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

		$session->set('orderData', $orderArray);
	}

	public static function getOrderItems()
	{
		$session = new Session();
		$data = $session->get('orderData');
		if (isset($data['items']))
		{
			return $data['items'];
		}
	}

	public static function getOrderData()
	{
		self::setOrder(self::getOrder());
		$session = new Session();
		return $session->get('orderData');
	}

	public static function getEstimateAddress()
	{
		$session = new Session();

		if ($address = $session->get('shippingEstimateAddress'))
		{
			return $address;
		}
		else
		{
			$order = self::getOrder();
			if ($order->shippingAddress->get())
			{
				return $order->shippingAddress->get();
			}

			$user = $order->user->get();
			if ($user && !$user->isAnonymous())
			{
				$user->load(true);
				foreach (array('defaultShippingAddress', 'defaultBillingAddress') as $key)
				{
					if ($address = $user->$key->get())
					{
						$address->load(array('UserAddress'));
						$address->userAddress->get()->load();
						return $address->userAddress->get();
					}
				}
			}
		}

		$config = ActiveRecordModel::getApplication()->getConfig();
		$address = UserAddress::getNewInstance();
		$address->countryID->set($config->get('DEF_COUNTRY'));

		return $address;
	}

	public static function setEstimateAddress(UserAddress $address)
	{
		$order = self::getOrder();
		$session = new Session();
		$session->set('shippingEstimateAddress', $address);
	}

	public static function save(CustomerOrder $order)
	{
		// mark shipment data as modified - to force saving
		$order->getShipments();

		$order->save();
		self::setOrder($order);
	}

	public static function destroy()
	{
		$session = new Session();
		$session->unsetValue('CustomerOrder');
	}
}

?>