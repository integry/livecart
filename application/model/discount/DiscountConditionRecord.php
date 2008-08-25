<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.discount.DiscountCondition');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.product.Manufacturer');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.user.UserGroup');
ClassLoader::import('application.model.delivery.DeliveryZone');

class DiscountConditionRecord extends ActiveRecordModel
{
	/**
	 * Define database schema for Category model
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("conditionID", "DiscountCondition", "ID", "DiscountCondition", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", "Category", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("manufacturerID", "Manufacturer", "ID", "Manufacturer", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userID", "User", "ID", "User", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("userGroupID", "UserGroup", "ID", "UserGroup", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone", ARInteger::instance()));
	}
}

?>