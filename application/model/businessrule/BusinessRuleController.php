<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.user.User');

/**
 * Determines which rules and actions are applicable
 *
 * @author Integry Systems
 * @package application.model.businessrule
 */
class BusinessRuleController
{
	private $context;

	private $conditions;

	public function __construct(BusinessRuleContext $context)
	{
		$this->context = $context;
	}

	public function apply($instance)
	{
		foreach ($this->getConditions() as $condition)
		{
			if ($condition->isApplicable($instance))
			{
				$condition->applyActions($instance);
			}
		}
	}

	public function getConditions()
	{
		if (is_null($this->conditions))
		{
			$file = $this->getRuleFile();

			if (!file_exists($file))
			{
				$this->updateRuleCache();
			}

			$this->conditions = include $file;

			foreach ($this->conditions as $condition)
			{
				$condition->setController($this);
			}
		}

		return $this->conditions;
	}

	public static function clearCache()
	{
		@unlink(self::getRuleFile());
	}

	private function updateRuleCache()
	{
		$conditions = ActiveRecord::getRecordSetArray('DiscountCondition', select());

		$idMap = array();
		foreach ($conditions as &$condition)
		{
			$idMap[$condition['ID']] =& $condition;
		}

		// get condition records
		foreach (ActiveRecord::getRecordSetArray('DiscountConditionRecord', select() as $record)
		{
			$idMap[$record['conditionID']]['records'][] = $record;
		}

		// get actions
		foreach (ActiveRecord::getRecordSetArray('DiscountAction', select() as $action)
		{
			if (!empty($action['actionConditionID']))
			{
				$action['condition'] =& $idMap[$action['actionConditionID']]
			}

			$idMap[$action['conditionID']]['actions'][] = $action;
		}

		foreach ($conditions as &$condition)
		{
			if (!$condition['parentNodeID'] || !isset($idMap[$condition['parentNodeID']]))
			{
				continue;
			}

			$idMap[$condition['parentNodeID']]['sub'][] =& $condition;
		}

		$rootCond = RuleCondition::createFromArray($idMap[DiscountCondition::ROOT_ID]);

		file_put_contents(self::getRuleFile(), '<?php return ' . var_export($rootCond->getConditions(), true) . '; ?>');
	}

	private function getRuleFile()
	{
		return ClassLoader::getRealPath('cache.') . 'businessrules.php';
	}
}

?>