<?php


/**
 *
 *
 * @package application.model.cache
 * @author Integry Systems <http://integry.com>
 */
class FileCache extends ValueCache
{
	private $root;

	public function getName()
	{
		return 'Files';
	}

	protected function storeValue($key, $value, $expiration = 0, $namespace = null)
	{
		// check if relative time offset is passed for expiration
		if (($expiration > 0) && ($expiration < time()))
		{
			$expiration += time();
		}

		$file = $this->getCacheFile($key, $namespace);
		if (!file_exists(dirname($file)))
		{
			mkdir(dirname($file), 0777, true);
		}

		file_put_contents($file, '<?php return ' . var_export(array(serialize($value), $expiration, $key), true) . '; ?>');
		touch($file, $expiration);
	}

	protected function retrieveValue($key, $defaultValue = null, $namespace = null)
	{
		$value = array_shift($this->getValueFromFile($this->getCacheFile($key, $namespace), $namespace, $defaultValue));
		if (!$value)
		{
			$value = $defaultValue;
		}

		return $value;
	}

	public function getNamespace($namespace)
	{
		$values = array();
		foreach(glob($this->getNamespaceDir($namespace) . '*') as $file)
		{
			list($value, $key) = $this->getValueFromFile($file, $namespace);
			$values[$key] = $value;
		}

		return $values;
	}

	private function getValueFromFile($file, $namespace, $defaultValue = null)
	{
		if (!file_exists($file))
		{
			return array();
		}

		list($value, $expiration, $key) = include $file;
		if ($expiration && ($expiration < time()))
		{
			$this->clear($key, $namespace);
			$value = $defaultValue;
		}
		else
		{
			$value = unserialize($value);
		}

		return array($value, $key);
	}

	public function clear($key, $namespace = null)
	{
		$file = $this->getCacheFile($key, $namespace);
		if (file_exists($file))
		{
			unlink($file);
		}
	}

	public function clearNamespace($namespace)
	{
		$this->application->rmdir_recurse($this->getNamespaceDir($namespace));
	}

	/* do nothing for now */
	public function gc()
	{

	}

	/* always valid */
	public function isValid()
	{
		return true;
	}

	private function getCacheFile($key, $namespace = null)
	{
		$path = $namespace ? $this->getNamespaceDir($namespace) : $this->getCacheRoot();
		return $path . md5($key) . '.php';
	}

	private function getNamespaceDir($namespace)
	{
		return $this->getCacheRoot() . $namespace . '/';
	}

	private function getCacheRoot()
	{
		if (!$this->root)
		{
			$this->root = ClassLoader::getRealPath('cache.value.');
		}

		return $this->root;
	}
}

?>
