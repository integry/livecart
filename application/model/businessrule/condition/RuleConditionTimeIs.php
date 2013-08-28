<?php

/**
 *
 * @author Integry Systems
 */
class RuleConditionTimeIs extends RuleCondition implements RuleOrderCondition
{
	public function isApplicable()
	{
		$params = $this->params['serializedCondition'];
		if (!$params)
		{
			$params = array();
		}

		$from = $this->fixTime($params['from'], '0:00');
		$to = $this->fixTime($params['to'], '24:00');

		$order = $this->getContext()->getOrder();
		$date = strtotime($order->getCompletionDate());

		if (!$date)
		{
			$date = time();
		}

		$clock = date('Hi', $date);

		// overnight
		if ($from > $to)
		{
			$to = (string)((int)$to + 2400);

			if ($clock < $from)
			{
				$clock = (string)((int)$clock + 2400);
			}
		}

		return ($clock >= $from) && ($clock <= $to);
	}

	private function fixTime($time, $defaut)
	{
		$time = preg_replace('[^0-9]', '', $time);

		if (!$time)
		{
			$time = $default;
		}

		$parts = explode(':', $time);
		$hour = array_shift($parts);
		$min = array_shift($parts);

		return $this->fixPart($hour) . $this->fixPart($min);
	}

	private function fixPart($hourOrMin)
	{
		if (!$hourOrMin)
		{
			$hourOrMin = '0';
		}

		if (strlen($hourOrMin) == 1)
		{
			$hourOrMin = '0'. $hourOrMin;
		}

		return substr($hourOrMin, 0, 2);
	}

	public function getFields()
	{
		return array(array('type' => 'text', 'label' => '_time_from', 'name' => 'from'),
					 array('type' => 'text', 'label' => '_time_to', 'name' => 'to'),
				);
	}
}

?>