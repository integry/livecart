<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule
 */
abstract class RuleAction
{
	protected $condition;
	protected $parentCondition;
	protected $params = array();

	public function setParams($dbArray)
	{
		$this->params = $dbArray;
	}

	public function getParam($key, $defaultValue = null)
	{
		return isset($this->params[$key]) ? $this->params[$key] : $defaultValue;
	}

	public function getFieldValue($key)
	{
		if (isset($this->params['serializedData'][$key]))
		{
			return $this->params['serializedData'][$key];
		}
	}

	public static function createFromArray(array $array)
	{
		ActiveRecordModel::getApplication()->loadPluginClass('application/model/businessrule/action', $array['actionClass']);
		$inst = new $array['actionClass'];
		if (!empty($array['condition']))
		{
			$condition = isset($array['instance']) ? $array['instance'] : RuleCondition::createFromArray($array['condition'], true);
			$inst->setCondition($condition);
		}

		unset($array['condition']);

		$inst->setParams($array);

		return $inst;
	}

	protected function setCondition(RuleCondition $condition)
	{
		$this->condition = $condition;
	}

	public function setParentCondition(RuleCondition $condition)
	{
		$this->parentCondition = $condition;
		if ($this->condition)
		{
			$this->condition->setController($this->parentCondition->getController());
		}
	}

	public function isItemAction()
	{
		//return $this instanceof RuleItemAction;
		return (DiscountAction::TYPE_ITEM_DISCOUNT == $this->getParam('type')) && ($this instanceof RuleItemAction);
	}

	public function isOrderAction()
	{
		//return $this instanceof RuleOrderAction && !$this->isItemAction();
		return (DiscountAction::TYPE_ORDER_DISCOUNT == $this->getParam('type')) && ($this instanceof RuleOrderAction);
	}

	public function isItemApplicable(BusinessRuleProductInterface $item)
	{
		if (!$this->condition)
		{
			return true;
		}

		return $this->condition->isProductMatching($item->getProduct());
	}

	public static function getSortorderBy()
	{
		return 999;
	}

	public function getContext()
	{
		return $this->condition->getContext();
	}

	public function getFields()
	{
		return array();
	}
}

?>