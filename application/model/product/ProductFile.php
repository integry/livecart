<?php

ClassLoader::import("application.model.ObjectFile");
/**
 *
 * @package application.model.product
 */
class ProductFile extends ObjectFile 
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", "Product", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productFileGroupID", "ProductFileGroup", "ID", "ProductFileGroup", ARInteger::instance()));
		$schema->registerField(new ARField("title", ARInteger::instance()));
		$schema->registerField(new ARField("description", ARInteger::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		$schema->registerField(new ARField("allowDownloadDays", ARInteger::instance()));
	}

	/**
	 * Create new instance of product file
	 *
	 * @param Product $product Product to which the file belongs
	 * @param string $filePath Path to that file (possibly a temporary file)
	 * @param string $fileName File name with extension. (image.jpg)
	 * @return ActiveRecord
	 */
	public static function getNewInstance(Product $product, $filePath, $fileName)
	{
	    $productFileInstance = parent::getNewInstance(__CLASS__, $filePath, $fileName);
	    $productFileInstance->product->set($product);
	    
	    return $productFileInstance;
	}

	/**
	 *
	 * @param Product $product
	 * 
	 * @return ARSet
	 */
	public static function getFilesByProduct(Product $product)
	{
	    return self::getRecordSet(__CLASS__, self::getFilesByProductFilter($product));
	}
	
	private static function getFilesByProductFilter(Product $product)
	{
	    $filter = new ARSelectFilter();	
		$filter->joinTable('ProductFileGroup', 'ProductFile', 'ID', 'productFileGroupID');	
		
		$filter->setOrder(new ARFieldHandle("ProductFileGroup", "position"), ARSelectFilter::ORDER_ASC);		
	    $filter->setCondition(new EqualsCond(new ARFieldHandle(__CLASS__, 'productID'), $product->getID()));
	    $filter->setOrder(new ARFieldHandle(__CLASS__, 'position'), ARSelectFilter::ORDER_ASC);
	    
	    return $filter;
	}
}

?>