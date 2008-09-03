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

	public static function getNewInstance(DiscountCondition $condition, ActiveRecordModel $record)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->condition->set($condition);

		$class = get_class($record);
		$field = strtolower(substr($class, 0, 1)) . substr($class, 1);
		if (!$instance->$field)
		{
			throw new ApplicationException($class . ' is not a valid instance for '  . __CLASS__);
		}

		$instance->$field->set($record);

		return $instance;
	}

	protected function insert()
	{
		parent::insert();
		$this->updateConditionRecordCount();
	}

	public function delete()
	{
		parent::delete();
		$this->updateConditionRecordCount(false);
	}

	private function updateConditionRecordCount($increase = true)
	{
		$update = new ARUpdateFilter();
		$update->addModifier('recordCount', new ARExpressionHandle('recordCount' . ($increase ? '+' : '-') . '1'));
		$this->condition->get()->updateRecord($update);
		$this->condition->get()->reload();
	}
}

?>