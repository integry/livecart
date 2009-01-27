<?php

ClassLoader::import('application.model.user.SessionUser');
ClassLoader::import('application.model.order.CustomerOrder');

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

		$currency = $order->currency->get();
		$currID = $currency->getID();
		$orderArray = array('total' => array($currID => $order->getTotal($currency)));
		$orderArray['formattedTotal'] = array($currID => $currency->getFormattedPrice($orderArray['total'][$currID]));
		$orderArray['basketCount'] = $order->getShoppingCartItemCount();
		$orderArray['currencyID'] = $currID;
		$orderArray['isOrderable'] = $order->isOrderable();

		$session->set('orderData', $orderArray);
	}

	public static function getOrderData()
	{
		self::setOrder(self::getOrder());
		$session = new Session();
		return $session->get('orderData');
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