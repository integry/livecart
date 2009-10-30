<?php

ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.discount.DiscountCondition');
ClassLoader::import('application.model.discount.DiscountAction');
ClassLoader::import('application.model.businessrule.RuleCondition');
ClassLoader::import('application.model.businessrule.condition.*');
ClassLoader::import('application.model.businessrule.action.*');
ClassLoader::import('application.model.businessrule.interface.*');

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

	private $disableDisplayDiscounts = false;

	public function __construct(BusinessRuleContext $context)
	{
		$this->context = $context;
	}

	public function apply($instance)
	{
		foreach ($this->getConditions() as $condition)
		{
			if ($condition->isValid($instance))
			{
				foreach ($condition->getActions() as $action)
				{
					if ($action->isItemAction())
					{
						if ($action->isItemApplicable($instance))
						{
							$action->applyToItem($instance);
						}
					}
				}
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

			$this->conditions = unserialize(include $file);

			foreach ($this->conditions as $condition)
			{
				$condition->setController($this);
			}
		}

		return $this->conditions;
	}

	public function getValidConditions()
	{
		$valid = array();
		foreach ($this->getConditions() as $condition)
		{
			if ($condition->isValid())
			{
				$valid[] = $condition;

				if ($condition->getParam('isFinal'))
				{
					break;
				}
			}
		}

		return $valid;
	}

	public function getActions()
	{
		$actions = array();
		foreach ($this->getValidConditions() as $condition)
		{
			$actions = array_merge($actions, $condition->getActions());
		}

		return $actions;
	}

	public function getProductPrice($product, $basePrice)
	{
		if ($this->disableDisplayDiscounts)
		{
			return $basePrice;
		}

		$item = $this->getContext()->addProduct($product);
		$item->setItemPrice($basePrice);
		$this->apply($item);
		$this->getContext()->removeLastProduct();

		return $item->getPriceWithoutTax();
	}

	/**
	 *  Do not include discounts in product display prices (for example, in backend)
	 */
	public function disableDisplayDiscounts()
	{
		$this->disableDisplayDiscounts = true;
	}

	public function getContext()
	{
		return $this->context;
	}

	public static function clearCache()
	{
		@unlink(self::getRuleFile());
	}

	private function updateRuleCache()
	{
		$f = select(eq('DiscountCondition.isEnabled', true));
		$f->setOrder(f('DiscountCondition.position'));
		$conditions = ActiveRecord::getRecordSetArray('DiscountCondition', $f);

		$idMap = array();
		foreach ($conditions as &$condition)
		{
			$idMap[$condition['ID']] =& $condition;
		}

		// get condition records
		foreach (ActiveRecord::getRecordSetArray('DiscountConditionRecord', select(), array('Category')) as $record)
		{
			$idMap[$record['conditionID']]['records'][] = $record;
		}

		// get actions
		$f = select();
		$f->setOrder(f('DiscountAction.position'));
		foreach (ActiveRecord::getRecordSetArray('DiscountAction', $f) as $action)
		{
			if (!empty($action['actionConditionID']))
			{
				$action['condition'] =& $idMap[$action['actionConditionID']];
			}

			$idMap[$action['conditionID']]['actions'][] = $action;
		}

		foreach ($conditions as &$condition)
		{
			if (!$condition['parentNodeID'] || !isset($idMap[$condition['parentNodeID']]) || !empty($condition['isActionCondition']))
			{
				continue;
			}

			$idMap[$condition['parentNodeID']]['sub'][] =& $condition;
		}

		$rootCond = RuleCondition::createFromArray($idMap[DiscountCondition::ROOT_ID]);
		file_put_contents(self::getRuleFile(), '<?php return "' . addslashes(serialize($rootCond->getConditions())) . '"; ?>');
	}

	private function getRuleFile()
	{
		return ClassLoader::getRealPath('cache.') . 'businessrules.php';
	}
}

?>