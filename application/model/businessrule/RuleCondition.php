<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule
 */
abstract class RuleCondition
{
	protected $records = array();
	protected $conditions = array();
	protected $actions = array();
	protected $params = array();

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

	public function getController()
	{
		return $this->controller;
	}

	public function getContext()
	{
		return $this->controller->getContext();
	}

	protected function getOrder()
	{
		return $this->getContext()->getOrder();
	}

	public function getConditions()
	{
		return $this->conditions;
	}

	public function getActions()
	{
		$actions = array();

		foreach ($this->actions as $action)
		{
			if ($action->getParam('isEnabled'))
			{
				$action->setParentCondition($this);
				$actions[] = $action;
			}
		}

		return $actions;
	}

	public function getParam($key, $defaultValue = null)
	{
		return isset($this->params[$key]) ? $this->params[$key] : $defaultValue;
	}

	/**
	 *  Evaluate condition tree
	 */
	public function isValid($instance = null)
	{
		if (($this instanceof RuleOrderCondition) && !$this->getOrder())
		{
			return false;
		}

		if (!$this->isApplicable($instance) xor $this->getParam('isReverse'))
		{
			return false;
		}

		if (!$this->conditions)
		{
			return true;
		}
		else
		{
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
		}

		return $isValid xor $this->getParam('isReverse');
	}

	/**
	 * Used for action conditions only
	 */
	public function isProductMatching($product)
	{
		$isMatching = $this->isProductApplicable($product);

		if ($this->hasSubConditions())
		{
			foreach ($this->conditions as $subCondition)
			{
				$isMatching = $subCondition->isProductMatching($product);
				if (!$isMatching && $this->getParam('isAllSubconditions'))
				{
					return false;
				}
			}
		}

		return $isMatching xor $this->getParam('isReverse');
	}

	protected function isProductApplicable()
	{
		return true;
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

		if ((($actualValue <= $constraintValue) && (DiscountCondition::COMPARE_LTEQ == $compType)) ||
			(($actualValue >= $constraintValue) && (DiscountCondition::COMPARE_GTEQ == $compType)) ||
			(($actualValue == $constraintValue) && (DiscountCondition::COMPARE_EQ == $compType)) ||
			(($actualValue != $constraintValue) && (DiscountCondition::COMPARE_NE == $compType)) ||
			($actualValue && !($actualValue % $constraintValue) && (DiscountCondition::COMPARE_DIV == $compType)) ||
			($actualValue && ($actualValue % $constraintValue) && (DiscountCondition::COMPARE_NDIV == $compType)))
		{
			return true;
		}
	}

	public static function createFromArray(&$array, $skipActions = false)
	{
		if (empty($array['conditionClass']))
		{
			$array['conditionClass'] = 'RuleConditionRoot';
		}

		ActiveRecordModel::getApplication()->loadPluginClass('application/model/businessrule/condition', $array['conditionClass']);
		$inst = new $array['conditionClass'];
		$array['instance'] = $inst;

		if (!empty($array['records']))
		{
			$inst->registerRecords($array['records']);
		}

		if (!empty($array['actions']) && !$skipActions)
		{
			foreach ($array['actions'] as $action)
			{
				$inst->addAction(RuleAction::createFromArray($action));
			}
		}

		if (!empty($array['sub']))
		{
			foreach ($array['sub'] as $sub)
			{
				$inst->addSubCondition(self::createFromArray($sub));
			}
		}

		foreach (array('validFrom', 'validTo') as $key)
		{
			if (isset($array[$key]) && (substr($array[$key], 0, 4) == '0000'))
			{
				unset($array[$key]);
			}
		}

		unset($array['sub'], $array['records'], $array['actions']);
		$inst->initConstraints($array);

		return $inst;
	}

	public static function create(DiscountCondition $condition)
	{
		return self::createFromArray($condition->toArray());
	}

	protected function registerRecords(array $records)
	{
		$fields = array_flip(array('ID', 'lft', 'rgt'));
		foreach ($records as $record)
		{
			unset($record['ID'], $record['Condition'], $record['__class__'], $record['conditionID'], $record['categoryID']);

			$record = array_filter($record);
			if (count($record) > 1)
			{
				$class = substr(ucfirst(array_shift(array_keys($record))), 0, -2);
				$data = array('ID' => reset($record));
			}
			else
			{
				$class = array_shift(array_keys($record));
				$data = array_intersect_key(array_shift($record), $fields);
			}

			$data['class'] = $class;

			$this->records[] = $data;
		}
	}

	protected function addSubCondition(RuleCondition $condition)
	{
		$this->conditions[] = $condition;
	}

	protected function addAction(RuleAction $action)
	{
		$this->actions[] = $action;
	}

	private function hasSubConditions()
	{
		return count($this->conditions) > 0;
	}

	public static function getSortOrder()
	{
		return 999;
	}

	public function getFields()
	{
		return array();
	}
}

?>