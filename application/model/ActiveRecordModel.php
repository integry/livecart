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

 	private static $eavQueue = array();

 	private $specificationInstance;

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
	public function loadRequestData(Request $request, $prefix = '')
	{
		$enabledFields = is_array($prefix) ? array_flip($prefix) : null;

		$schema = ActiveRecordModel::getSchemaInstance(get_class($this));
		foreach ($schema->getFieldList() as $field)
		{
			if (!($field instanceof ARForeignKey || $field instanceof ARPrimaryKey))
			{
				$name = $field->getName();
				$reqName = $prefix . $name;

				if (is_array($enabledFields) && !isset($enabledFields[$name]))
				{
					continue;
				}

				if ($request->isValueSet($reqName) ||
				   ($request->isValueSet('checkbox_' . $reqName) && ('ARBool' == get_class($field->getDataType())))
					)
				{
					switch (get_class($field->getDataType()))
					{
						case 'ARArray':
							$this->setValueArrayByLang(array($name), self::getApplication()->getDefaultLanguageCode(), self::getApplication()->getLanguageArray(LiveCart::INCLUDE_DEFAULT), $request);
						break;

						case 'ARBool':
							$this->setFieldValue($name, in_array($request->get($reqName), array('on', 1)));
						break;

						case 'ARInteger':
						case 'ARFloat':
							if (is_numeric($request->get($reqName)))
							{
								$this->setFieldValue($name, $request->get($reqName));
							}
						break;

						default:
							$this->setFieldValue($name, $request->get($reqName));
						break;
					}
				}
			}
		}

		if ($this instanceof EavAble)
		{
			$this->getSpecification()->loadRequestData($request);
		}
	}

	public function getSpecification()
	{
		if (!$this->specificationInstance)
		{
			$this->loadSpecification();
		}

		return $this->specificationInstance;
	}

	public function loadSpecification($specificationData = null)
	{
	  	if ($this->specificationInstance)
	  	{
	  		return false;
		}

		if (!$this instanceof EavAble && !$this instanceof EavObject)
		{
			throw new ApplicationException(get_class($this) . ' does not support EAV');
		}

		ClassLoader::import("application.model.eav.EavSpecificationManager");

		$obj = $this instanceof EavObject ? $this : EavObject::getInstance($this);

		$this->specificationInstance = new EavSpecificationManager($obj, $specificationData);
	}

	protected function setLastPosition($parentField = null, ARValueMapper $parent = null)
	{
		if ($parentField && !$parent)
		{
			$parent = $this->$parentField;
		}

		$parentField .= 'ID';

		// get max position
	  	$cond = $parent ? new EqualsCond(new ARFieldHandle(get_class($this), $parentField), $parent->get()->getID()) : null;

		$f = new ARSelectFilter($cond);
		$f->setOrder(new ARFieldHandle(get_class($this), 'position'), 'DESC');
		$f->setLimit(1);
		$rec = ActiveRecord::getRecordSetArray(get_class($this), $f);
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 1;

		// default new language state
		$this->position->set($position);
	}

	protected function insert()
	{
		$res = parent::insert();
		$this->executePlugins($this, 'insert');
		$this->executePlugins($this, 'save');
		return $res;
	}

	protected function update()
	{
		$this->executePlugins($this, 'update');
		$res = parent::update();
		$this->executePlugins($this, 'save');
		return $res;
	}

	public function save()
	{
		$res = parent::save();

		if ($this instanceof EavAble && $this->specificationInstance)
		{
			$this->specificationInstance->save();
		}

		return $res;
	}

	public function updateTimeStamp()
	{
		$args = func_get_args();
		$update = new ARUpdateFilter();

		foreach ($args as $field)
		{
			$update->addModifier($field, new ARExpressionHandle('NOW()'));
		}

		return $this->updateRecord($update);
	}

	protected static function transformArray($array, ARSchema $schema)
	{
		foreach ($schema->getFieldsByType('ARDateTime') as $name => $field)
		{
			if (isset($array[$name]))
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
		}

		$data = parent::transformArray($array, $schema);

		self::executePlugins($data, 'array', $schema->getName());

		return $data;
	}

	public function toArray($force = false)
	{
		$array = parent::toArray($force);

		self::executePlugins($array, 'array', get_class($this));

		if ($this->specificationInstance && !isset($array['attributes']))
		{
			$array['attributes'] = $this->specificationInstance->toArray();
		}

		return $array;
	}

	public static function addToEavQueue($className, &$record)
	{
		self::$eavQueue[$className][] =& $record;
	}

	public static function addArrayToEavQueue($className, &$array)
	{
		foreach ($array as &$element)
		{
			self::addToEavQueue($className, $element);
		}
	}

	public static function loadEav()
	{
		if (!self::$eavQueue)
		{
			return false;
		}

		ClassLoader::import('application.model.eav.EavObject');
		ClassLoader::import('application.model.eav.EavSpecificationManager');

		$map = array();

		// build query for fetching EavObject gateway objects for all queued records
		foreach (self::$eavQueue as $class => &$objects)
		{
			$ids = array();
			foreach ($objects as &$object)
			{
				$ids[] = $object['ID'];

				if (!isset($map[$class][$object['ID']]))
				{
					$map[$class][$object['ID']] = null;
					$ref =& $map[$class][$object['ID']];
					$ref['attributes'] = array();
					$ref['byHandle'] = array();
				}

				$ref =& $map[$class][$object['ID']];

				$object['attributes'] =& $ref['attributes'];
				$object['byHandle'] =& $ref['byHandle'];
			}

			$c = new INCond(new ARFieldHandle('EavObject', EavObject::getClassField($class)), $ids);
			if (!isset($cond))
			{
				$cond = $c;
			}
			else
			{
				$cond->addOr($c);
			}
		}

		// fetch EAV values
		$eavObjects = ActiveRecordModel::getRecordSetArray('EavObject', new ARSelectFilter($cond));
		EavSpecificationManager::loadSpecificationForRecordSetArray($eavObjects, true);

		// assign attribute values to the respective records
		foreach ($eavObjects as $entry)
		{
			unset($entry['ID']);
			foreach ($entry as $field => $refId)
			{
				if ($refId)
				{
					$class = ucfirst(substr($field, 0, -2));
					if (isset($entry['attributes']))
					{
						$map[$class][$refId]['attributes'] = $entry['attributes'];
					}
					break;
				}
			}
		}

		self::$eavQueue = array();
	}

	private function executePlugins(&$object, $action, $className = null)
	{
		// in case the event is array transformation, the classname will be passed in as a separate variable
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
				if ($plugin->isFile() && ('php' == pathinfo($plugin->getFileName(), PATHINFO_EXTENSION)))
				{
					self::$plugins[$className][$action][] = basename($plugin->getFileName(), '.php');
				}
			}
		}

		if (isset(self::$plugins[$className][$action]) && !self::$plugins[$className][$action])
		{
			return false;
		}

		if (!class_exists('ModelPlugin'))
		{
			ClassLoader::import('application.model.ModelPlugin');
		}

		foreach (self::$plugins[$className][$action] as $plugin)
		{
			ClassLoader::import($path . '.' . $plugin);
			new $plugin($object, self::$application);
		}
	}
}

?>
