<?php


/**
 *
 * @package application.controller.backend
 * @author Integry Systems <http://integry.com>
 */
class QuickSearchController extends StoreManagementController
{
	const LIMIT = 5;
	private $query;

	public function resultBlockPriorityAction()
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

	public function searchAction()
	{
		$request = $this->getRequest();
		$this->query = $request->gget('q');
		$cn = trim($request->gget('class', ''));
		$to = $request->gget('to', 0);
		$from = $request->gget('from',0);
		if(!strlen($cn))
		{
			$cn = null;
		}
		else
		{
			$cn = explode(',', $cn);
			if(!is_array($cn))
			{
				$cn = null;
			}
		}
		$cnOn = array();  // include class names. empty for all
		$cnOff = array(); // exclude class names.
		if ($cn)
		{
			foreach($cn as $token)
			{
				if(substr($token,0,1) == '-')
				{
					$cnOff[] = substr($token,1);
				}
				else
				{
					$cnOn[] = $token;
				}
			}
		}

		$res = array();

		$offset = 0;
		if ($cn)
		{
			$direction = $request->gget('direction');
			if ($direction == 'next')
			{
				$offset = $to;
			}
			else if ($direction == 'previous')
			{
				$offset = $from - $this->getLimit() - 1;
			}
		}
		if ($offset < 0)
		{
			$offset = 0;
		}

		// deal wirth ARSearchable models
		foreach (SearchableModel::getInstances(SearchableModel::FRONTEND_SEARCH_MODEL|SearchableModel::BACKEND_SEARCH_MODEL, true) as $searchable)
		{
			$searchableClassName = $searchable->getClassName();
			if (in_array($searchableClassName, $cnOff) //  turned off
				||
			(count($cnOn) && !in_array($searchableClassName, $cnOn))) // or "on" classes is not empty and class names does not match
			{
				continue;
			}
			$searchable->setOption('BACKEND_QUICK_SEARCH', true);
			$f = $searchable->getSelectFilter($this->query);
			$f->setLimit($this->getLimit(), $offset);
			$res[$searchable->getClassName()] = $this->fetchData($searchable, $f);
		}
        // deal with non-ar-searchable models
 		foreach (SearchableModel::getInstances(SearchableModel::FRONTEND_SEARCH_MODEL|SearchableModel::BACKEND_SEARCH_MODEL, false) as $searchable)
		{
			$searchableClassName = $searchable->getClassName();
			if (in_array($searchableClassName, $cnOff) //  turned off
				||
			(count($cnOn) && !in_array($searchableClassName, $cnOn))) // or "on" classes is not empty and class names does not match
			{
				continue;
			}

			// pass everything
			$searchable->setOption('query', $this->query);
			$searchable->setOption('from', $from);
			$searchable->setOption('to', $to);
			$searchable->setOption('limit', $this->getLimit());
			$searchable->setOption('offset', $offset);

			// let non-ar-searchable do its magick
			$res[$searchable->getClassName()] = $searchable->fetchData();
		}

		if (!empty($res['SearchableItem']))
		{
			foreach ($res['SearchableItem']['records'] as &$item)
			{
				$item['meta'] = unserialize($item['meta']);
			}
		}

		return new ActionResponse
		(
			'customResultTemplates', $this->getCustomResultTemplates(),
			'query', $this->query,
			'result', $res,
			'randomToken', md5(time().mt_rand(1,9999999999)),
			'to', $to,
			'from', $from,
			'classNames', $this->orderResultBlockKeys(array_keys($res)),
			'fullSearch', ($cn == '') || ($this->request->gget('limit') && !$this->request->gget('to'))
		);
	}

	private function getCustomResultTemplates()
	{
		$result = array();
		$resultTemplates = $this->getRequest()->get('resultTemplates');
		$pairs = explode('|', $resultTemplates);
		foreach($pairs as $pair)
		{
			if (strpos($pair, ':') == false)
			{
				continue;
			}
			list($class, $replace) = explode(':', $pair);
			$result[$class] = $replace;
		}
		return $result;
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
			call_user_func_array(array($this, 'toArray_'.$searchable->getClassName()), array(&$ret['records']));
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

	private function getLimit()
	{
		return $this->request->gget('limit') ? $this->request->gget('limit') : self::LIMIT;
	}
}

?>