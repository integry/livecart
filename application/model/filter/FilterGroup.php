<?php


/**
 * Filters allow to filter the product list by specific product attribute values.
 * FilterGroup is a container of Filters that are based on the same attribute.
 * For selector attribute values, the Filters are generated automatically.
 *
 * @package application/model/filter
 * @author Integry Systems <http://integry.com>
 */
class FilterGroup extends MultilingualObject
{
	const STYLE_LINKS = 0;
	const STYLE_DROPDOWN = 1;
	const LOC_SIDE = 0;
	const LOC_TOP = 1;

	/**
	 * Define FilterGroup database schema
	 */
	public static function defineSchema()
	{
		$schema = self::getSchemaInstance(__CLASS__);
		$schema->setName(__CLASS__);

		public $ID;
		public $specFieldID", "SpecField", "ID", "SpecField;
		public $name;
		public $position;
		public $isEnabled;
		public $displayStyle;
		public $displayLocation;
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Get new instance of FilterGroup record
	 *
	 * @return ActiveRecord
	 */
	public static function getNewInstance(SpecField $specField)
	{
		$inst = new self();
		$inst->specField = $specField;
		return $inst;
	}

	/**
	 * Get FilterGroup active record instance
	 *
	 * @param integer $recordID
	 * @param boolean $loadRecordData
	 * @param boolean $loadReferencedRecords
	 * @return Filter
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Delete filter group from database by id
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public static function deletebyID($id)
	{
		return parent::deleteByID(__CLASS__, $id);
	}

	/**
	 * Get record set of filter groups using select filter
	 *
	 * @param ARSelectFilter $filter
	 * @return ARSet
	 */
	public static function getRecordSetArray(ARSelectFilter $filter)
	{
		return parent::getRecordSetArray(__CLASS__, $filter);
	}

	/**
	 * Get record set as array of filter groups using select filter
	 *
	 * @param ARSelectFilter $filter
	 * @return array
	 */
	public static function getRecordSet(ARSelectFilter $filter)
	{
		return parent::getRecordSet(__CLASS__, $filter);
	}

	/*####################  Value retrieval and manipulation ####################*/

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

	/**
	 * This method is checking if SpecField record with passed id exist in the database
	 *
	 * @param int $id Record id
	 * @return boolean
	 */
	public static function exists($id)
	{
		return ActiveRecord::objectExists(__CLASS__, (int)$id);
	}

	/*####################  Saving ####################*/

	/**
	 * Save group filters in database
	 *
	 * @param array $filters
	 * @param int $specFieldType
	 * @param array $languages
	 */
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

	public function beforeCreate()
	{
		$this->setLastPosition();

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