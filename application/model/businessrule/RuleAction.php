<?php

ClassLoader::import('application.model.businessrule.RuleOrderAction');
ClassLoader::import('application.model.businessrule.RuleItemAction');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule
 */
abstract class RuleAction
{
	protected $condition;

	public function initConstraints($dbArray)
	{

	}

	public static function createFromArray(array $array)
	{
		$inst = new $array['actionClass'];
		if (!empty($array['condition']))
		{
			$inst->setCondition(RuleCondition::createFromArray($array['condition']));
		}

		return $inst;
	}

	protected function setCondition(RuleCondition $condition)
	{
		$this->condition = $condition;
	}

	public function isItemDiscount()
	{
		return $this instanceof RuleItemAction;
	}
}

?>