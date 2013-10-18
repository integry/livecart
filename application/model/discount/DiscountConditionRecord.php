<?php


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
		$instance = new self();
		$instance->condition = $condition;

		$class = get_class($record);
		$field = strtolower(substr($class, 0, 1)) . substr($class, 1);
		if (!$instance->$field)
		{
			throw new ApplicationException($class . ' is not a valid instance for '  . __CLASS__);
		}

		$instance->$field = $record;

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

	public function beforeCreate()
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