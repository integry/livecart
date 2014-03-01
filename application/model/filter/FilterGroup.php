<?php

namespace filter;

/**
 * Filters allow to filter the product list by specific product attribute values.
 * FilterGroup is a container of Filters that are based on the same attribute.
 * For selector attribute values, the Filters are generated automatically.
 *
 * @package application/model/filter
 * @author Integry Systems <http://integry.com>
 */
 
class FilterGroup extends \system\MultilingualObject implements \filter\FilterInterface
{
	const STYLE_LINKS = 0;
	const STYLE_DROPDOWN = 1;
	const LOC_SIDE = 0;
	const LOC_TOP = 1;

	public $ID;
	public $eavFieldID;//", "SpecField", "ID", "SpecField;
	public $name;
	public $position;
	public $isEnabled;
	public $displayStyle;
	public $displayLocation;
	
	protected $options = array();

	/**
	 * Get new instance of FilterGroup record
	 *
	 * @return ActiveRecord
	 */
	public static function getNewInstance(SpecField $specField)
	{
		$inst = new self();
		$inst->specFieldID = $specField->getID();
		return $inst;
	}
	
	/**
	 * Add new filter to filter group
	 *
	 * @param Filter $filter
	 */
	public function addFilter(Filter $filter)
	{
		$filter->filterGroup = $this;
		$filter->save();
	}

	public function registerOption(\eav\EavValue $value)
	{
		$this->options[] = $value;
	}
	
	public function getOptions()
	{
		return $this->options;
	}
	
	/*####################  Saving ####################*/

	/**
	 * Save group filters in database
	 *
	 * @param array $filters
	 * @param int $specFieldType
	 * @param array $languages
	 */
	/*
	public function saveFilters($filters, $specFieldType, $languageCodes)
	{
		$position = 1;
		$filtersCount = count($filters);
		$i = 0;

		$newIDs = array();
		foreach ($filters as $key => $value)
		{
			// Ignore last new empty filter
			$i++;
			if($filtersCount == $i && $value['name'][$languageCodes[0]] == '' && preg_match("/new/", $key)) continue;

			if(preg_match('/^new/', $key))
			{
				$filter = Filter::getNewInstance($this);
			}
			else
			{
				$filter = Filter::getInstanceByID((int)$key);
			}

			$filter->setLanguageField('name', $value['name'], $languageCodes);

			if($specFieldType == SpecField::TYPE_TEXT_DATE)
			{
				$filter->rangeDateStart = $value['rangeDateStart']);
				$filter->rangeDateEnd = $value['rangeDateEnd']);
				$filter->rangeStart = null;
				$filter->rangeEnd = null;
			}
			else
			{
				$filter->rangeDateStart = null;
				$filter->rangeDateEnd = null;
				$filter->rangeStart = $value['rangeStart']);
				$filter->rangeEnd = $value['rangeEnd']);
			}

			$filter->filterGroup = $this;
			$filter->position = $position++);
			$filter->save();

			if(preg_match('/^new/', $key))
			{
				$newIDs[$filter->getID()] = $key;
			}

		}

		return $newIDs;
	}
	*/

	public function toArray()
	{
		$arr = parent::toArray();
		$arr['options'] = array();
		foreach ($this->options as $option)
		{
			$arr['options'][] = $option->toArray();
		}
		
		return $arr;
	}

	public function beforeCreate()
	{
		$this->setLastPosition();
	}
	
	public function setCondition(\Phalcon\Mvc\Model\Query\Builder $query, $params)
	{
		foreach ($params as $key => $param)
		{
			if (!is_numeric($param))
			{
				unset($params[$key]);
			}
		}

		// @todo: remove
		if (2 == $this->getID())
		{
			$query->andWhere('(SUBQUERY("SELECT COUNT(*) FROM EavItem WHERE EavItem.objectID=EavObject.ID AND EavItem.fieldID=' . $this->eavFieldID . ' AND EavItem.valueID IN (' . implode(', ', $params) . ')") > 0) OR (SUBQUERY("SELECT COUNT(*) FROM EavItem WHERE EavItem.objectID=EavObject.ID AND EavItem.fieldID = 19") = 0)');
		}
		else
		{
			$query->andWhere('SUBQUERY("SELECT COUNT(*) FROM EavItem WHERE EavItem.objectID=EavObject.ID AND EavItem.fieldID=' . $this->eavFieldID . '  AND EavItem.valueID IN (' . implode(', ', $params) . ')") > 0');
		}
		
		//$query->andWhere('SUBQUERY("SELECT COUNT(*) FROM EavItem WHERE EavItem.objectID=EavObject.ID AND EavItem.valueID IN (' . implode(', ', $params) . ')") > 0');
	}

	/*####################  Get related objects ####################*/

	/**
	 * Loads a set of spec field records in current category
	 *
	 * @return ARSet
	 */
	public function getFiltersList()
	{
		$filter = new ARSelectFilter();
		$filter->orderBy(new ARFieldHandle("Filter", "position"));
		$filter->setCondition(new EqualsCond(new ARFieldHandle("Filter", "filterGroupID"), $this->getID()));

		return Filter::getRecordSet($filter);
	}
}

?>
