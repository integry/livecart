<?php

use eav\EavAble;
use eav\EavObject;
use eav\EavSpecificationManager;

Phalcon\Mvc\Model::setup(['exceptionOnFailedSave' => true]);

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
 	
 	protected $relatedInstances;

	public function initialize()
	{
		$this->useDynamicUpdate(true);
	}
   
	public function getSource()
	{
		$parts = explode('\\', get_class($this));
		return array_pop($parts);
	}

	public function getID()
	{
		return $this->ID;
	}

	public function setID($id)
	{
		$this->ID = $id;
	}

	public static function getInstanceByID($id)
	{
		$class = get_called_class();
		return $class::query()->where('ID = :id:', array('id' => $id))->execute()->getFirst();
	}

	public static function getRequestInstance(\Phalcon\Http\Request $request, $field = 'ID')
	{
		$class = get_called_class();
		return $class::getInstanceByID($request->getJson($field));
	}

	public static function updateFromRequest(\Phalcon\Http\Request $request)
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
	public function loadRequestData(LiveCartRequest $request, $sanitize = false)
	{
		if ($json = $request->getJsonRawBody())
		{
			if ($sanitize)
			{
				foreach ($json as $key => $value)
				{
					if (is_string($value))
					{
						$json[$key] = strip_tags($value);
					}
				}
			}
			
			$this->assign($json);
		}
		else
		{
			$this->assign($request);
		}
		
		if ($this instanceof EavAble)
		{
			$spec = $this->getSpecification();
			$spec->loadRequestData($request);
		}

		return;
		//var_dump($request);exit;
		$enabledFields = is_array($prefix) ? array_flip($prefix) : null;
		$languages = $this->getDI()->get('application')->getLanguageArray(LiveCart::INCLUDE_DEFAULT);

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
						if ($request->has($reqName . '_' . $lang))
						{
							$hasLangValue = true;
							break;
						}
					}
				}

				if ($hasLangValue || $request->has($reqName) ||
				   ($request->has('checkbox_' . $reqName) && ('ARBool' == get_class($field->getDataType())))
					)
				{
					switch ($dataType)
					{
						case 'ARArray':
							$this->setValueArrayByLang(array($name), $this->getDI()->get('application')->getDefaultLanguageCode(), $this->getDI()->get('application')->getLanguageArray(LiveCart::INCLUDE_DEFAULT), $request);
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
		$this->eavObject = null;
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

		$this->specificationInstance = new EavSpecificationManager($this, $specificationData);
	}
	
	public function handle($handle)
	{
		return $this->getSpecification()->getAttributeByHandle($handle);
	}

	protected function setLastPosition($parentField = null, $parent = null)
	{
		if ($parentField && !$parent)
		{
			$parent = $this->$parentField;
		}

		// get max position
	  	$query = $this->query();
	  	if ($parent)
	  	{
	  		$query->where($parentField . '= :parent:', array('parent' => $parent));
	  	}
		$query->orderBy('position DESC')->limit(1);

		$rec = $query->execute()->getFirst();
		$this->position = $rec ? $rec->position + 1 : 0;
	}

	public function beforeCreate()
	{
		$this->executePlugins($this, 'before-insert');
	}

	public function afterCreate()
	{
		$this->executePlugins($this, 'insert');
		$this->executePlugins($this, 'save');
	}

	protected function beforeUpdate()
	{
		$this->executePlugins($this, 'before-update');
	}

	public function afterUpdate()
	{
		$this->executePlugins($this, 'update');
		$this->executePlugins($this, 'save');
	}

	public function beforeSave()
	{
		$this->executePlugins($this, 'before-save');
	}

	public function afterSave()
	{
		if (($this instanceof EavAble) && $this->isSpecificationLoaded() && $this->getSpecification()->hasValues())
		{
			$eavObject = $this->get_EavObject();
			if (!$eavObject)
			{
				$eavObject = EavObject::getNewInstance($this);
				$eavObject->save();

				$this->getSpecification()->setOwner($this);

				$this->eavObjectID = $eavObject->getID();
				$this->save();
			}
		}

		if (isset($eavObject))
		{
			$eavObject->save();
		}

		if ($this instanceof EavAble && $this->specificationInstance && isset($eavObject))
		{
			if ($this->specificationInstance->hasValues())
			{
				//$this->specificationInstance->save();
			}
			else
			{
				$eavObject->delete();
			}
		}

		$this->executePlugins($this, 'after-save');
	}

/*
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
					$locale = $this->getDI()->get('application')->getLocale();
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
*/

	public static function isEav($className)
	{
		if (!isset(self::$isEav[$className]))
		{
			self::$isEav[$className] = (array_search('eav\EavAble', class_implements($className)) !== false);
		}

		return self::$isEav[$className];
	}

	public function toArray()
	{
		$array = parent::toArray();

		if ($this->specificationInstance)
		{
			$array['eav'] = $this->specificationInstance->toArray();
		}

		/*
		self::executePlugins($array, 'array', get_class($this));
		if ($this->specificationInstance && ($this->specificationInstance instanceof EavSpecificationManager) && (empty($array['attributes']) || $force))
		{
			$array['attributes'] = $this->specificationInstance->toArray();
			$array['attributes']['markAsJSONObject'] = true;
			EavSpecificationManager::sortAttributesByHandle('EavSpecificationManager', $array);
		}
		*/

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
		
		$className = get_real_class($className);

		// get plugins
		$path = 'model/' . strtolower($className) . '/' . $action;
		foreach ($this->getDI()->get('application')->getPlugins($path) as $plugin)
		{
			include_once $plugin['path'];
			new $plugin['class']($object, $this->getDI());
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
	
	public function getFormattedTime($field, $type)
	{
		$type = str_replace('t_', 'time_', str_replace('d_', 'date_', $type));
		return $this->getDI()->get('application')->getLocale()->getFormattedTime($this->$field, $type);
	}

	public function __call($method, $arguments = NULL)
	{
		if (substr($method, 0, 4) == 'set_')
		{
			$property = $this->getRelatedProperty($method);
			return $this->setRelatedInstance($property, $arguments[0]);
		}
		
		if (substr($method, 0, 4) == 'get_')
		{
			$property = $this->getRelatedProperty($method);
			if (property_exists($this, $property))
			{
				return $this->getRelatedInstance($property);
			}
		}
		
		if (isset($this->$method) && (!empty($arguments) && (in_array(substr($arguments[0], 0, 2), array('d_', 't_')))))
		{
			return $this->getFormattedTime($method, $arguments[0]);
		}
		else
		{
			return parent::__call($method, $arguments);
		}
	}
	
	protected function getRelatedProperty($method)
	{
		$property = substr($method, 4);
		return lcfirst($property) . 'ID';
	}
	
	protected function setRelatedInstance($key, ActiveRecordModel $value)
	{
		if (!empty($value->ID))
		{
			$this->$key = $value->getID();
		}
		
		$this->relatedInstances[$key] = $value;
	}

	protected function getRelatedInstance($key)
	{
		$objectKey = substr($key, 0, -2);

		if (!empty($this->relatedInstances[$key]))
		{
			return $this->relatedInstances[$key];
		}
		else if (property_exists($this, $key))
		{
			$method = 'get' . ucfirst($objectKey);
			$this->relatedInstances[$key] = $this->$method();
			return $this->relatedInstances[$key];
		}
	}

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

	public function toAngular()
	{
		return htmlentities(json_encode($this->toArray()));
	}
}

?>
