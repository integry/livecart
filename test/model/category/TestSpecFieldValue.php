<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.category.SpecField");
ClassLoader::import("application.model.category.SpecFieldValue");
ClassLoader::import("application.model.product.Product");

class testSpecFieldValue extends UnitTest
{
	/**
	 * Root category
	 * @var Category
	 */
    private $rootCategory;
    
    /**
     * Some specification field
     * @var SpecField
     */
    private $specField;
    
    /**
     * Some product
     * @var Product
     */
    private $product;

    public function __construct()
	{
	    parent::__construct('Specification fields test');
	    $this->rootCategory = Category::getInstanceByID(ActiveTreeNode::ROOT_ID);
	}
	
	public function getUsedSchemas()
	{
	    return array(
	        'Category',
	        'SpecField',
	        'SpecFieldValue',
	        'Product'
	    );
	}
    
    public function setUp()
	{
	    parent::setUp();
		
	    $this->specField = SpecField::getNewInstance($this->rootCategory, SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_SELECTOR);
	    $this->specField->save();
	    $this->specFieldAutoIncrementNumber = $this->specField->getID();
	    
	    $specFieldValue = SpecFieldValue::getNewInstance($this->specField);
	    $specFieldValue->save();
	    $this->specFieldValueAutoIncrementNumber = $specFieldValue->getID();
	    
	    $this->product = Product::getNewInstance($this->rootCategory);
	    $this->product->save();
	    $this->productAutoIncrementNumber = $this->product->getID();
	}

	public function testMergeSpecFieldValidParameters()
	{
	    $specFieldValues = array();
	    foreach(range(1, 2) as $i) $specFieldValues[$i] = SpecFieldValue::getNewInstance($this->specField);
	    
	    try {
            $specFieldValues[1]->mergeWith($this->rootCategory);
            $this->fail();
        } catch(ApplicationException $e) { 
            $this->swallowErrors();
            $this->pass(); 
	    } catch(Exception $e) {
	        $this->fail();
	    }
	    
        try {
            $specFieldValues[1]->mergeWith($specFieldValues[2]);
            $this->fail();
        } catch(ApplicationException $e) { 
            $this->pass(); 
	    } catch (Exception $e) {
	        $this->fail();
	    }
	}
	
	public function testMergeValueWithItself()
	{
	    $specFieldValue = SpecFieldValue::getNewInstance($this->specField);;
	    $specFieldValue->save();
	    $specFieldValue->mergeWith($specFieldValue);
	    $specFieldValue->save();

	    // Value should not be deleted
	    $this->assertTrue($specFieldValue->isExistingRecord());
	}
	
	public function testUpdateSpecificationItems()
	{
	    $specFieldValues = array();
	    foreach(range(1, 3) as $i) 
	    {
	        $specFieldValues[$i] = SpecFieldValue::getNewInstance($this->specField);
	        $specFieldValues[$i]->save();
	    }
	    
	    $specificationItems = array();
	    foreach(range(1, 2) as $i)
	    {
	        $specificationItems[$i] = SpecificationItem::getNewInstance($this->product, $this->specField, $specFieldValues[$i]);
	        $specificationItems[$i]->save();
	    }
	    
	    $specFieldValues[1]->mergeWith($specFieldValues[2]);
	    $specFieldValues[1]->save();
	   
	    // After merging values specification item should point to other value
	    $this->assertTrue($specificationItems[1]->specFieldValue->get() === $specFieldValues[1]);
	    $this->assertTrue($specificationItems[2]->specFieldValue->get() === $specFieldValues[2]);
	    $this->assertTrue($specificationItems[2]->specFieldValue->get() !== $specFieldValues[3]);
	}
}

?>