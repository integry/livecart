<?php

ClassLoader::import("application.model.MultilingualDataObject");
ClassLoader::import("application.model.product.CatalogLangData");

/**
 * Just for TEST purposes.
 *
 * @package application.model.product
 */
class Catalog extends MultiLingualDataObject {
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		Tree::defineSchema($className);		

		$schema->setName("Catalog");	
	}
	
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false) {
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}
	
	public function getSpecFieldList() {
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle("SpecField", "position"));
		$filter->setCondition(new EqualsCond(new ARFieldHandle("SpecField", "catalogID"), $this->getID()));
		
		return SpecField::getRecordSetArray($filter);
	}
	

	
}

?>