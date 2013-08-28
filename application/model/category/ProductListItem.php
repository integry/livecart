<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.category.ProductList');
ClassLoader::import('application.model.product.Product');

/**
 *
 * @package application.model.category
 * @author Integry Systems <http://integry.com>
 */
class ProductListItem extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		public $ID;
		public $productListID', 'ProductList', 'ID', null, ARInteger::instance()));
		public $productID', 'Product', 'ID', null, ARInteger::instance()));
		public $position;
		$schema->registerAutoReference('productID');
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(ProductList $productList, Product $product)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->productList = $productList);
		$instance->product = $product);
		return $instance;
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
		$this->setLastPosition('productList');

		return parent::insert();
	}
}

?>