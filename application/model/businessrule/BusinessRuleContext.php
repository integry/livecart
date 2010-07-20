<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.businessrule.RuleProductContainer');
ClassLoader::import('application.model.businessrule.RuleOrderContainer');

/**
 * Defines context for evaluating business rules
 *
 * @author Integry Systems
 * @package application.model.businessrule
 */
class BusinessRuleContext
{
	private $order;

	private $user;

	private $products = array();

	private $pastOrders = null;

	private $messages = array();

	public function setOrder(BusinessRuleOrderInterface $order)
	{
		$this->order = $order;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function getProducts()
	{
		return $this->products;
	}

	public function resetProducts()
	{
		$this->products = array();
	}

	public function addProduct($product)
	{
		static $calls = 0;

		$item = new RuleProductContainer($product);
		$this->products[] = $item;
		return $item;
	}

	public function removeLastProduct()
	{
		return array_pop($this->products);
	}

	public function addProducts(array $products)
	{
		foreach ($products as $product)
		{
			$this->addProduct($product);
		}
	}

	public function getPastOrders()
	{
		if (!is_null($this->pastOrders))
		{
			return $this->pastOrders;
		}

		if (!$this->user)
		{
			return array();
		}

		$sessionUser = SessionUser::getUser();
		if ($this->isSessionCacheUsable())
		{
			$session = new Session();
			$sessionHandler = ActiveRecordModel::getApplication()->getSessionHandler();
			$pastOrders = $session->get('pastOrders');
			if (!$pastOrders || ($pastOrders['cacheUpdated'] != $sessionHandler->getCacheUpdateTime()))
			{
				unset($pastOrders);
			}
		}

		if (empty($pastOrders))
		{
			$f = select(eq('CustomerOrder.userID', $this->user->getID()), eq('CustomerOrder.isFinalized', true), eq('CustomerOrder.isCancelled', 0), eq('CustomerOrder.isPaid', true));
			$f->setOrder(f('OrderedItem.customerOrderID'), 'DESC');
			$records = ActiveRecordModel::getRecordSetArray('OrderedItem', $f, array('CustomerOrder', 'Product'));

			// load variation parent products separately
			$parentIDs = array();
			foreach ($records as $record)
			{
				if ($record['Product']['parentID'])
				{
					//$parentIDs[$record['Product']['parentID']] = true;
				}
			}

			if ($parentIDs)
			{
				$parents = array();
				foreach (ActiveRecordModel::getRecordSetArray('Product', select(in('Product.ID', array_keys($parentIDs)))) as $parent)
				{
					$parents[$parent['ID']] = $parent;
				}

				foreach ($records as &$record)
				{
					if ($record['Product']['parentID'])
					{
						$record['Product']['Parent'] = $parents[$record['Product']['parentID']];
					}
				}
			}

			// split records by orders
			$orders = array();
			foreach ($records as $record)
			{
				$orders[$record['customerOrderID']][] = $record;
			}

			$pastOrders = array();
			foreach ($orders as $order)
			{
				$pastOrders[] = new RuleOrderContainer($order);
			}

			$pastOrders = array('cacheUpdated' => time(), 'orders' => $pastOrders);
		}

		if ($this->isSessionCacheUsable())
		{
			$session->set('pastOrders', $pastOrders);
			$sessionHandler->updateCacheTimestamp();
		}

		$this->pastOrders = $pastOrders;

		return $this->pastOrders;
	}

	public function getPastOrdersBetween($timeFrom, $timeTo)
	{
		$pastOrders = $this->getPastOrders();
		$pastOrders = $pastOrders ? $pastOrders['orders'] : array();

		foreach ($pastOrders as $key => $order)
		{
			$completed = strtotime($order->getCompletionDate());
			if ($completed < $timeFrom || $completed > $timeTo)
			{
				unset($pastOrders[$key]);
			}
		}

		return $pastOrders;
	}

	public function getUserGroupID()
	{
		if (!$this->user)
		{
			return 0;
		}

		return is_null($this->user->userGroup->get()) ? 0 : $this->user->userGroup->get()->getID();
	}

	public function addMessage($type, $text)
	{
		$this->messages[$type][] = $text;
	}

	private function isSessionCacheUsable()
	{
		if (!$this->user)
		{
			return false;
		}

		return $this->user->getID() == SessionUser::getUser()->getID();
	}
}

?>