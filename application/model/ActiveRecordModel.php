<?php

/**
 * Base class for all ActiveRecord based models of application (single entry point in
 * application specific model class hierarchy)
 *
 * @package application/model
 * @author Integry Systems <http://integry.com>
 */
abstract class ActiveRecordModel extends \Phalcon\Mvc\Model
{
 	private static $eavQueue = array();

 	private static $isEav = array();

 	protected $specificationInstance;

	public function getSource()
	{
		$parts = explode('\\', get_class($this));
		return array_pop($parts);
	}

	public function loadRequestModel(Request $request, $key = '')
	{
		$json = $request->getJSON();
		if ($key)
		{
			$json = $json[$key];
		}

		$modelReq = new Request();
		$modelReq->setValueArray($json);

		if (!empty($json['attributes']) && is_array($json['attributes']))
		{
			foreach ($json['attributes'] as $key => $value)
			{
				if (!empty($value['valueIDs']))
				{
					foreach (json_decode($value['valueIDs']) as $valueID)
					{
						$modelReq->set('specItem_' . $valueID, 'on');
					}

					if (!empty($value['newValues']))
					{
						foreach (json_decode($value['newValues']) as $newVal)
						{
							$others = $modelReq->get('other', array());
							$others[$key][] = $newVal;
							$modelReq->set('other', $others);
						}
					}

					$modelReq->set('removeEmpty_' . $key, 'on');
				}
				else if (isset($value['ID']))
				{
					$modelReq->set('specField_' . $key, $value['ID']);
					if (!empty($value['newValue']))
					{
						$others = $modelReq->get('other', array());
						$others[$key] = $value['newValue'];
						$modelReq->set('other', $others);
					}
				}
				else
				{
					$modelReq->set('specField_' . $key, $value['value']);
					foreach (self::getApplication()->getLanguageArray() as $lang)
					{
						if (!empty($value['value_' . $lang]))
						{
							$modelReq->set('specField_' . $key . '_' . $lang, $value['value_' . $lang]);
						}
					}
				}
			}
		}

		$this->loadRequestData($modelReq);
	}

	public static function getRequestInstance(Request $request, $field = 'ID')
	{
		$data = $request->getJSON();
		return ActiveRecordModel::getInstanceByID(get_called_class(), $data[$field], true);
	}

	public static function updateFromRequest(Request $request)
	{
		$instance = self::getRequestInstance($request);
		$instance->loadRequestModel($request);

		return $instance;
	}

