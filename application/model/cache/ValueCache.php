<?php

/**
 *
 * @package application.model.cache
 * @author Integry Systems <http://integry.com>
 */
abstract class ValueCache
{
	protected $application;

	public function __construct(LiveCart $application)
	{
		$this->application = $application;
	}

	public abstract function getName();
	public abstract function set($key, $value, $expiration = null, $namespace = null);
	public abstract function get($key, $defaultValue = null, $namespace = null);
	public abstract function getNamespace($namespace);
	public abstract function clear($key, $namespace = null);
	public abstract function clearNamespace($namespace);
	public abstract function gc();
	public abstract function isValid();

	public static function getCacheMethods(LiveCart $application)
	{
		$ret = array();

		$tranlationHandler = $application->getLocale()->translationManager();

		foreach (new DirectoryIterator(dirname(__file__) . '/') as $method)
		{
			if (substr($method->getFileName(), 0, 1) != '.')
			{
				$class = substr($method->getFileName(), 0, -4);

				if ($class != __CLASS__)
				{
					include_once $method->getFileName();
					$tranlationHandler->setDefinition($class, call_user_func(array($class, 'getName')));
					$ret[] = $class;
				}
			}
		}

		return $ret;
	}
}

?>