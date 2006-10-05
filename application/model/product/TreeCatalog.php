<?php

ClassLoader::import("application.model.MultilingualDataObject");
ClassLoader::import("application.model.product.CatalogLangData");
//ClassLoader::import("application.model.Tree");

/**
 * Catalog item class (represents one branch of catalog)
 *
 * @package application.model.product
 */
class TreeCatalog extends Tree {	

	private $catalog;
	
	private $tree_loaded = false;
	
	public static function defineSchema($className = __CLASS__) {
		
		$schema = self::getSchemaInstance($className);
		Tree::defineSchema($className);
		$schema->setName("Catalog");	
	}
	
	public static function getNewTreeInstance($parent = null) {
	 
	 	$tree_catalog = parent::getNewTreeInstance("TreeCatalog", $parent); 	 		 	
	 	$tree_catalog->catalog = Catalog::getNewInstance("Catalog");
	 	return $tree_catalog;	 	
	}
	
	public static function getTreeInstanceById($id) {
	 
	 	$tree_catalog = parent::getTreeInstanceById("TreeCatalog", $id); 	 		 	 	
		
		if (empty($tree_catalog->catalog)) {

			$lft =$tree_catalog->lft->get();
			$rgt =$tree_catalog->rgt->get();
				
			$cond = new OperatorCond(new ARFieldHandle("Catalog", "lft"), $lft, ">=");
			$cond->addAND(new OperatorCond(new ARFieldHandle("Catalog", "rgt"), $rgt, "<="));
	
			$filter = new ARSelectFilter();
			$filter->setCondition($cond);
			Catalog::getRecordSet("Catalog", $filter, true);
	
			//All this components a
		 	$tree_catalog->setCatalogRecursive();			
		}
	
	 	return $tree_catalog;	 	
	}
	
	public static function getAllTree() {
	
		$tree_catalog = parent::getAllTree("TreeCatalog");   
		if (!$tree_catalog->tree_loaded) {
			
			Catalog::getRecordSet("Catalog", new ARSelectFilter(), true);
				
			//All this components a
		 	$tree_catalog->setCatalogRecursive();	
		}
			
		return $tree_catalog;
	}
	
	private function setCatalogRecursive() {
  	
  		$this->tree_loaded = true;  		
  		if ($this->getId()) {

		  	$this->catalog = Catalog::getInstanceById($this->getId(), true, true);	  	
		} 
		
	  	foreach ($this as $child) {
		    			
			$child->setCatalogRecursive();
		}	  	
	}
		
	public function lang($lang_code) {
		
	  	return $this->catalog->lang($lang_code);
	}
	
	public function save() {
	  
	  	parent::save();		  		  	
	  	
	  	$this->catalog->setId($this->getId());
		$this->catalog->save();	  
	}
	
}

?>