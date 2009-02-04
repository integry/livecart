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
		return $response;
	}

	public function totalOrders()
	{
		// "January 1 | now" - this year
		// or
		// "w:Monday ~ -1 week | w:Monday" - last week
		list($from, $to) = explode(' | ', $this->request->get('period'));

		$cond = new EqualsOrMoreCond(new ARFieldHandle('CustomerOrder', 'dateCompleted'), getDateFromString($from));

		if ('now' != $to)
		{
			$cond->addAnd(new EqualsOrLessCond(new ARFieldHandle('CustomerOrder', 'dateCompleted'), getDateFromString($to)));
		}

		$f = new ARSelectFilter($cond);
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));

		return new RawResponse(ActiveRecordModel::getRecordCount('CustomerOrder', $f));
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
}

?>
