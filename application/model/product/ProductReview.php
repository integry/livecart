<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.user.User');

/**
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductReview extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField('ID', ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField('productID', 'Product', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARPrimaryForeignKeyField('userID', 'User', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARField('title', ARVarchar::instance(255)));
		$schema->registerField(new ARField('text', ARText::instance()));
		$schema->registerField(new ARField('dateCreated', ARDateTime::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Product $product)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product->set($product);
		return $instance;
	}
}

?>
