<?php

/**
 *  Cron (scheduled) tasks manager
 *
 *  @package application/model/system
 *  @author Integry Systems
 */
class Cron
{
	const CRON_INTERVAL = 60;

	private $application;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
	}

	public function isExecutable()
	{
		if (!file_exists($this->getCronFile()))
		{
			$this->setLastExecTime(0);
		}

		return time() - self::CRON_INTERVAL > (include $this->getCronFile());
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

		$this->setLastExecTime(time());
	}

	private function processBatch($plugins, $interval = null)
	{
		if ($plugins && !class_exists('CronPlugin', false))
		{
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

	private function setLastExecTime($time)
	{
		file_put_contents($this->getCronFile(), '<?php return ' . $time . '; ?>');
	}

	private function getCronFile()
	{
		return $this->config->getPath('cache/cronExecTime') . '.php';
	}
}

?>