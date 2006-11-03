<?php

ClassLoader::import("application.model.MultilingualDataObject");
ClassLoader::import("application.model.product.CatalogLangData");

/**
 * Catalog item class (represents one branch of catalog)
 *
 * @package application.model.product
 */
class TreeCatalog extends Tree
{

	private static $catalog;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		Tree::defineSchema($className);
		$schema->setName("Catalog");
	}

	public static function getNewTreeInstance($parent = null)
	{
		$tree_catalog = parent::getNewTreeInstance($className, $parent);

		return $tree_catalog;
	}

	private function catalog()
	{
		if (empty(self::$catalog))
		{
			if ($this->hasID())
			{
				self::$catalog = Catalog::getInstanceById("Catalog", $this->getId(), true, true);
			}
			else
			{
				self::$catalog = Catalog::getNewInstance("Catalog");
			}
		}

		return self::$catalog;
	}

	public function lang($lang_code)
	{
		return $this->catalog()->lang($lang_code);
	}

	public function save()
	{
		parent::save();


		//$catalog = Catalog::getInstanceById("Catalog", $this->getId(), true);
		//echo get_class($catalog);
		//$catalog->lang("en")->name->set("asdfasd");
		//$catalog->save();

		//$this->catalog()->lft->set(1);
		//$this->catalog()->rgt->set(2);

		//$this->catalog()->save();

		/*$catalog = Catalog::getNewInstance("Catalog");
		$catalog->lft->set(1);
		$catalog->rgt->set(2);
		$catalog->lang("en")->name->set("asdfasd");
		$catalog->save();	  	*/
	}


	/**
	 * Gets a list of specification fields created for this catalog
	 *
	 * @return ARSet
	 */
	/*	public function getSpecFieldList() {

	$filter = new ARSelectFilter();
	$filter->setOrder(new ARFieldHandle("SpecField", "position"));
	$joinCond = new EqualsCond(new ARFieldHandle("SpecField", "catalogID"), $this->getID());
	$filter->mergeCondition($joinCond);

	$specFieldList = SpecField::getRecordSetArray($filter);

	return $specFieldList;
	}

	public static function defineSchema($className = __CLASS__) {

	$schema = self::getSchemaInstance($className);
	$schema->setName("Catalog");

	$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));
	$schema->registerField(new ARField("isLeaf", Integer::instance(1)));
	$schema->registerField(new ARField("isVisible", Integer::instance(1)));
	$schema->registerField(new ARField("leftBranch", Integer::instance()));
	$schema->registerField(new ARField("rightBranch", Integer::instance()));
	}

	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false) {
	return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	public static function getNewInstance() {
	return parent::getNewInstance(__CLASS__);
	}*/
}

?>
