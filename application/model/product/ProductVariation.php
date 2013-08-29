<?php

ClassLoader::import('application/model/product/ProductVariationType');
ClassLoader::import('application/model/system/MultilingualObject');

/**
 * Defines a product variation selection.
 *
 * For example, if "size" is ProductVariationType, then ProductVariation would be "small", "normal", "large", etc.
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductVariation extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $typeID", "ProductVariationType", "ID", null, ARInteger::instance()));

		public $name;
		public $position;
	}

	public static function getNewInstance(ProductVariationType $type)
	{
		$instance = new __CLASS__();
		$instance->type = $type;
		return $instance;
	}

	protected function insert()
	{
		if (is_null($this->position->get()))
		{
			$this->setLastPosition('type');
		}
		parent::insert();
	}
}

?>