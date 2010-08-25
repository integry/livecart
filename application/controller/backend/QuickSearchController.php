<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.model.searchable.SearchableModel');

/**
 *
 * @package application.controller.backend
 * @author Integry Systems <http://integry.com>
 */
class QuickSearchController extends StoreManagementController
{
	const LIMIT = 5;
	public function search()
	{
/*
	* Produkti (pēc sku vai nosaukuma)
	* Kategorijas (nosaukums)
	* Lietotāji (vārds, e-pasts)
	* Pasūtījumi (invoiceNumber)
	* DiscountCondition (name) - business rules
	* StaticPage (title)
	* NewsPost (title)
*/
		$request = $this->getRequest();
		$q = $request->get('q');
		$res = array();
		foreach (SearchableModel::getInstances(SearchableModel::FRONTEND_SEARCH_MODEL|SearchableModel::BACKEND_SEARCH_MODEL) as $searchable)
		{
			$f = $searchable->getSelectFilter($q);
			$f->setLimit(self::LIMIT);
			$res[$searchable->getClassName()] = $this->fetchData($searchable, $f);
		}

		return new ActionResponse('result', $res, 'randomToken', md5(time().mt_rand(1,9999999999)));
	}
	
	
	private function fetchData(SearchableModel $searchable, ARSelectFilter $filter)
	{
		$class = $searchable->getClassName();
		$ret = array();
		$ret['records'] = ActiveRecordModel::getRecordSetArray($class, $filter, true);
		$ret['count'] = ActiveRecordModel::getRecordCount($class, $filter);
		$ret['meta'] = $searchable->toArray();
		if(method_exists($this, 'toArray_'.$searchable->getClassName()))
		{
			call_user_func_array(
				array($this, 'toArray_'.$searchable->getClassName()), array(&$ret['records']));
		}
		return $ret;
	}

	private function toArray_CustomerOrder($records)
	{
		foreach($records as &$order)
		{
			$currency = Currency::getInstanceById($order['currencyID']);
			$order['formattedTotalAmount'] =  $currency->getFormattedPrice($order['totalAmount']);
		}
	}
}

?>