	/**
	 *  Note that the form may not always contain all the fields of the model, so we must always
	 *  make sure that the data for the particular field has actually been submitted to avoid
	 *  setting empty values for fields that weren't included in the form
	 */
	public function loadRequestData(Request $request, $prefix = '')
	{
		$enabledFields = is_array($prefix) ? array_flip($prefix) : null;
		$languages = self::getApplication()->getLanguageArray(LiveCart::INCLUDE_DEFAULT);

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

				$dataType = get_class($field->getDataType());

				// lang field?
				$hasLangValue = false;
				if ('ARArray' == $dataType)
				{
					foreach ($languages as $lang)
					{
						if ($request->isValueSet($reqName . '_' . $lang))
						{
							$hasLangValue = true;
							break;
						}
					}
				}

				if ($hasLangValue || $request->isValueSet($reqName) ||
				   ($request->isValueSet('checkbox_' . $reqName) && ('ARBool' == get_class($field->getDataType())))
					)
				{
					switch ($dataType)
					{
						case 'ARArray':
							$this->setValueArrayByLang(array($name), self::getApplication()->getDefaultLanguageCode(), self::getApplication()->getLanguageArray(LiveCart::INCLUDE_DEFAULT), $request);
						break;

						case 'ARBool':
							$this->setFieldValue($name, in_array(strtolower($request->gget($reqName)), array('on', 1, 'yes', 'true')));
						break;

						case 'ARInteger':
						case 'ARFloat':
							if (is_numeric($request->gget($reqName)))
							{
								$this->setFieldValue($name, $request->gget($reqName));
							}
						break;

						default:
							$this->setFieldValue($name, $request->gget($reqName));
						break;
					}
				}
			}
		}

		if ($this instanceof EavAble)
		{
			$hasSpecification = $this->isSpecificationLoaded();
			$this->getSpecification()->loadRequestData($request, $prefix);
			if (!$hasSpecification && !$this->getSpecification()->hasValues())
			{
				$this->removeSpecification();
			}
		}
	}

	public function isSpecificationLoaded()
	{
		return !empty($this->specificationInstance);
	}

	public function removeSpecification()
	{
		$this->eavObject->setNull();
		unset($this->specificationInstance);
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

		if ($this->isSpecificationLoaded() && !$specificationData)
		{
			return;
		}

		if (!$this instanceof EavAble && !$this instanceof EavObject)
		{
			throw new ApplicationException(get_class($this) . ' does not support EAV');
		}


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
	  	$cond = $parent ? new EqualsCond(new ARFieldHandle(get_class($this), $parentField), $parent->getID()) : null;

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
		$this->executePlugins($this, 'before-insert');
		$res = parent::insert();
		$this->executePlugins($this, 'insert');
		$this->executePlugins($this, 'save');
		return $res;
	}

	protected function _update()
	{
		$this->executePlugins($this, 'update');
		$res = parent::update();
		$this->executePlugins($this, 'save');
		return $res;
	}

	public function __save($forceOperation = null)
	{
		$this->executePlugins($this, 'before-save');

		if (($this instanceof EavAble) && $this->eavObject && !$this->eavObject->getID() && $this->isSpecificationLoaded() && $this->getSpecification()->hasValues())
		{
			$eavObject = $this->eavObject;
			$this->eavObject->setNull();
		}

		$res = parent::save($forceOperation);

		if (isset($eavObject))
		{
			$eavObject->save();
			$this->eavObject->set($eavObject);
			$this->save();
		}

		if ($this instanceof EavAble && $this->specificationInstance && $this->eavObject)
		{
			if ($this->specificationInstance->hasValues())
			{
				$this->specificationInstance->save();
			}
			else
			{
				$this->eavObject->delete();
			}
		}

		$this->executePlugins($this, 'after-save');

		$cache = self::getApplication()->getCache();
		if ($cache instanceof MemCachedCache)
		{
			$cache->set($this->getRecordIdentifier($this), $this);
		}

		return $res;
	}

	protected function storeToPool()
	{
		$cache = self::getApplication()->getCache();
		if ($cache instanceof MemCachedCache)
		{
			$cache->set($this->getRecordIdentifier($this), $this);
		}

		parent::storeToPool();
	}

	public static function retrieveFromPool($className, $recordID = null)
	{
		if (is_object($recordID))
		{
			if (!($recordID instanceof ActiveRecord))
			{
				return;
			}

			if ($recordID instanceof ARSerializableDateTime)
			{
				debug_zval_dump($recordID);
			}

			$recordID = $recordID->getID();
		}

		if (($memPool = parent::retrieveFromPool($className, $recordID)) || is_null($recordID))
		{
			return $memPool;
		}

		$cache = self::getApplication()->getCache();
		if ($cache instanceof MemCachedCache)
		{
			if ($instance = $cache->get($className . '-' . self::getRecordHash($recordID)))
			{
				$instance->storeToPool();
				return $instance;
			}
		}
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
		$data['__class__'] = $schemaName;

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
		if ($this->specificationInstance && ($this->specificationInstance instanceof EavSpecificationManager) && (empty($array['attributes']) || $force))
		{
			$array['attributes'] = $this->specificationInstance->toArray();
			$array['attributes']['markAsJSONObject'] = true;
			EavSpecificationManager::sortAttributesByHandle('EavSpecificationManager', $array);
		}

		return $array;
	}

	public function processBusinessRules(BusinessRuleManager $manager)
	{
		$manager->processInstanceActions($this);
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

	/*
	public function __clone()
	{
		parent::__clone();

		if (count($this->getSchema()->getPrimaryKeyList()) == 1)
		{
			$this->setID(null);
		}

		if (($this instanceof EavAble) && $this->specificationInstance)
		{
			$this->setSpecification(clone $this->specificationInstance);
		}
	}
	*/

	/**
	 *	Assign an entirely new specification (custom field) container. Usually necessary after cloning, etc.
	 */
	public function setSpecification(EavSpecificationManager $specificationInstance)
	{
		$this->eavObject->set(null);
		$this->specificationInstance = $specificationInstance;
		$this->specificationInstance->setOwner(EavObject::getInstance($this));
	}

	public function unserialize($serialized)
	{
				$res = parent::unserialize($serialized);

		if (!empty($this->specificationInstance))
		{
			$this->specificationInstance->setOwner($this->eavObject);
		}

		return $res;
	}
}

?>
