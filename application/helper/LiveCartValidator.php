<?php

/**
 *
 * @package application/helper
 * @author Integry Systems
 */
class LiveCartValidator extends \Phalcon\Validation
{
	protected $isPluginProcessed;

	public function getValidators($field)
	{
		$validators = array();
		foreach ($this->_validators as $validator)
		{
			if ($validator[0] == $field)
			{
				$validators[] = $validator[1];
			}
		}

		return $validators;
	}

	public function hasValidator($field, $type = null)
	{
		$validators = $this->getValidators($field);
		if ($type)
		{
			$filtered = array();
			foreach ($validators as $val)
			{
				$parts = explode('\\', get_class($val));
				$class = array_pop($parts);
				if ($class == $type)
				{
					$filtered[] = $val;
				}
			}
			$validators = $filtered;
		}

		return $validators;
	}

	public function validate($data = array(), $entity = null)
	{
		//$this->processPlugins();
		return parent::validate($data, $entity);
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