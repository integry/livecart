<?php

ClassLoader::import('application.model.businessrule.condition.RuleConditionContainsProduct');
ClassLoader::import('application.model.businessrule.interface.RuleOrderCondition');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.condition
 */
class RuleConditionPastOrderContainsProduct extends RuleConditionContainsProduct
{
	protected function getOrders()
	{
		$pastOrders = $this->getContext()->getPastOrders();
		$pastOrders = $pastOrders ? $pastOrders['orders'] : array();

		$ser = $this->params['serializedCondition'];
		if (!empty($ser['time']))
		{
			$t = $ser['time'];
			$timeFrom = $timeTo = time();
			if (empty($t['conditionTime']) || ('before' == $t['conditionTime']))
			{
				$multi = array('min' => 60, 'hr' => 3600, 'day' => 3600 * 24, 'year' => 3600 * 24 * 365);
				foreach ($multi as $key => $seconds)
				{
					if (!empty($t[$key]))
					{
						$timeFrom -= $t[$key] * $seconds;
					}
				}
			}
			else
			{
				if (!empty($t['from']))
				{
					$timeFrom = strtotime($t['from']);
				}

				if (!empty($t['to']))
				{
					$timeTo = strtotime($t['to']);
				}
			}

			foreach ($pastOrders as $key => $order)
			{
				$completed = strtotime($order->getCompletionDate());
				if ($completed < $timeFrom || $completed > $timeTo)
				{
					unset($pastOrders[$key]);
				}
			}
		}

		return $pastOrders;
	}

	public static function getSortOrder()
	{
		return 20;
	}
}

?>