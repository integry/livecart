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

 	private static $eavQueue = array();

 	private static $isEav = array();

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
							$this->setFieldValue($name, in_array(strtolower($request->get($reqName)), array('on', 1, 'yes', 'true')));
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
			$this->getSpecification()->loadRequestData($request, $prefix);
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
		ClassLoader::import("application.model.eav.EavObject");

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
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 0;

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

	public function save($forceOperation = null)
	{
		if (($this instanceof EavAble) && $this->eavObject->get() && !$this->eavObject->get()->getID())
		{
			$eavObject = $this->eavObject->get();
			$this->eavObject->setNull();
		}

		$res = parent::save($forceOperation);

		if (isset($eavObject))
		{
			$eavObject->save();
			$this->eavObject->set($eavObject);
			$this->save();
		}

		if ($this instanceof EavAble && $this->specificationInstance)
		{
			$this->specificationInstance->save();
		}

		return $res;
	}

	protected static function transformArray($array, ARSchema $schema)
	{
		$schemaName = $schema->getName();

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

		if (self::isEav($schemaName))
		{
			self::addToEavQueue($schemaName, $data);
		}

		self::executePlugins($data, 'array', $schemaName);

		return $data;
	}

	public static function isEav($className)
	{
		if (!isset(self::$isEav[$className]))
		{
			self::$isEav[$className] = (array_search('EavAble', class_implements($className)) !== false);
		}

		return self::$isEav[$className];
	}

	public function toArray($force = false)
	{
		$array = parent::toArray($force);

		self::executePlugins($array, 'array', get_class($this));

		if ($this->specificationInstance && (!isset($array['attributes']) || $force))
		{
			$array['attributes'] = $this->specificationInstance->toArray();
			EavSpecificationManager::sortAttributesByHandle('EavSpecificationManager', $array);
		}

		return $array;
	}

	public static function addToEavQueue($className, &$record)
	{
		if (!$record['ID'] || (empty($record['eavObjectID']) && empty($record['EavObject']['ID'])) || isset($record['attributes']))
		{
			return false;
		}

		if (!isset(self::$eavQueue[$className][$record['ID']]))
		{
			$eavID = empty($record['eavObjectID']) ? $record['EavObject']['ID'] : $record['eavObjectID'];
			self::$eavQueue[$className][$record['ID']] = array('attributes' => array(), 'byHandle' => array(), 'eavObjectID' => $eavID);
		}

		$record['attributes'] =& self::$eavQueue[$className][$record['ID']]['attributes'];
		$record['byHandle'] =& self::$eavQueue[$className][$record['ID']]['byHandle'];
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

		// create array of EavObject gateway objects for all queued records
		$eavObjects = array();
		foreach (self::$eavQueue as $class => &$records)
		{
			$field = EavObject::getClassField($class);

			foreach ($records as $id => $record)
			{
				$eavObjects[] = array('ID' => $record['eavObjectID'], $field => $id);
			}
		}

		// fetch EAV values
		if ($eavObjects)
		{
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
							self::$eavQueue[$class][$refId]['attributes'] = $entry['attributes'];
							foreach ($entry['attributes'] as $attr)
							{
								self::$eavQueue[$class][$refId]['byHandle'][$attr['EavField']['handle']] = $attr;
							}
						}
						break;
					}
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

		if (!class_exists('ModelPlugin'))
		{
			ClassLoader::import('application.model.ModelPlugin');
		}

		// get plugins
		$path = 'model/' . strtolower($className) . '/' . $action;
		foreach (self::getApplication()->getPlugins($path) as $plugin)
		{
			include_once $plugin['path'];
			new $plugin['class']($object, self::getApplication());
		}
	}

	protected function event($event)
	{
		$this->executePlugins($this, $event);
	}

	public function __clone()
	{
		parent::__clone();

		if (count($this->getSchema()->getPrimaryKeyList()) == 1)
		{
			$this->setID(null);
		}

		if (($this instanceof EavAble) && $this->specificationInstance)
		{
			$this->eavObject->set(null);
			$this->specificationInstance = clone $this->specificationInstance;
			$this->specificationInstance->setOwner(EavObject::getInstance($this));
		}
	}
}

?>
