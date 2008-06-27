<?php

ClassLoader::import('application.model.eav.EavField');
ClassLoader::import('application.model.eavcommon.iEavFieldManager');

/**
 * Custom field container for a particular EAV type (users, manufacturers, orders, etc)
 *
 * @package application.model.eav
 * @author Integry Systems <http://integry.com>
 */
class EavFieldManager implements iEavFieldManager
{
	private $classID;

	public function __construct($classID)
	{
		if (!is_numeric($classID))
		{
			$classID = EavField::getClassID($classID);
		}

		$this->classID = $classID;
	}

	public function getClassID()
	{
		return $this->classID;
	}

	public function getClassName()
	{
		return EavField::getClassNameById($this->classID);
	}

	public function getSpecFieldsWithGroupsArray()
	{
		$groups = ActiveRecordModel::getRecordSetArray('EavFieldGroup', $this->getGroupFilter());
		$fields = ActiveRecordModel::getRecordSetArray('EavField', $this->getFieldFilter(), array('EavFieldGroup'));
		return ActiveRecordGroup::mergeGroupsWithFields('EavFieldGroup', $groups, $fields);
	}

	/**
	 * Creates a select filter for custom fields
	 *
	 * @param bool $includeParentFields
	 * @return ARSelectFilter
	 */
	private function getFieldFilter()
	{
		$filter = new ARSelectFilter(new EqualsCond(new ARFieldHandle('EavField', 'classID'), $this->classID));

		$filter->setOrder(new ARFieldHandle('EavFieldGroup', 'position'));
		$filter->setOrder(new ARFieldHandle('EavField', 'position'));

		return $filter;
	}

	/**
	 * Creates a select filter for fields groups
	 * @return ARSelectFilter
	 */
	private function getGroupFilter()
	{
		$filter = new ARSelectFilter(new EqualsCond(new ARFieldHandle('EavFieldGroup', 'classID'), $this->classID));
		$filter->setOrder(new ARFieldHandle('EavFieldGroup', 'position'));

		return $filter;
	}
}

?>