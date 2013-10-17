<?php

ClassLoader::import("application/controller/backend/abstract/StoreManagementController");
ClassLoader::import("application/model/order/CustomerOrder");
ClassLoader::import("application/model/order/OrderNote");
ClassLoader::import("application/model/category/Category");
ClassLoader::importNow("application/helper/getDateFromString");

/**
 * Main backend controller which stands as an entry point to administration functionality
 *
 * @package application/controller/backend
 * @author Integry Systems <http://integry.com>
 */
class IndexController extends StoreManagementController
{
	public function indexAction()
	{
		die('testing');
		$this->updateApplicationUri();

		// order stats
		$conditions = array(

			'last' => new EqualsOrMoreCond(new ARFieldHandle('CustomerOrder', 'dateCompleted'), time() - 86400),
			'new' => new IsNullCond(new ARFieldHandle('CustomerOrder', 'status')),
			'processing' => new EqualsCond(new ARFieldHandle('CustomerOrder', 'status'), CustomerOrder::STATUS_PROCESSING),
			'total' => new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true),
		);

		foreach ($conditions as $key => $cond)
		{
			$f = new ARSelectFilter($cond);
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), false));
			$orderCount[$key] = ActiveRecordModel::getRecordCount('CustomerOrder', $f);
		}

		// messages
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('OrderNote', 'isAdmin'), 0));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('OrderNote', 'isRead'), 0));
		$orderCount['messages'] = ActiveRecordModel::getRecordCount('OrderNote', $f);

		// inventory stats
		$lowStock = new EqualsOrLessCond(new ARFieldHandle('Product', 'stockCount'), $this->config->get('LOW_STOCK'));
		$lowStock->addAnd(new MoreThanCond(new ARFieldHandle('Product', 'stockCount'), 0));

		$conditions = array(

			'lowStock' => $lowStock,
			'outOfStock' => new EqualsOrLessCond(new ARFieldHandle('Product', 'stockCount'), 0),

		);

		foreach ($conditions as $key => $cond)
		{
			$cond->addAnd(new EqualsCond(new ARFieldHandle('Product', 'isEnabled'), true));
			$inventoryCount[$key] = ActiveRecordModel::getRecordCount('Product', new ARSelectFilter($cond));
		}

		// overall stats
		$rootCat = Category::getRootNode();
		$rootCat->load();


		$this->set('orderCount', $orderCount);
		$this->set('inventoryCount', $inventoryCount);
		$this->set('rootCat', $rootCat->toArray());
		$this->set('thisMonth', date('m'));
		$this->set('lastMonth', date('Y-m', strtotime(date('m') . '/15 -1 month')));

		$this->set('ordersLast24', $this->getOrderCount('-24 hours', 'now'));
		$this->set('ordersThisWeek', $this->getOrderCount('w:Monday', 'now'));
		$this->set('ordersThisMonth', $this->getOrderCount(date('m') . '/1', 'now'));
		$this->set('ordersLastMonth', $this->getOrderCount($response->get('lastMonth') . '-1', date('m') . '/1'));

		$this->set('lastOrders', $this->getLastOrders());
	}

	public function totalOrdersAction()
	{
		// "January 1 | now" - this year
		// or
		// "w:Monday ~ -1 week | w:Monday" - last week
		list($from, $to) = explode(' | ', $this->request->get('period'));

		$count = $this->getOrderCount($from, $to);
		if (!$count)
		{
			$count = '&nbsp;0';
		}

		return new RawResponse($count);
	}

	protected function getLastOrders()
	{
		$f = select();
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), false));
		$f->setOrder(new ARFieldHandle('CustomerOrder', 'dateCompleted'), 'desc');
		$f->setLimit(10);

		$customerOrders = ActiveRecordModel::getRecordSet('CustomerOrder', $f, ActiveRecordModel::LOAD_REFERENCES);
		$ordersArray = array();
		if($customerOrders->size() > 0)
		{
			$i = 0;
			foreach($customerOrders as $order)
			{
				$ordersArray[$i] = $order->toArray();
				$ordersArray[$i]['status_name'] = CustomerOrder::getStatusName($ordersArray[$i]['status'] ? $ordersArray[$i]['status'] : CustomerOrder::STATUS_NEW);
				$i++;
			}
			return $ordersArray;
		}

		return array();
	}

	protected function getOrderCount($from, $to)
	{
		$cond = new EqualsOrMoreCond(new ARFieldHandle('CustomerOrder', 'dateCompleted'), getDateFromString($from));

		if ('now' != $to)
		{
			$cond->addAnd(new EqualsOrLessCond(new ARFieldHandle('CustomerOrder', 'dateCompleted'), getDateFromString($to)));
		}

		$f = new ARSelectFilter($cond);
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isCancelled'), false));

		return ActiveRecordModel::getRecordCount('CustomerOrder', $f);
	}

	public function setUserPreferenceAction()
	{
		$user = $this->user;
		$user->setPreference($this->request->get('key'), $this->request->get('value'));
		$user->save();
	}

	public function keepAliveAction()
	{
		return new RawResponse('OK');
	}

	private function updateApplicationUri()
	{
		$data = array('url' => $this->router->createFullUrl($this->router->createUrl(array())), 'rewrite' => !$this->request->get('noRewrite', false));
		file_put_contents($this->config->getPath('storage/configuration/') . 'url.php', '<?php return ' . var_export($data, true) . '; ?>');
	}
}

?>
