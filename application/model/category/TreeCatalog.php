<?php
ClassLoader::import("application.model.MultilingualDataObject");
ClassLoader::import("application.model.product.CatalogLangData");
//ClassLoader::import("application.model.Tree");

/**
 * Catalog item class (represents one branch of catalog)
 *
 * @package application.model.product
 */
class TreeCatalog extends Tree 
{	

	/**
	 * Catalog instance
	 *
	 * @var Catalog
	 */
	private $catalog;
	
	/**
	 * Shows if catalog tree is loaded
	 *
	 * @var bool
	 */
	private $treeLoaded = false;
	
	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */
	public static function defineSchema($className = __CLASS__) 
	{
		$schema = self::getSchemaInstance($className);
		Tree::defineSchema($className);
		
		$schema->setName("Catalog");	
	}
	
	/**
	 * Get catalog tree
	 *
	 * @param null|int|Tree Parent tree or it's id. If null, has no parent.
	 * @return TreeCatalog
	 */
	public static function getNewTreeInstance($parent = null) 
	{
	 	$treeCatalog = parent::getNewTreeInstance(__CLASS__, $parent); 	 		 	
	 	$treeCatalog->catalog = Catalog::getNewInstance("Catalog");
	 	return $treeCatalog;	 	
	}

	/**
	 * Get catalog tree
	 *
	 * @param int $id Root tree node
	 * @return TreeCatalog
	 */
	public static function getTreeInstanceById($id) 
	{
	 	$treeCatalog = parent::getTreeInstanceById("TreeCatalog", $id); 	 		 	 	
		
		if (empty($treeCatalog->catalog)) 
		{
			$lft =$treeCatalog->lft->get();
			$rgt =$treeCatalog->rgt->get();
				
			$cond = new OperatorCond(new ARFieldHandle("Catalog", "lft"), $lft, ">=");
			$cond->addAND(new OperatorCond(new ARFieldHandle("Catalog", "rgt"), $rgt, "<="));
	
			$filter = new ARSelectFilter();
			$filter->setCondition($cond);
			Catalog::getRecordSet("Catalog", $filter, true);
	
		 	$treeCatalog->setCatalogRecursive();			
		}
	
	 	return $treeCatalog;	 	
	}

	/**
	 * Get whole catalog tree tree
	 *
	 * @param int $id Root tree node
	 * @return TreeCatalog
	 */
	public static function getAllTree() 
	{
		$treeCatalog = parent::getAllTree("TreeCatalog");   
		if (!$treeCatalog->treeLoaded) 
		{
			Catalog::getRecordSet("Catalog", new ARSelectFilter(), true);
				
		 	$treeCatalog->setCatalogRecursive();	
		}
			
		return $treeCatalog;
	}
	
	private function setCatalogRecursive() 
	{
  		$this->treeLoaded = true;  		
  		if ($this->getId()) 
  		{
		  	$this->catalog = Catalog::getInstanceById($this->getId(), true, true);	  	
		} 
		
	  	foreach ($this as $child) 
	  	{
			$child->setCatalogRecursive();
		}	  	
	}
		
	/**
	 * Get CatalogLangData object
	 *
	 * @param string $langCode Language code
	 * @return CatalogLangData
	 */
	public function lang($langCode) 
	{
	  	return $this->catalog->lang($langCode);
	}
	
	/**
	 * Save changes to object
	 *
	 */
	public function save() 
	{
	  	parent::save();		  		  	
	  	
	  	$this->catalog->setId($this->getId());
		$this->catalog->save();	  
	}
}

?>