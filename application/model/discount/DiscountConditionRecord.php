<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.discount.DiscountCondition');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.product.Manufacturer');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.user.UserGroup');
ClassLoader::import('application.model.delivery.DeliveryZone');
ClassLoader::import('application.model.businessrule.BusinessRuleController');

class DiscountConditionRecord extends ActiveRecordModel
{
	/**
	 * Define database schema for Category model
	 *
	 * @param string $className Schema name
	 */



		public $ID;
		public $conditionID", "DiscountCondition", "ID", "DiscountCondition;
		public $productID", "Product", "ID", "Product;
		public $categoryID", "Category", "ID", "Category;
		public $manufacturerID", "Manufacturer", "ID", "Manufacturer;
		public $userID", "User", "ID", "User;
		public $userGroupID", "UserGroup", "ID", "UserGroup;
		public $deliveryZoneID", "DeliveryZone", "ID", "DeliveryZone;
	}

	public static function getNewInstance(DiscountCondition $condition, ActiveRecordModel $record)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->condition = $condition);

		$class = get_class($record);
		$field = strtolower(substr($class, 0, 1)) . substr($class, 1);
		if (!$instance->$field)
		{
			throw new ApplicationException($class . ' is not a valid instance for '  . __CLASS__);
		}

		$instance->$field = $record);

		return $instance;
	}

	public static function getOwnerInstance($className, $id)
	{
		return ActiveRecordModel::getInstanceByID($className, $id, ActiveRecordModel::LOAD_DATA);
	}

	public function save()
	{
		BusinessRuleController::clearCache();
		return parent::save();
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
		BusinessRuleController::clearCache();
		$update = new ARUpdateFilter();
		$update->addModifier('recordCount', new ARExpressionHandle('recordCount' . ($increase ? '+' : '-') . '1'));
		$this->condition->updateRecord($update);
		$this->condition->reload();
	}
}

?>