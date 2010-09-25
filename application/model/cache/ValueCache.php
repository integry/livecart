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

	public function set($key, $value, $expiration = null)
	{
		return $this->storeValue($this->getKeyName($key), $value, $expiration, $this->getKeyNameSpace($key));
	}

	public function get($key, $defaultValue = null)
	{
		return $this->retrieveValue($this->getKeyName($key), $defaultValue, $this->getKeyNameSpace($key));
	}

	public abstract function getName();
	protected abstract function storeValue($key, $value, $expiration = null, $namespace = null);
	protected abstract function retrieveValue($key, $defaultValue = null, $namespace = null);
	public abstract function getNamespace($namespace);
	public abstract function clear($key, $namespace = null);
	public abstract function clearNamespace($namespace);
	public abstract function gc();
	public abstract function isValid();

	private function getKeyNameSpace($key)
	{
		if (is_array($key))
		{
			return array_shift($key);
		}
	}

	private function getKeyName($key)
	{
		return is_array($key) ? array_pop($key) : $key;
	}

	public static function getCacheMethods(LiveCart $application)
	{
		$ret = array();

		$tranlationHandler = $application->getLocale()->translationManager();

		foreach (new DirectoryIterator(dirname(__file__) . '/') as $method)
		{
			if (substr($method->getFileName(), 0, 1) != '.')
			{
				$class = substr($method->getFileName(), 0, -4);

				if (($class != __CLASS__) && (file_exists($method->getPathname())))
				{
					include_once $method->getPathname();
					$tranlationHandler->setDefinition($class, call_user_func(array($class, 'getName')));
					$ret[] = $class;
				}
			}
		}

		$ret = array_merge(array('FileCache'), array_diff($ret, array('FileCache')));

		return $ret;
	}
}

?>