<?php

namespace eav;

/**
 * Custom field container for a particular EAV type (users, manufacturers, orders, etc)
 *
 * @package application/model/eav
 * @author Integry Systems <http://integry.com>
 */
class EavFieldManager
{
	private $classID;
	private $stringIdentifier;
	private $fields;
	private static $instances = array();
	
	public static function getInstance($identifier)
	{
		if (empty(self::$instances[$identifier]))
		{
			self::$instances[$identifier] = new self($identifier);
			self::$instances[$identifier]->loadFields();
		}
		
		return self::$instances[$identifier];
	}

	public function __construct($classID)
	{
		if (!is_numeric($classID))
		{
			$newID = EavField::getClassID($classID);
			if (!$newID)
			{
				$this->stringIdentifier = $classID;
			}

			$classID = $newID;
		}

		$this->classID = $classID;
	}
	
	public function getClassID()
	{
		return $this->classID;
	}

	public function getClassName()
	{
		return EavField::getClassNameById($this->classID);
	}
	
	public function loadFields()
	{
		if (is_null($this->fields))
		{
			$this->fields = array();
			$ids = array();
			
			foreach ($this->getFieldQuery()->execute() as $field)
			{
				$this->fields[$field->getID()] = $field;
				$ids[] = $field->getID();
				
				if ($field->valueFieldID)
				{
					$ids[] = $field->valueFieldID;
					$aliases[$field->getID()] = $field->valueFieldID;
				}
			}
			
			if ($ids)
			{
				foreach (EavValue::query()->inWhere('fieldID', $ids)->orderBy('position')->execute() as $value)
				{
					if (isset($this->fields[$value->fieldID]))
					{
						$this->fields[$value->fieldID]->registerValue($value);
					}

					foreach ($aliases as $field => $alias)
					{
						if ($alias == $value->fieldID)
						{
							$this->fields[$field]->registerValue($value);
						}
					}
				}
			}
		}
	}
	
	public function handle($handle)
	{
		foreach ($this->fields as $field)
		{
			if ($handle == $field->handle)
			{
				return $field;
			}
		}
	}
	
	public function getFields()
	{
		$this->loadFields();
		return $this->fields;
	}
	
	public function getField($id)
	{
		$this->loadFields();
		return !empty($this->fields[$id]) ? $this->fields[$id] : null;
	}

	public function setResponse(\ControllerBase $controller)
	{
		$this->loadFields();
		
		$response = array('eavFieldsHandle' => new \StdClass);
		foreach ($this->fields as $field)
		{
			$handle = $field->handle;
			$response['eavFieldsHandle']->$handle = $field;
		}
		
		foreach ($response as $key => $value)
		{
			$controller->set($key, $value);
		}
	}
	
	public function toArray()
	{
		$arr = array();
		foreach ($this->fields as $field)
		{
			$arr[$field->handle] = $field->toJson(false);
		}
		
		return $arr;
	}

	/**
	 * Creates a select filter for custom fields
	 *
	 * @param bool $includeParentFields
	 * @return ARSelectFilter
	 */
	private function getFieldQuery($class = null)
	{
		$classID = $class ? EavField::getClassID($class) : $this->classID;

		$filter = EavField::query()->where('classID = :classID:', array('classID' => $classID));
		if (is_null($class) && $this->stringIdentifier)
		{
			$filter->andWhere('stringIdentifier = :stringIdentifier:', array('stringIdentifier' => $this->stringIdentifier));
		}

//		$filter->orderBy('EavFieldGroup.position');
		$filter->orderBy('position');

		return $filter;
	}

	/**
	 * Creates a select filter for fields groups
	 * @return ARSelectFilter
	 */
	private function getGroupFilter()
	{
		$filter = query::query()->where('EavFieldGroup.classID = :EavFieldGroup.classID:', array('EavFieldGroup.classID' => $this->classID));
		if ($this->stringIdentifier)
		{
			$filter->andWhere('EavFieldGroup.stringIdentifier = :EavFieldGroup.stringIdentifier:', array('EavFieldGroup.stringIdentifier' => $this->stringIdentifier));
		}

		$filter->orderBy('EavFieldGroup.position');

		return $filter;
	}
}

?>
