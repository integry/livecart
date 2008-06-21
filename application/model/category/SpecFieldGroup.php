<?php

ClassLoader::import("application.model.category.SpecField");
ClassLoader::import("application.model.eav.EavFieldGroupCommon");
ClassLoader::import("application.model.category.Category");

/**
 * SpecFieldGroups allow to group related attributes (SpecFields) together.
 *
 * @package application.model.category
 * @author Integry Systems <http://integry.com>
 */
class SpecFieldGroup extends EavFieldGroupCommon
{
	/**
	 * Define SpecFieldGroup database schema
	 */
	public static function defineSchema()
	{
		$schema = parent::defineSchema(__CLASS__);
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", "Category", ARInteger::instance()));
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Get new SpecFieldGroup active record instance
	 *
	 * @return SpecFieldGroup
	 */
	public static function getNewInstance(Category $category)
	{
		$inst = parent::getNewInstance(__CLASS__);
		$inst->category->set($category);

		return $inst;
	}

	/**
	 * Get specification group item instance
	 *
	 * @param int|array $recordID Record id
	 * @param bool $loadRecordData If true loads record's structure and data
	 * @param bool $loadReferencedRecords If true loads all referenced records
	 * @return SpecFieldGroup
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	protected function getParentCondition()
	{
		return new EqualsCond(new ARFieldHandle(get_class($this), 'categoryID'), $this->category->get()->getID());
	}
}

?>