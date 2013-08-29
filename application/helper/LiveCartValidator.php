<?php


/**
 *
 * @package application/helper
 * @author Integry Systems
 */
class LiveCartValidator extends RequestValidator
{
	protected $application;
	protected $isPluginProcessed;

	public function setApplication(LiveCart $application)
	{
		$this->application = $application;
	}

	public function isValid()
	{
		$this->processPlugins();
		return parent::isValid();
	}

	public function getJSValidatorParams($requestVarName = null)
	{
		$this->processPlugins();
		return parent::getJSValidatorParams($requestVarName);
	}

	protected function processPlugins()
	{
		if (!$this->isPluginProcessed)
		{
			foreach ($this->application->getPlugins('validator/' . $this->getName()) as $plugin)
			{
				if (!class_exists('ValidatorPlugin', false))
				{
									}

				include_once $plugin['path'];
				$inst = new $plugin['class']($this, $this->application);
				$inst->process();
			}
		}

		$this->isPluginProcessed = true;
	}
}

?>