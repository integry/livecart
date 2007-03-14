<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.category.Category");

class TestRelatedProduct extends UnitTestCase
{
    private $groupAutoIncrementNumber = 0;
    private $productAutoIncrementNumber = 0;
    private $relatedAutoIncrementNumber = 0;
    
    /**
     * @var Product
     */
    private $product1 = null;
    
    /**
     * @var Product
     */
    private $product2 = null;
    
    /**
     * @var Category
     */
    private $rootCategory = null;
    
    /**
     * @var TestRelatedProductGroup
     */
    private $group = null;
        
    /**
     * Creole database connection wrapper
     *
     * @var Connection
     */
    private $db = null;
    
    public function __construct()
    {
        parent::__construct('Related product tests');
        
        $this->rootCategory = Category::getInstanceByID(Category::ROOT_ID);
	    $this->db = ActiveRecord::getDBConnection();
    }
	
    public function setUp()
	{
	    ActiveRecordModel::beginTransaction();	
	    
	    // Create some product
		$this->product1 = Product::getNewInstance($this->rootCategory);
		$this->product1->save();
		$this->productAutoIncrementNumber = $this->product1->getID();	
		
	    // Create second product 
		$this->product2 = Product::getNewInstance($this->rootCategory);
		$this->product2->save();
		
   		// create new group
		$this->group = RelatedProductGroup::getNewInstance($this->product1);
		$this->group->position->set(5);
		$this->group->save();	
		$this->groupAutoIncrementNumber = $this->group->getID();
	}

	public function tearDown()
	{
	    ActiveRecordModel::rollback();	
	    	
	    $this->db->executeUpdate("ALTER TABLE RelatedProductGroup AUTO_INCREMENT=" . $this->groupAutoIncrementNumber);
	    $this->db->executeUpdate("ALTER TABLE Product AUTO_INCREMENT=" . $this->productAutoIncrementNumber);
	}
	
	public function testInvalidRelationship()
	{
	    // valid
	    try { 
	        $relationship = RelatedProduct::getNewInstance($this->product1, $this->product2); 
		    $relationship->save();
	        $this->pass();
	    } catch(Exception $e) { 
	        $this->fail();
	    }
	    
		// invalid
	    try { 
	        $relationship = RelatedProduct::getNewInstance($this->product1, $this->product1); 
		    $relationship->save();
	        $this->fail();
	    } catch(Exception $e) { 
	        $this->pass();
	    }
	    
	    // two identical relationships are also invalid
	    try {
		    $relationship = RelatedProduct::getNewInstance($this->product1, $this->product2);
		    $relationship->save();
	    	$this->fail();
	    } catch(Exception $e) { 
	        $this->pass();
	    }
	}
	
	public function testCreateNewRelationship()
	{
	    // create
	    $relationship = RelatedProduct::getNewInstance($this->product1, $this->product2);
	    $relationship->save();
	    
	    // reloat
	    $relationship->markAsNotLoaded();
	    $relationship->load(true);
	    
	    // Check if product and related products are not null
	    $this->assertNotNull($relationship->product->get());
	    $this->assertNotNull($relationship->relatedProduct->get());
	    // Check group
	    $this->assertNull($relationship->relatedProductGroup->get());
	    
	    // Check if product is product and related product is related
	    $this->assertTrue($relationship->product->get() === $this->product1);
	    $this->assertTrue($relationship->relatedProduct->get() === $this->product2);

	    // Check if related and main products are not the same
	    $this->assertFalse($relationship->product->get() === $relationship->relatedProduct->get());
	    $this->assertFalse($this->product1 === $this->product2);
	    
	    $relationship->relatedProductGroup->set($this->group);
	    $relationship->save();
	    
	    // reloat
	    $relationship->markAsNotLoaded();
	    $relationship->load();
	    
	    // Check group
	    $this->assertTrue($relationship->relatedProductGroup->get() === $this->group);
	}

	public function testDeleteRelationship()
	{
	    $relationship = RelatedProduct::getNewInstance($this->product1, $this->product2);
	    $relationship->save();
	    $this->assertTrue($relationship->isExistingRecord());
	    
	    $relationship->delete();
	    $this->assertFalse($relationship->isLoaded());
	}
}
?>