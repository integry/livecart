<?php


/**
 *
 * @author Integry Systems
 * @package application/model/discount
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



		public $ID;
		public $conditionID", "DiscountCondition", "ID", "DiscountCondition;
		public $actionConditionID", "DiscountCondition", "ID", "DiscountCondition;

		public $isEnabled;
		public $isOrderLevel;
		public $type;

		public $position;
		public $discountStep;
		public $discountLimit;

		public $amount;
		public $actionClass;
		public $serializedData;
	}

	public static function getNewInstance(DiscountCondition $condition, $className = 'RuleActionPercentageDiscount')
	{
		$instance = new self();
		$instance->condition = $condition;
		$instance->actionClass = $className;

		return $instance;
	}

	private function loadActionRuleClass($className)
	{
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
		$params = unserialize($this->serializedData);
		$params[$key] = $value;
		$this->serializedData = serialize($params));
	}

	public function save()
	{
		BusinessRuleController::clearCache();
		return parent::save();
	}

	public function beforeCreate()
	{
		$this->setLastPosition();

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