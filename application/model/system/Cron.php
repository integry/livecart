<?php

/**
 *  Cron (scheduled) tasks manager
 *
 *  @package application.model.system
 *  @author Integry Systems
 */
class Cron
{
	private $application;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
	}

	public function process()
	{
		$standard = array('minute' => 60,
						  'hourly' => 3600,
						  'daily' => 3600 * 24,
						  'weekly' => 3600 * 24 * 7);

		foreach ($standard as $type => $interval)
		{
			$this->processBatch($this->application->getPlugins('cron/' . $type), $interval);
		}
	}

	private function processBatch($plugins, $interval = null)
	{
		if ($plugins && !class_exists('CronPlugin', false))
		{
			ClassLoader::import('application.CronPlugin');
		}

		foreach ($plugins as $plugin)
		{
			include_once($plugin['path']);
			$inst = new $plugin['class']($this->application, $plugin['path']);

			if ($inst->isExecutable($interval))
			{
				$res = $inst->process();
				$inst->markCompletedExecution();
			}
		}
	}
}

?>