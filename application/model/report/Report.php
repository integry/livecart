<?php

/**
 * Generate reports and stats
 *
 * @package application.model.report
 * @author	Integry Systems
 */
abstract class Report
{
	protected $from;
	protected $to;
	protected $interval = 'day';
	protected $locale;
	protected $chartType = 0;
	protected $application;

	protected $values;

	const LINE = 0;
	const BAR = 1;

	protected abstract function getMainTable();

	protected abstract function getDateHandle();

	public function setFrom($from)
	{
		$this->from = $from;
	}

	public function setTo($to)
	{
		$this->to = $to;
	}

	public function setLocale(Locale $locale)
	{
		$this->locale = $locale;
	}

	public function setApplication(LiveCart $application)
	{
		$this->application = $application;
	}

	public function setInterval($interval)
	{
		$this->interval = $interval;
	}

	public function setChartType($type)
	{
		$this->chartType = $type;
	}

	protected function getChartInstance()
	{
		$types = array(self::LINE => 'line_dot', self::BAR => 'bar');
		return new $types[$this->chartType]();
	}

	protected function getData($countSql)
	{
		return $this->getReportData($this->getQuery($countSql));
	}

	protected function getReportData(ARSelectQueryBuilder $q)
	{
		$this->values = $this->getLineChartData(ActiveRecord::getDataByQuery($q));
	}

	protected function getQuery($countSql = null)
	{
		$q = new ARSelectQueryBuilder();
		$q->includeTable($this->getMainTable());

		$f = new ARSelectFilter();
		$q->setFilter($f);

		$this->setDateCondition($f, $this->getDateHandle());
		$this->prepareDateQuery($this->getDateHandle()->getField()->getName(), $this->interval, $q);

		if ($countSql)
		{
			$q->addField(new ARExpressionHandle($countSql), null, 'cnt');
		}

		return $q;
	}

	public function getChartDataString()
	{
		$values = $this->values;

		$chart = new open_flash_chart();

		$line = self::getChartInstance();

		$line->set_values($values['x']);

		if (self::LINE == $this->chartType)
		{
			$line->set_halo_size(2);
			$line->set_dot_size(2);
		}

		$chart->add_element($line);

		$x = new x_axis();
		$x_labels = new x_axis_labels();

		if ('hour' != $this->interval)
		{
			$x_labels->set_vertical();
		}

		$x_labels->set_labels($values['y']);

		if ('hour' != $this->interval)
		{
			$x_labels->set_steps(ceil(count($values['y']) / 13));
		}

		$line->set_tooltip('#x_label#: #val#');
		$x->set_labels( $x_labels );
		$chart->set_x_axis( $x );

		$y = new y_axis();
		$y->set_range($values['min'], $values['max'], $values['step']);
		$chart->add_y_axis( $y );

		return $chart->toPrettyString();
	}

	protected function getDateCondition(ARFieldHandle $handle)
	{
		$conds = array();

		if ($this->from)
		{
			$conds[] = new EqualsOrMoreCond($handle, $this->from);
		}

		if ($this->to)
		{
			$conds[] = new EqualsOrLessCond($handle, $this->to);
		}

		if ($conds)
		{
			return Condition::mergeFromArray($conds);
		}
	}

	protected function setDateCondition(ARSelectFilter $filter, ARFieldHandle $handle)
	{
		$cond = $this->getDateCondition($handle);
		if ($cond)
		{
			$filter->mergeCondition($cond);
		}
	}

	protected function prepareDateQuery($field, $range, ARSelectQueryBuilder $q)
	{
		if ('hour' == $range)
		{
			$this->addDateFieldToQuery($q, 'EXTRACT(HOUR FROM ' . $field .')', 'hour');
		}
		else if ('week' == $range)
		{
			$this->addDateFieldToQuery($q, 'WEEK(' . $field .')', 'week');
			$this->addDateFieldToQuery($q, 'EXTRACT(YEAR FROM ' . $field .')', 'year');
		}
		else
		{
			$intervals = array('day', 'month', 'year');
			while ($intervals)
			{
				reset($intervals);
				$i = end($intervals);

				if (in_array($range, $intervals))
				{
					$this->addDateFieldToQuery($q, 'EXTRACT(' . strtoupper($i) . ' FROM ' . $field .')', $i);
				}

				array_pop($intervals);
			}
		}
	}

