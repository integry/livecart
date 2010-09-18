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
	private $query;
	
	public function resultBlockPriority()
	{
		return array
		(
			// sort result blocks (by classes) in this order:
			'Product',
			'CustomerOrder',
			'User',
			'Category'
		);
	}

	public function search()
	{
		$request = $this->getRequest();
		$this->query = $request->get('q');
		$cn = trim($request->get('class', ''));
		$to = $request->get('to', 0);
		$from = $request->get('from',0);
		if(!strlen($cn))
		{
			$cn = null;
		}
		$res = array();

		foreach (SearchableModel::getInstances(SearchableModel::FRONTEND_SEARCH_MODEL|SearchableModel::BACKEND_SEARCH_MODEL) as $searchable)
		{
			if($cn && $searchable->getClassName() != $cn)
			{
				continue;
			}
			$searchable->setOption('BACKEND_QUICK_SEARCH', true);
			$f = $searchable->getSelectFilter($this->query);
			$offset = 0;
			if ($cn)
			{
				$direction = $request->get('direction');
				if ($direction == 'next')
				{
					$offset = $to;
				}
				else if ($direction == 'previous')
				{
					$offset = $from - self::LIMIT - 1;
				}
			}
			if ($offset < 0)
			{
				$offset = 0;
			}
			$f->setLimit(self::LIMIT, $offset);
			$res[$searchable->getClassName()] = $this->fetchData($searchable, $f);
		}

		return new ActionResponse
		(
			'query', $this->query,
			'result', $res,
			'randomToken', md5(time().mt_rand(1,9999999999)),
			'to', $to,
			'from', $from,
			'classNames', $this->orderResultBlockKeys(array_keys($res)),
			'fullSearch', $cn == ''
		);
	}
	
	
	private function fetchData(SearchableModel $searchable, ARSelectFilter $filter)
	{
		$class = $searchable->getClassName();
		$ret = array();
		$ret['records'] = ActiveRecordModel::getRecordSetArray($class, $filter, true);
		$ret['count'] = ActiveRecordModel::getRecordCount($class, $filter);

		// calculate form and to
		$ret['from'] = $filter->getOffset();
		$ret['to'] = $filter->getLimit() + $ret['from'];
		$diff = $ret['to'] - $ret['from'];
		$c = count($ret['records']);
		if($diff != $c)
		{
			$ret['to'] = $ret['from']+$c;
		}
		$ret['from']++;

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

	private function toArray_SearchableItem($records)
	{
		foreach($records as &$item)
		{
			$item['meta'] = @unserialize($item['meta']);
		}
	}


	private function orderResultBlockKeys($data)
	{
		// Order by:
		//   first as in resultBlockPriority()
		//   then all others in alphabetical order
		// elements that are in priority list but are missing in data are ignored
		$priorityList = array_intersect($this->resultBlockPriority(),$data);
		sort($data);
		$data = array_flip($data);
		foreach ($data as &$value)
		{
			$value *= -1;
		}
		$data = array_merge($data, array_flip(array_reverse($priorityList)));
		arsort($data);
		return array_keys($data);
	}
}

?>