<?php

ClassLoader::import('application.model.product.ProductVariationType');
ClassLoader::import('application.model.system.MultilingualObject');

/**
 * Defines a product variation selection.
 *
 * For example, if "size" is ProductVariationType, then ProductVariation would be "small", "normal", "large", etc.
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductVariation extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("typeID", "ProductVariationType", "ID", null, ARInteger::instance()));

		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(4)));
	}

	public static function getNewInstance(ProductVariationType $type)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->type->set($type);
		return $instance;
	}

	protected function insert()
	{
		if (is_null($this->position->get()))
		{
			$this->setLastPosition();
		}
		parent::insert();
	}
}

?>