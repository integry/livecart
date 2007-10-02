<?php

ClassLoader::import("library.activerecord.ActiveRecord");
ClassLoader::import("application.model.*");

ActiveRecord::$creolePath = ClassLoader::getRealPath("library");

/**
 * Base class for all ActiveRecord based models of application (single entry point in
 * application specific model class hierarchy)
 *
 * @package application.model
 * @author Integry Systems <http://integry.com>    
 */
abstract class ActiveRecordModel extends ActiveRecord
{	
 	private static $application;
 	
 	private static $plugins = array();

	public static function setApplicationInstance(LiveCart $application)
	{
		self::$application = $application;
	}

	/**
	 * @return LiveCart
	 */
	public function getApplication()
	{
		return self::$application;
	}
	
	/**
	 *  Note that the form may not always contain all the fields of the model, so we must always
	 *  make sure that the data for the particular field has actually been submitted to avoid
	 *  setting empty values for fields that weren't included in the form
	 */
    public function loadRequestData(Request $request)
	{
		$schema = ActiveRecordModel::getSchemaInstance(get_class($this));
		foreach ($schema->getFieldList() as $field)
		{
			if (!($field instanceof ARForeignKey || $field instanceof ARPrimaryKey))
			{
				$name = $field->getName();
				if ($request->isValueSet($name) || 
                   ($request->isValueSet('checkbox_' . $name) && ('ARBool' == get_class($field->getDataType())))
                    )
				{
					switch (get_class($field->getDataType()))
					{
						case 'ARArray':
							$this->setValueArrayByLang(array($name), self::getApplication()->getDefaultLanguageCode(), self::getApplication()->getLanguageArray(LiveCart::INCLUDE_DEFAULT), $request);
						break;
								
						case 'ARBool':
                            $this->setFieldValue($name, in_array($request->get($name), array('on', 1)));
						break;
							
						default:
							$this->setFieldValue($name, $request->get($name));	
						break;	
					}
				}
			}
		}	
	}
	
	public function save($force = null)
	{
		$res = parent::save($force);
		$this->executePlugins($this, 'save');
		return $res;
	}
	
	protected static function transformArray($array, ARSchema $schema)
	{
		foreach ($schema->getFieldsByType('ARDateTime') as $name => $field)
		{
			$time = strtotime($array[$name]);
			
			if (!$time)
			{
				continue;
			}
			
			if (!isset($locale))
			{
				$locale = self::getApplication()->getLocale();
			}
			
			$array['formatted_' . $name] = $locale->getFormattedTime($time);
		}	
		
		return parent::transformArray($array, $schema);
	}
	
	private function executePlugins(&$object, $action, $className = null)
	{
		// in case the even is array transformation, the classname will be passed in as a separate variable
		if (!$className)
		{
			$className = get_class($object);
		}
				
		// get plugins
		$path = 'plugin.model.' . $className . '.' . $action;
		if (!isset(self::$plugins[$className][$action]))
		{
			self::$plugins[$className][$action] = array();
			$dir = ClassLoader::getRealPath($path);
			if (!is_dir($dir))
			{
				return false;
			}
			
			foreach (new DirectoryIterator($dir) as $plugin)
			{
				if ($plugin->isFile())
				{
					self::$plugins[$className][$action][] = basename($plugin->getFileName(), '.php');
				}
			}
		}
		
		if (isset(self::$plugins[$className][$action]) && !self::$plugins[$className][$action])
		{
			return false;
		}
		
		foreach (self::$plugins[$className][$action] as $plugin)
		{
			ClassLoader::import($path . '.' . $plugin);
			new $plugin($object, self::$application);
		}
	}
}

?>