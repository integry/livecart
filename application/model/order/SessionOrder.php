<?php

namespace order;

use order\CustomerOrder;
use user\User;

/**
 *
 * @package application/model/order
 * @author Integry Systems <http://integry.com>
 */
class SessionOrder extends \Phalcon\DI\Injectable
{
	private $instance = null;
	
	public function __construct(\Phalcon\DI\FactoryDefault $di)
	{
		$this->setDI($di);
	}

	/**
	 * Get CustomerOrder instance from session
	 *
	 * @return CustomerOrder
	 */
	public function getOrder()
	{
		if ($this->instance)
		{
			return $this->instance;
		}

		$id = $this->session->get('CustomerOrder');
		if ($id)
		{
			$instance = CustomerOrder::getInstanceById($id);

			if ($instance)
			{
				if (!$instance->getOrderedItems())
				{
					$instance->loadItems();
				}

				$instance->isSyncedToSession = true;
			}
		}

		if (empty($instance))
		{
			$userId = $this->sessionUser->getUser()->getID();

			// get the last unfinalized order by this user
			if ($userId > 0)
			{
				$f = CustomerOrder::query()->where('userID = :userID:', array('userID' => $userId));
				$f->andWhere('isFinalized != 1');
				$f->orderBy('ID DESC');
				$f->limit(1);
				$orders = $f->execute();
				if ($orders->count())
				{
					$instance = $orders->shift();
				}
			}
		}

		if (empty($instance))
		{
			$instance = CustomerOrder::getNewInstance(User::getNewInstance(0));
			//$instance->setUser(null);
		}

		if (!$instance->user && $this->sessionUser->getUser()->getID() > 0)
		{
			$instance->setUser($this->sessionUser->getUser());
			$instance->save();
		}

		if ($instance->isFinalized)
		{
			$this->session->remove('CustomerOrder');
			return $this->getOrder();
		}

		// fixes issue when trying to add OrderedItem to unsaved(without ID) CustomerOrder.
		// ~ but i don't know if returning unsaved CustomerOrder is expected behaviour.
		if (!$instance->getDirtyState())
		{
			$instance->save();
		}
		
		$this->setOrder($instance);
		return $instance;
	}

	public function setOrder(CustomerOrder $order)
	{
		
		$this->session->set('CustomerOrder', $order->getID());

		$currency = $order->getCurrency();
		$currID = $currency->getID();

		$total = $order->getTotal();
		$orderArray = array('total' => array($currID => $total));
		$orderArray['formattedTotal'] = array($currID => $currency->getFormattedPrice($orderArray['total'][$currID]));
		$orderArray['basketCount'] = $order->getShoppingCartItemCount();
		$orderArray['currencyID'] = $currID;

		$isOrderable = $order->isOrderable();
		$orderArray['isOrderable'] = is_bool($isOrderable) ? $isOrderable : false;

		/*
		$items = array();
		foreach ($order->getPurchasedItems() as $item)
		{
			$items[] = $item->toArray();
		}

		$orderArray['items'] = new \businessrule\RuleOrderContainer($items);
		$orderArray['items']->setCoupons($order->getCoupons());
		$orderArray['items']->setTotal($total);
		*/

		$this->session->set('orderData', $orderArray);

		$this->instance = $order;
	}

	public function getOrderItems()
	{
		return array();
		
		
		$data = $this->session->get('orderData');
		if (isset($data['items']))
		{
			return $data['items'];
		}
	}

	public function getOrderData()
	{
		$this->setOrder($this->getsetOrder());
		
		return $this->session->get('orderData');
	}

	public function getEstimateAddress()
	{
		if ($address = $this->session->get('shippingEstimateAddress'))
		{
			return unserialize($address);
		}
		else
		{
			$order = $this->getOrder();
			if ($order->shippingAddress)
			{
				return $order->shippingAddress;
			}

			$user = $order->user;
			if ($user && !$user->isAnonymous())
			{
				/*
				foreach (array('defaultShippingAddress', 'defaultBillingAddress') as $key)
				{
					if ($address = $user->$key)
					{
						return $address->userAddress;
					}
				}
				*/
			}
		}

		return $this->getDefaultEstimateAddress();
	}

	public function getDefaultEstimateAddress()
	{
		$config = $this->config;
		$address = \user\UserAddress::getNewInstance();
		$address->countryID = $config->get('DEF_COUNTRY');

		if ($state = $config->get('DEF_STATE'))
		{
			$address->state = State::getInstanceByID($config->get('DEF_STATE'));
		}

		return $address;
	}

	public function setEstimateAddress(UserAddress $address)
	{
		$order = $this->getsetOrder();
		$estimateAddress = clone $address;
		$estimateAddress->removeSpecification();
		
		$this->session->set('shippingEstimateAddress', $estimateAddress);
	}

	public function save(CustomerOrder $order)
	{
		// mark shipment data as modified - to force saving
		$order->getShipments();

		$order->save();
		$this->setOrder($order);
	}
}
1
?>
