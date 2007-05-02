<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.product.ProductFileGroup");
ClassLoader::import("application.model.category.Category");

class TestProductFileGroup extends UnitTest
{
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
			'ProductFile', 
			'Product', 
			'ProductFileGroup'
        );
    }

    public function setUp()
	{
	    parent::setUp();
	    
		$this->product = Product::getNewInstance($this->rootCategory);
		$this->product->save();
	}
	
	public function testCreateNewGroup()
	{
	    $group = ProductFileGroup::getNewInstance($this->product);
	    $group->setValueByLang('name', 'en', 'TEST_GROUP');
	    $group->save();
	    
	    // Reload
	    $group->markAsNotLoaded();
	    $group->load(array('Product'));
	    
	    $name = $group->name->get();
	    $this->assertEqual($name['en'], 'TEST_GROUP');
	    $this->assertEqual($this->product->getID(), $group->product->get()->getID());
	    $this->assertTrue($this->product === $group->product->get());
	}
	
	public function testDeleteGroup()
	{
	    $group = ProductFileGroup::getNewInstance($this->product);
	    $group->setNextPosition();
	    $group->setValueByLang('name', 'en', 'TEST_GROUP');
	    $group->save();
	    
	    $this->assertTrue($group->isExistingRecord());
	    
	    $group->delete();
	    $group->markAsNotLoaded();
	    
	    try 
        { 
            $group->load(); 
            $this->fail(); 
        } 
        catch(Exception $e) 
        { 
            $this->pass(); 
        }
	}
	
	public function testDeleteFileGroupWithFiles()
	{
	    $group = ProductFileGroup::getNewInstance($this->product);
	    $group->setNextPosition();
	    $group->setValueByLang('name', 'en', 'TEST_GROUP');
	    $group->save();
	    
	    file_put_contents('blabla', 'asdsad');
	    $productFile = ProductFile::getNewInstance($this->product, 'blabla', 'movedFile.txt');
	    $productFile->productFileGroup->set($group);
	    $productFile->save();
	    
	    $productFilePath = $productFile->getPath();
	    	    
	    $group->delete();
	    
	    try {
	        $productFile->markAsNotLoaded();
		    $productFile->load();
		    $this->fail();
	    } catch (Exception $e) {
	        $this->pass();
	    }
	    
        $this->assertFalse(is_file($productFilePath));
        
        unlink('blabla');
	}
	
	public function testGetProductGroups()
	{
	    // new product
		$product = Product::getNewInstance($this->rootCategory);
		$product->save();	
	    
	    $groups = array();
	    foreach(range(1, 3) as $i)
	    {
		    $groups[$i] = ProductFileGroup::getNewInstance($product);
		    $groups[$i]->position->set($i);
		    $groups[$i]->setValueByLang('name', 'en', 'TEST_GROUP_' . $i);
		    $groups[$i]->save();
	    }
	    
	    $this->assertEqual(count($groups), ProductFileGroup::getProductGroups($product)->getTotalRecordCount());
	    $i = 1;
	    foreach(ProductFileGroup::getProductGroups($product) as $group)
	    {
	        $this->assertTrue($groups[$i] === $group);
	        $i++;
	    }
	}
	
	
}
?>