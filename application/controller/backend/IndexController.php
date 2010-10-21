<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.order.OrderNote");
ClassLoader::import("application.model.category.Category");
ClassLoader::importNow("application.helper.getDateFromString");

/**
 * Main backend controller which stands as an entry point to administration functionality
 *
 * @package application.controller.backend
 * @author Integry Systems <http://integry.com>
 */
class IndexController extends StoreManagementController
{
	public function index()
	{
		$this->loadLanguageFile('backend/abstract/ActiveGridQuickEdit');

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

		$response = new ActionResponse();
		$response->set('orderCount', $orderCount);
		$response->set('inventoryCount', $inventoryCount);
		$response->set('rootCat', $rootCat->toArray());
		$response->set('thisMonth', date('m'));
		$response->set('lastMonth', date('Y-m', strtotime(date('m') . '/15 -1 month')));

		$response->set('ordersLast24', $this->getOrderCount('-24 hours', 'now'));
		$response->set('ordersThisWeek', $this->getOrderCount('w:Monday', 'now'));
		$response->set('ordersThisMonth', $this->getOrderCount(date('m') . '/1', 'now'));
		$response->set('ordersLastMonth', $this->getOrderCount($response->get('lastMonth') . '-1', date('m') . '/1'));

		$response->set('lastOrders', $this->getLastOrders());
		return $response;
	}

	public function totalOrders()
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
				$order->loadAll();
				$order->getShipments();
				$order->loadDiscounts();
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

	public function setUserPreference()
	{
		$user = $this->user;
		$user->setPreference($this->request->get('key'), $this->request->get('value'));
		$user->save();
	}

	public function keepAlive()
	{
		return new RawResponse('OK');
	}

	private function updateApplicationUri()
	{
		$data = array('url' => $this->router->createFullUrl($this->router->createUrl(array())), 'rewrite' => !$this->request->get('noRewrite', false));
		file_put_contents(ClassLoader::getRealPath('storage.configuration.') . 'url.php', '<?php return ' . var_export($data, true) . '; ?>');
	}
}

?>
