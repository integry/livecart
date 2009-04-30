<?php

ClassLoader::import('application.model.filter.SpecificationFilterInterface');
ClassLoader::importNow('application.helper.CreateHandleString');

/**
 * Filter product list by selector attribute value. SelectorFilters are being generated automatically based on
 * the available attribute values.
 *
 * @package application.model.filter
 * @author Integry Systems <http://integry.com>
 */
class SelectorFilter implements SpecificationFilterInterface
{
	private $specFieldValue;
	private $filterGroup;

	public function __construct(SpecFieldValue $specFieldValue, FilterGroup $group = null)
	{
		$this->specFieldValue = $specFieldValue;
		$this->filterGroup = $group;
	}

	public function getCondition()
	{
		return new EqualsCond(new ARExpressionHandle($this->getJoinAlias() . '.SpecFieldValueID'), $this->specFieldValue->getID());
	}

	/**
	 *	Adds JOIN definition to ARSelectFilter to retrieve product attribute value for the particular SpecField
	 *
	 *	@param	ARSelectFilter	$filter	Filter instance
	 */
	public function defineJoin(ARSelectFilter $filter)
	{
		$table = $this->getJoinAlias();
		$filter->joinTable('SpecificationItem', 'Product', 'productID AND ' . $table . '.SpecFieldValueID = ' . $this->specFieldValue->getID(), 'ID', $table);
	}

	public function getID()
	{
		return $this->specFieldValue->specField->get()->getID() . '_' . $this->specFieldValue->getID();
	}

	public function toArray()
	{
		$array = $this->specFieldValue->toArray();
		$array['name_lang'] = $array['value_lang'];
		$array['handle'] = createHandleString($array['value_lang']);
		$array['ID'] = 'v' . $array['ID'];
		if (!isset($array['FilterGroup']))
		{
			$array['FilterGroup']['SpecField'] = $array['SpecField'];
		}
		return $array;
	}

	public function getSpecField()
	{
		return $this->specFieldValue->specField->get();
	}

	public function getFilterGroup()
	{
		return $this->filterGroup;
	}

	protected function getJoinAlias()
	{
		return 'specFieldValue_' . $this->specFieldValue->getID();
	}
}

?>