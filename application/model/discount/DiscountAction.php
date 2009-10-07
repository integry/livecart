<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.discount.DiscountCondition');
ClassLoader::import('application.model.discount.DiscountActionSet');
ClassLoader::import('application.model.order.OrderDiscount');
ClassLoader::import('application.model.businessrule.interface.*');
ClassLoader::import('application.model.businessrule.BusinessRuleController');

/**
 *
 * @author Integry Systems
 * @package application.model.discount
 */
class DiscountAction extends ActiveRecordModel
{
	const TYPE_ORDER_DISCOUNT = 0;
	const TYPE_ITEM_DISCOUNT = 1;
	const TYPE_CUSTOM_DISCOUNT = 5;

	/**
	 * Action for discount condition (define the actual discount)
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("conditionID", "DiscountCondition", "ID", "DiscountCondition", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("actionConditionID", "DiscountCondition", "ID", "DiscountCondition", ARInteger::instance()));

		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("isOrderLevel", ARBool::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance()));

		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("discountStep", ARInteger::instance()));
		$schema->registerField(new ARField("discountLimit", ARInteger::instance()));

		$schema->registerField(new ARField("amount", ARFloat::instance()));
		$schema->registerField(new ARField("actionClass", ARVarchar::instance(80)));
		$schema->registerField(new ARField("serializedData", ARText::instance()));
	}

	public static function getNewInstance(DiscountCondition $condition, $className = 'RuleActionPercentageDiscount')
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->condition->set($condition);
		$instance->actionClass->set($className);

		return $instance;
	}

	private function loadActionRuleClass($className)
	{
		ClassLoader::import('application.model.businessrule.action.' . $className);
		if (!class_exists($className, false))
		{
			foreach (self::getApplication()->getPlugins('businessrule/action/' . $className) as $plugin)
			{
				include_once $plugin['path'];
			}
		}

		return $className;
	}

	public function setParamValue($key, $value)
	{
		$params = unserialize($this->serializedData->get());
		$params[$key] = $value;
		$this->serializedData->set(serialize($params));
	}

	public function save()
	{
		BusinessRuleController::clearCache();
		return parent::save();
	}

	protected function insert()
	{
		$this->setLastPosition();
		return parent::insert();
	}

	/**
	 * Creates array representation
	 *
	 * @return array
	 */
	protected static function transformArray($array, ARSchema $schema)
	{
		if (!empty($array['serializedData']))
		{
			$array['serializedData'] = unserialize($array['serializedData']);
		}

		return parent::transformArray($array, $schema);
	}
}

?>