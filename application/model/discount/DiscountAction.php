<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.discount.DiscountCondition');

class DiscountAction extends ActiveRecordModel
{
	const TYPE_ORDER_DISCOUNT = 1;

	const MEASURE_PERCENT = 1;
	const MEASURE_AMOUNT = 2;

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

		$schema->registerField(new ARField("type", ARInteger::instance()));
		$schema->registerField(new ARField("amountMeasure", ARInteger::instance()));
		$schema->registerField(new ARField("amount", ARFloat::instance()));
	}

	public static function getNewInstance(DiscountCondition $condition)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->condition->set($condition);

		return $instance;
	}

}

?>