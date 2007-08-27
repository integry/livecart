<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.order.OrderNote");
ClassLoader::import("application.model.category.Category");

/**
 * Main backend controller which stands as an entry point to administration functionality
 *
 * @package application.controller.backend
 * @author Integry Systems <http://integry.com>
 *
 * @role login
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
		return $response;
	}
}

?>
