<?php

ClassLoader::import('application.model.discount.DiscountCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule
 */
abstract class RuleCondition
{
	protected $records;
	protected $conditions;
	protected $actions;

	protected $controller;

	/**
	 *  Evaluate main condition (without sub-conditions)
	 */
	public abstract function isApplicable();

	public function setController(BusinessRuleController $controller)
	{
		$this->controller = $controller;

		foreach ($this->conditions as $condition)
		{
			$condition->setController($controller);
		}
	}

	protected function getController()
	{
		return $this->controller;
	}

	protected function getContext()
	{
		return $this->controller;
	}

	/**
	 *  Evaluate condition tree
	 */
	public function isValid($instance = null)
	{
		if (!$this->isApplicable($instance))
		{
			return false;
		}

		if (!$this->conditions)
		{
			return true;
		}

		$isValid = false;
		foreach ($this->conditions as $condition)
		{
			if ($condition->isValid($instance))
			{
				$isValid = true;
			}
			else if ($this->params['isAllSubconditions'])
			{
				return false;
			}
		}

		return $isValid;
	}

	public function applyActions($instance)
	{
		foreach ($this->actions as $action)
		{
			$action->apply($instance);
		}
	}

	public function initConstraints($dbArray)
	{
		$this->params = $dbArray;
	}

	protected function compareValues($actualValue, $constraintValue)
	{
		$compType = $this->params['comparisonType'];

		if ((($actualValue < $constraintValue) && (DiscountCondition::COMPARE_LTEQ == $compType)) ||
			(($actualValue > $constraintValue) && (DiscountCondition::COMPARE_GTEQ == $compType)) ||
			(($actualValue == $constraintValue) && (DiscountCondition::COMPARE_EQ == $compType)) ||
			(($actualValue != $constraintValue) && (DiscountCondition::COMPARE_NE == $compType)) ||
			(!($actualValue % $constraintValue) && (DiscountCondition::COMPARE_DIV == $compType)) ||
			(($actualValue % $constraintValue) && (DiscountCondition::COMPARE_NDIV == $compType)))
		{
			return true;
		}
	}

	public static function createFromArray($array)
	{
		if (empty($array['conditionClass']))
		{
			$array['conditionClass'] = 'RuleConditionRoot';
		}

		$inst = new $array['conditionClass'];

		if (!empty($array['records']))
		{
			$inst->registerRecords($array['records']);
		}

		if (!empty($array['actions']))
		{
			foreach ($array['actions'] as $action)
			{
				$inst->addAction(RuleAction::createFromArray($action));
			}

			$inst->registerRecords($array['records']);
		}

		if (!empty($array['conditions']))
		{
			foreach ($array['conditions'] as $sub)
			{
				$inst->addSubCondition(self::createFromArray($sub));
			}
		}

		unset($array['conditions'], $array['records'], $array['actions']);
		$inst->initConstraints($array);

		return $inst;
	}

	protected function registerRecords(array $records)
	{
		$this->records = $record;
	}

	protected function addSubCondition(RuleCondition $condition)
	{
		$this->conditions[] = $condition;
	}

	protected function addAction(RuleAction $action)
	{
		$this->actions[] = $action;
	}
}

?>