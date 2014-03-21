<?php

/**
 * Main backend controller which stands as an entry point to administration functionality
 *
 * @package application/controller/backend
 * @author Integry Systems <http://integry.com>
 */
class IndexController extends ControllerBackend
{
	public function indexAction()
	{
		/*
		die('testing');
		$this->updateApplicationUri();

		// order stats
		$conditions = array(

			'last' => new EqualsOrMoreCond('CustomerOrder.dateCompleted', time() - 86400),
			'new' => new IsNullCond('CustomerOrder.status'),
			'processing' => 'CustomerOrder.status = :CustomerOrder.status:', array('CustomerOrder.status' => CustomerOrder::STATUS_PROCESSING),
			'total' => 'CustomerOrder.isFinalized = :CustomerOrder.isFinalized:', array('CustomerOrder.isFinalized' => true),
		);

		foreach ($conditions as $key => $cond)
		{
			$f = new ARSelectFilter($cond);
			$f->andWhere('CustomerOrder.isFinalized = :CustomerOrder.isFinalized:', array('CustomerOrder.isFinalized' => true));
			$f->andWhere('CustomerOrder.isCancelled = :CustomerOrder.isCancelled:', array('CustomerOrder.isCancelled' => false));
			$orderCount[$key] = ActiveRecordModel::getRecordCount('CustomerOrder', $f);
		}

		// messages
		$f = query::query()->where('OrderNote.isAdmin = :OrderNote.isAdmin:', array('OrderNote.isAdmin' => 0));
		$f->andWhere('OrderNote.isRead = :OrderNote.isRead:', array('OrderNote.isRead' => 0));
		$orderCount['messages'] = ActiveRecordModel::getRecordCount('OrderNote', $f);

		// inventory stats
		$lowStock = new EqualsOrLessCond('Product.stockCount', $this->config->get('LOW_STOCK'));
		$lowStock->andWhere(new MoreThanCond('Product.stockCount', 0));

		$conditions = array(

			'lowStock' => $lowStock,
			'outOfStock' => new EqualsOrLessCond('Product.stockCount', 0),

		);

		foreach ($conditions as $key => $cond)
		{
			$cond->andWhere('Product.isEnabled = :Product.isEnabled:', array('Product.isEnabled' => true));
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
		*/
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
		$f->andWhere('CustomerOrder.isFinalized = :CustomerOrder.isFinalized:', array('CustomerOrder.isFinalized' => true));
		$f->andWhere('CustomerOrder.isCancelled = :CustomerOrder.isCancelled:', array('CustomerOrder.isCancelled' => false));
		$f->orderBy('CustomerOrder.dateCompleted', 'desc');
		$f->limit(10);

		$customerOrders = ActiveRecordModel::getRecordSet('CustomerOrder', $f, ActiveRecordModel::LOAD_REFERENCES);
		$ordersArray = array();
		if($customerOrders->count() > 0)
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
		$cond = new EqualsOrMoreCond('CustomerOrder.dateCompleted', getDateFromString($from));

		if ('now' != $to)
		{
			$cond->andWhere(new EqualsOrLessCond('CustomerOrder.dateCompleted', getDateFromString($to)));
		}

		$f = new ARSelectFilter($cond);
		$f->andWhere('CustomerOrder.isFinalized = :CustomerOrder.isFinalized:', array('CustomerOrder.isFinalized' => true));
		$f->andWhere('CustomerOrder.isCancelled = :CustomerOrder.isCancelled:', array('CustomerOrder.isCancelled' => false));

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
