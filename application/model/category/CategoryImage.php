<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 *
 * @package application.model.category
 */
class CategoryImage extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("CategoryImage");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", "Category", ARInteger::instance()));
		$schema->registerField(new ARField("title", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
	}
	
	public function getPath($size = 0)
	{
		$path = 'upload/categoryimage/' . $this->category->get()->getID() . '-' . $this->getID() . '-' . $size . '.jpg';
	  	return $path;
	}
	
}

?>