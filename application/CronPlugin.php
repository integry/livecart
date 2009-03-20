<?php

/**
 *  Cron (scheduled) task
 *
 *  @package application
 *  @author Integry Systems
 */
abstract class CronPlugin
{
	protected $application;
	protected $path;

	abstract public function process();

	public function __construct(LiveCart $application, $path)
	{
		$this->application = $application;
		$this->path = $path;
	}

	public function isExecutable($interval)
	{
		return time() - $this->getLastExecutionTime() >= $interval;
	}

	public function getLastExecutionTime()
	{
		$file = $this->getExecutionTimeFile();
		return !file_exists($file) ? null : include $file;
	}

	public function markCompletedExecution()
	{
		$file = $this->getExecutionTimeFile();
		$dir = dirname($file);
		if (!file_exists($dir))
		{
			mkdir($dir, octdec(777), true);
		}

		file_put_contents($file, '<?php return ' . time() . '; ?>');
	}

	private function getExecutionTimeFile()
	{
		return ClassLoader::getRealPath('storage.configuration.cron.') . md5($this->path) . '.php';
	}
}

?>