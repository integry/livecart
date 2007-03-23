<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.category.SpecField");
ClassLoader::import("application.model.category.SpecFieldValue");

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
        
    private $specFieldAutoIncrementNumber;
    private $specFieldValueAutoIncrementNumber;
    private $productAutoIncrementNumber;
    
    /**
     * Creole database connection wrapper
     *
     * @var Connection
     */
    private $db;
    
    public function __construct()
	{
	    parent::__construct();
	    $this->db = ActiveRecord::getDBConnection();
	    $this->rootCategory = Category::getInstanceByID(ActiveTreeNode::ROOT_ID);
	}
    
    public function setUp()
	{
	    ActiveRecordModel::beginTransaction();	
		
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
	
	public function tearDown()
	{
	    ActiveRecordModel::rollback();		
	    $this->db->executeUpdate("ALTER TABLE SpecField AUTO_INCREMENT=" . $this->specFieldAutoIncrementNumber);
	    $this->db->executeUpdate("ALTER TABLE SpecFieldValue AUTO_INCREMENT=" . $this->specFieldValueAutoIncrementNumber);
	    $this->db->executeUpdate("ALTER TABLE Product AUTO_INCREMENT=" . $this->productAutoIncrementNumber);
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
	
	public function testDeleteMergeSpecFields()
	{
	    $specFieldValues = array();
	    foreach(range(1, 2) as $i) 
	    {
	        $specFieldValues[$i] = SpecFieldValue::getNewInstance($this->specField);
	        $specFieldValues[$i]->save();
	    }
	    
	    $specFieldValues[1]->mergeWith($specFieldValues[2]);
	    $specFieldValues[1]->save();
	    
	    // All merged loosers should be deleted from database
	    $this->assertFalse($specFieldValues[2]->isExistingRecord());
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