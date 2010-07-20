<?php

ClassLoader::import('application.model.filter.FilterInterface');

/**
 * Filter product list by search keyword.
 *
 * @package application.model.filter
 * @author Integry Systems <http://integry.com>
 */
class SearchFilter implements FilterInterface
{
	private $query;

	function __construct($searchQuery)
	{
		$searchQuery = preg_replace('/\s+/', ' ', $searchQuery);
		$this->query = rawurldecode(preg_replace('/(_)([0-9A-Z]{2})/', '%$2', $searchQuery));
	}

	public function getCondition()
	{
		// analyze search query
		// find exact phrases first
		$query = $this->query;
		preg_match_all('/"(.*)"/sU', $query, $matches);

		$phrases = array();
		if ($matches[1])
		{
			$phrases = $matches[1];
		}

		$query = $this->getCleanedQuery($query);
		$phrases = array_merge($phrases, explode(' ', $query));

		$searchFields = array('name', 'keywords', 'shortDescription', 'longDescription', 'sku');

		$conditions = array();

		foreach ($phrases as $phrase)
		{
			$searchCond = null;
			foreach ($searchFields as $field)
			{
				$cond = new LikeCond(new ARFieldHandle('Product', $field), '%' . $phrase . '%');
				if (!$searchCond)
				{
					$searchCond = $cond;
				}
				else
				{
					$searchCond->addOr($cond);
				}
			}

			$conditions[] = $searchCond;
		}

		$condition = new AndChainCondition($conditions);

		ActiveRecordModel::getApplication()->processInstancePlugins('searchFilter', $condition);

		return $condition;
	}

	public function getCleanedQuery($query)
	{
		$query = preg_replace('/"(.*)"/sU', '', $query);
		$query = preg_replace('/[-,\._!\?\\/]/', ' ', $query);
		$query = preg_replace('/ {2,}/', ' ', $query);

		$query = trim($query);

		return $query;
	}

	public function defineJoin(ARSelectFilter $filter)
	{
		/* do nothing */
	}

	public function getID()
	{
		return 's';
	}

	public function getKeywords()
	{
		return $this->query;
	}

	public function toArray()
	{
		$array = array();
		$array['name_lang'] = '"' . $this->query . '"';
		$array['handle'] = preg_replace('/(%)([0-9A-Z]{2})/', '_$2', rawurlencode($this->query));
		$array['ID'] = $this->getID();
		return $array;
	}
}

?>