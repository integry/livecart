<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.user.User');

/**
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductRatingType extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField('ID', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('categoryID', 'Category', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARField('name', ARArray::instance()));
		$schema->registerField(new ARField('position', ARInteger::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Category $category)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->category->set($category);
		return $instance;
	}
}

?>