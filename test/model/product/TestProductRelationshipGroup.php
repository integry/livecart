<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.category.Category");

/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class TestProductRelationshipGroup extends UnitTest
{
    private $groupAutoIncrementNumber = 0;
    private $productAutoIncrementNumber = 0;
    
    /**
     * @var Product
     */
    private $product = null;

    /**
     * @var Category
     */
    private $rootCategory = null;
 
    public function __construct()
    {
        parent::__construct('Related product groups tests');
        
        $this->rootCategory = Category::getInstanceByID(Category::ROOT_ID);
    }
    
    public function getUsedSchemas()
    {
        return array(
            'ProductRelationshipGroup',
            'Product'
        );
    }
	
    public function setUp()
	{
	    parent::setUp();
		
	    // Create some product
		$this->product = Product::getNewInstance($this->rootCategory, 'test');
		$this->product->save();
		return;
   		// create new group
		$dump = ProductRelationshipGroup::getNewInstance($this->product);
		$dump->save();
	}
	
	public function testCreateNewGroup()
	{
	    $group = ProductRelationshipGroup::getNewInstance($this->product);
	    $group->setValueByLang('name', 'en', 'TEST_GROUP');
	    $group->save();
	    
	    // Reload
	    $group->reload(array('Product'));
	    
	    $name = $group->name->get();
	    $this->assertEqual($name['en'], 'TEST_GROUP');
	    $this->assertEqual($this->product->getID(), $group->product->get()->getID());
	    $this->assertTrue($this->product === $group->product->get());
	}
		
	public function testDeleteGroup()
	{
	    $group = ProductRelationshipGroup::getNewInstance($this->product);
	    $group->save();
	    $this->assertTrue($group->isExistingRecord());
	    
	    $group->delete();
	    $this->assertFalse($group->isLoaded());
	}

	public function testGetProductGroups()
	{
	    // new product
		$product = Product::getNewInstance($this->rootCategory, 'test');
		$product->save();	
	    
	    $groups = array();
	    foreach(range(1, 3) as $i)
	    {
		    $groups[$i] = ProductRelationshipGroup::getNewInstance($product);
		    $groups[$i]->position->set($i);
		    $groups[$i]->setValueByLang('name', 'en', 'TEST_GROUP_' . $i);
		    $groups[$i]->save();
	    }
	    
	    $this->assertEqual(count($groups), ProductRelationshipGroup::getProductGroups($product)->getTotalRecordCount());
	    $i = 1;
	    foreach(ProductRelationshipGroup::getProductGroups($product) as $group)
	    {
	        $this->assertTrue($groups[$i] === $group);
	        $i++;
	    }
	}
}
?>