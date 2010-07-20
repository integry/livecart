<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.category.Category');

/**
 *
 * @package application.model.category
 * @author Integry Systems <http://integry.com>
 */
class CategoryRelationship extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField('ID', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('relatedCategoryID', 'Category', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('categoryID', 'Category', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARField('position', ARInteger::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Category $category, Category $relatedCategory)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->category->set($category);
		$instance->relatedCategory->set($relatedCategory);
		return $instance;
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
		$this->setLastPosition('category');

		return parent::insert();
	}
}

?>