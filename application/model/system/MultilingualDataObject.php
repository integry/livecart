<?php

ClassLoader::import("application.model.ActiveRecordModel");
ClassLoader::import("application.model.locale.Language");

/**
 * ORM class for data objects that have to be translated to many different languages
 *
 * @package application.model
 *
 */
abstract class MultilingualDataObject extends ActiveRecordModel
{

	/**
	 * List oj language data objects. Each object maps to a different language
	 *
	 * @var ActiveRecord[]
	 */
	protected $langDataObjecList = array();

	/**
	 * Proxy method for accessing data object fields related to particular language
	 *
	 * @param string $langCode
	 * @return ActiveRecord
	 */
	public function lang($langCode)
	{
		if (empty($this->langDataObjecList[$langCode]))
		{
			$langDataClassName = self::getLanguageDataObjectName(get_class($this));
			$instance = ActiveRecord::getNewInstance($langDataClassName);
			$langObj = ActiveRecord::getInstanceByID("Language", $langCode);
			$instance->language->set($langObj);

			$langDataShema = self::getSchemaInstance(self::getLanguageDataObjectName(get_class($this)));
			foreach($langDataShema->getForeignKeyList()as $FKField)
			{
				if ($FKField->getForeignClassName() == get_class($this))
				{
					$instance->setFieldValue($FKField->getName(), $this);
				}
			}

			$this->setLangDataObject($instance);
		}
		return $this->langDataObjecList[$langCode];
	}

	/**
	 * Sets language object which contains data subset of this object translated to some language
	 *
	 * @param ActiveRecord $obj
	 */
	public function setLangDataObject($obj)
	{
		$lang = $obj->getFieldValue("languageID");
		$langCode = $lang->getID();

		$this->langDataObjecList[$langCode] = $obj;
	}

	/**
	 * Gets a class name which maps to some table containing data translation of this object
	 *
	 * @param string $className
	 * @return string
	 */
	public static function getLanguageDataObjectName($className)
	{
		return $className."LangData";
	}

	public function load($loadReferencedRecords = false)
	{
		if ($this->isLoaded)
		{
			return ;
		}
		$query = self::createSelectQuery(get_class($this), $loadReferencedRecords);
		$this->loadData($loadReferencedRecords, $query);

		$langDataRecordSet = self::getLangDataRecordSet(get_class($this), $query, array($this->getID()));
		foreach($langDataRecordSet as $langDataRecord)
		{
			$this->setLangDataObject($langDataRecord);
		}
	}

	/**
	 * Loads a record set of translated fields (in different languages)
	 *
	 * @param string $className
	 * @param ARSelectQueryBuilder $query
	 * @param array $recordIDList
	 * @return ARSet
	 */
	protected static function getLangDataRecordSet($className, ARSelectQueryBuilder $query, $recordIDList = array())
	{
		$IDList = $recordIDList;
		$langDataQuery = new ARSelectQueryBuilder();
		$langDataScheme = self::getSchemaInstance(self::getLanguageDataObjectName($className));
		$langDataTableName = $langDataScheme->getName();
		$mainTableName = self::getSchemaInstance($className)->getName();
		$langDataJoinField = null;

		foreach($langDataScheme->getForeignKeyList()as $FKField)
		{
			if ($FKField->getForeignClassName() == $className)
			{
				$langDataJoinField = $FKField;
			}
		}

		$langDataQuery->includeTable($langDataTableName);
		$langDataQuery->includeTable($mainTableName);
		$langDataQuery->setFilter($query->getFilter());

		$langDataQuery->addField($langDataTableName.".*");
		$langDataQuery->getFilter()->mergeCondition(new EqualsCond(new ARFieldHandle($className, $langDataJoinField->getForeignFieldName()), new ARFieldHandle(self::getLanguageDataObjectName($className), $langDataJoinField->getName())));

		//denisas
		if (count($IDList) > 0)
		{
			$langDataQuery->getFilter()->mergeCondition(new INCond(new ARFieldHandle($className, "ID"), implode(", ", $IDList)));
		}

		$langDataRecordSet = ActiveRecord::getRecordSetByQuery(self::getLanguageDataObjectName($className), $langDataQuery);

		return $langDataRecordSet;
	}

	/**
	 * Overloads a parent method of loading a record set of this class
	 * Additionaly this method loads translated field list from other table (multilingual
	 * data) and maps to a loaded record set
	 *
	 * @param string $className
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 * @return ARSet
	 */
	public static function getRecordSet($className, ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		$query = self::createSelectQuery($className, $loadReferencedRecords);
		$query->setFilter($filter);
		$recordSet = self::createRecordSet($className, $query, $loadReferencedRecords);

		$IDList = array();
		foreach($recordSet as $record)
		{
			$IDList[] = $record->getID();
		}

		$langDataRecordSet = self::getLangDataRecordSet($className, $query, $IDList);

		foreach($langDataRecordSet as $langRecord)
		{
			$recordID = $langRecord->getID();
			$languageID = $recordID['languageID'];
			$PKFieldNameList = array_keys($recordID);
			if ($PKFieldNameList[0] == 'languageID')
			{
				$dataObjectID = $recordID[$PKFieldNameList[1]];
			}
			else
			{
				$dataObjectID = $recordID[$PKFieldNameList[0]];
			}

			foreach($recordSet as $dataRecord)
			{
				if ($dataRecord->getID() == $dataObjectID)
				{
					$dataRecord->setLangDataObject($langRecord);
				}
			}
		}
		return $recordSet;
	}

	/**
	 * Creates a record set as array
	 *
	 * @param string $className
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 * @return array
	 *
	 * @todo Make this method faster (rewrite). Now it needs to create a complex
	 * object structure which is converted to array. Method should create array immidiately
	 */
	public static function getRecordSetArray($className, ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return self::getRecordSet($className, $filter, $loadReferencedRecords)->toArray();
	}

	public function save()
	{
		parent::save();
		foreach($this->langDataObjecList as $lang => $object)
		{
			/* Checking if such an object (data translation) already exists */
			$langRecordID = array();
			$langRecordID['languageID'] = $lang;
			$langDataClassName = self::getLanguageDataObjectName(get_class($this));

			$langDataSchema = self::getSchemaInstance($langDataClassName);
			foreach($langDataSchema->getForeignKeyList()as $field)
			{
				if ($field->getForeignClassName() == get_class($this))
				{
					$langRecordID[$field->getName()] = $this->getID();
				}
			}

			if (ActiveRecord::objectExists($langDataClassName, $langRecordID))
			{
				$object->save(ActiveRecord::PERFORM_UPDATE);
			}
			else
			{
				$object->save(ActiveRecord::PERFORM_INSERT);
			}
		}
	}

	public function toArray($recursive = true)
	{
		$data = parent::toArray($recursive);
		foreach($this->langDataObjecList as $key => $langObj)
		{
			$data['lang'][$key] = $langObj->toArray(false);
		}
		return $data;
	}

}

?>