	protected function addDateFieldToQuery(ARSelectQueryBuilder $q, $sqlFunction, $alias)
	{
		$f = $q->getFilter();
		$q->addField(new ARExpressionHandle($sqlFunction), null, $alias);
		$f->setGrouping(new ARExpressionHandle($alias));
		$f->setOrder(new ARExpressionHandle($alias));
	}

	protected function getLineChartData($array)
	{
		$array = $this->fillDataGaps($array);

		$min = $max = null;
		$x = $y = array();

		foreach ($array as $e)
		{
			$value = (float)$e['cnt'];
			$x[] = $value;

			if (isset($e['day']))
			{
				$key = $this->locale->getFormattedTime(mktime(0, 0, 0, $e['month'], $e['day'], $e['year']), 'date_short');
			}
			else if (isset($e['month']))
			{
				$key = $this->locale->getMonthName($e['month']) . ', ' . $e['year'];
			}
			else if (isset($e['hour']))
			{
				$key = (string)($e['hour'] < 10 ? '0' . $e['hour'] : $e['hour']);
			}
			else
			{
				$key = (string)$e[key($e)];
			}

			$y[] = $key;

			if (is_null($min) || ($min > $value))
			{
				$min = $value;
			}

			if (is_null($max) || ($max < $value))
			{
				$max = $value;
			}
		}

		$min -= max(1, floor($min * 0.05));
		$max += max(1, floor($max * 0.05));
		$step = ceil(($max - $min) / 10);

		return array('x' => $x, 'y' => $y, 'min' => max(0, $min), 'max' => max(0, $max), 'step' => $step);
	}

	protected function fillDataGaps($array)
	{
		if (!$array)
		{
			return $array;
		}

		$first = $array[0];
		$last = $array[count($array) - 1];

		if (isset($first['month']))
		{
			if (!isset($first['day']))
			{
				$first['day'] = null;
			}

			if (!isset($last['day']))
			{
				$last['day'] = null;
			}

			$firstStamp = mktime(0, 0, 0, $first['month'], $first['day'] ? $first['day'] : 1, $first['year']);
			$lastStamp = mktime(0, 0, 0, $last['month'], $last['day'] ? $last['day'] : date('t', mktime(0, 0, 0, $last['month'], 1, $last['year'])), $last['year']);

			for ($year = $first['year']; $year <= $last['year']; $year++)
			{
				for ($month = 1; $month <= 12; $month++)
				{
					for ($day = 1; $day <= 31; $day++)
					{
						if (!checkdate($month, $day, $year))
						{
							break;
						}

						$stamp = mktime(0, 0, 0, $month, $day, $year);
						if ($stamp < $firstStamp)
						{
							continue;
						}

						if ($stamp > $lastStamp)
						{
							$break = true;
							break;
						}

						if ($first['day'])
						{
							$res[$year . '-' . $month . '-' . $day] = array('year' => $year, 'month' => $month, 'day' => $day, 'cnt' => null);
						}
						else
						{
							$res[$year . '-' . $month] = array('year' => $year, 'month' => $month, 'cnt' => null);
							break;
						}
					}
				}
			}

			foreach ($array as $v)
			{
				$res[$v['year'] . '-' . $v['month'] . (isset($v['day']) ? '-' . $v['day'] : '')]['cnt'] = $v['cnt'];
			}
		}
		else
		{
			// yearly, hourly, weekly, etc summaries
			$key = key($first);
			for ($k = $first[$key]; $k <= $last[$key]; $k++)
			{
				$res[$k] = array($key => $k, 'cnt' => null);
			}

			foreach ($array as $v)
			{
				$res[$v[$key]]['cnt'] = $v['cnt'];
			}
		}

		return array_values($res);
	}

	protected function getCurrencyMultiplier()
	{
		$curr = array();
		foreach ($this->application->getCurrencySet() as $currency)
		{
			$curr[] = 'IF (currencyID = "' . $currency->getID() . '", ' . $currency->rate->get();
		}

		return implode(', ', $curr) . ', 1' . str_repeat(')', count($curr));
	}
}

?>