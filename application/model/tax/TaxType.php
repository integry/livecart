<?php
ClassLoader::import("application.model.system.ActiveTreeNode");
ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("application.model.delivery.*");

/**
 * Hierarchial product category model class
 *
 * @package application.model.delivery
 */
class TaxType extends MultilingualObject 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("TaxType");
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("isShippingAddressBased", ARBool::instance()));
		$schema->registerField(new ARField("name", ARArray::instance()));
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return TaxType
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	/**
	 * Create new tax rate
	 * 
	 * @param string $$defaultLanguageName Type name spelled in default language
	 * @return TaxType
	 */
	public static function getNewInstance($defaultLanguageName)
	{
	  	$instance = ActiveRecord::getNewInstance(__CLASS__);
	  	$instance->setValueByLang('name', Store::getInstance()->getDefaultLanguageCode(), $defaultLanguageName);
        
	  	return $instance;
	}
}

?>