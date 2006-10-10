<?php

ClassLoader::import("application.model.ActiveRecordModel");

/**
 * Site configuration model.
 *
 * @package application.user.model
 * @author Denis Slaveckij <denis@integry.net>
 *
 */
class Configuration
{

	private static $instance = null;
	private $configData = array();

	public function __construct()
	{
		try
		{
			$config = ActiveRecord::getInstanceById("SiteConfig", 1, true);
			$this->configData = unserialize($config->configData->get());

			if (count($this->configData) == 0)
			{
				$this->updateFromFile();
			}
		}
		catch(ARNotFoundException $e)
		{
			$this->updateFromFile();
		}
	}

	/**
	 * Returns singleton of Configuration.
	 * @return Configuration
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new Configuration();
		}
		return self::$instance;
	}

	/**
	 * Adds to configuration new configuration key/value pairs and saves them
	 */
	public function updateFromFile()
	{
		$file = ClassLoader::getBaseDir().'application\configuration\config_values\config.xml';
		$struct = simplexml_load_file($file);

		$data = Configuration::getDataFromFile();

		foreach($data as $value)
		{
			if (!array_key_exists($value['key'], $this->configData))
			{
				$config[$value['key']] = $value['value'];
			}
			else
			{
				$config[$value['key']] = $this->configData[$value['key']];
			}
		}

		$this->configData = $config;
		$this->save();
	}

	/**
	 * Saves configuration
	 */
	public function save()
	{
		ActiveRecord::deleteById("SiteConfig", 1);

		$config = ActiveRecord::getNewInstance("SiteConfig");
		$config->setId(1);
		$config->configData->set(addslashes(serialize($this->configData)));
		$config->save(ActiveRecord::PERFORM_INSERT);
	}

	/**
	 * Gets array of site configuration.
	 * @return array
	 */
	public function getData()
	{
		return $this->configData;
	}

	/**
	 * Gets value of site configurations.
	 * @param string $name
	 * @return string
	 */
	public function getValue($key)
	{
		return $this->configData[$key];
	}

	/**
	 * Sets value of site configurations.
	 * @param string $key Key of value
	 * @param mixed $value Value
	 */
	public function setValue($key, $value)
	{
		$this->configData[$key] = $value;
	}

	/**
	 * Gets sorted by category array of configuration also with category. Used for displaying configuration in form of Configuration controller.
	 * @return $array
	 */
	public static function getDataFromFile()
	{
		$file = ClassLoader::getBaseDir().'application\configuration\config_values\config.xml';
		$struct = simplexml_load_file($file);

		$array = array();
		$i = 0;
		foreach($struct as $value)
		{
			$array[$i]['category'] = (string)$value->Category;
			$array[$i]['key'] = (string)$value->Key;
			$array[$i]['value'] = (string)$value->Value;
			$array[$i]['type'] = (string)$value->Type;

			if ((string)$value->Type == 'list')
			{
				foreach($value->Items->Item as $value2)
				{

					$array[$i]['items'][] = (string)$value2;
				}
			}

			$i++;
		}

		usort($array, 'CompareConfig');
		return $array;
	}
}

function CompareConfig($a, $b)
{

	if ($a['category'] == $b['category'])
	{
		return 0;
	}

	return ($a['category'] < $b['category']) ?  - 1: 1;
}

?>
