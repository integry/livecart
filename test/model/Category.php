<?php
require_once('../Initialize.php');

ClassLoader::import("application.model.category.Category");

class TestCategory extends UnitTest
{
	/**
	 * Root category
	 * @var Category
	 */
    private $root;
    
    private $autoIncrementNumber;
    
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
	}
    
    public function setUp()
	{
	    ActiveRecordModel::beginTransaction();	
		
	    $this->root = Category::getInstanceByID(ActiveTreeNode::ROOT_ID);
	    
	    $newCategory = Category::getNewInstance($this->root);
		$newCategory->setValueByLang("name", 'en', "dump");
		$newCategory->setFieldValue("handle", "dump");
        $newCategory->save();
        $newCategory->delete();
        
	    $this->autoIncrementNumber = $newCategory->getID();
	}
	
	function tearDown()
	{
	    ActiveRecord::removeFromPool($this->root);
	    ActiveRecordModel::rollback();		
	    $this->db->executeUpdate("ALTER TABLE Category AUTO_INCREMENT=" . $this->autoIncrementNumber);
	}
	
	public function testRootCategoryIsCategory()
	{
	    $this->assertIsA($this->root, 'Category');
	}
	
	public function testCreatedCategoryIsCategory()
	{
		$newCategory = Category::getNewInstance($this->root);
		$this->assertIsA($newCategory, 'Category');
	}
	
	public function testCreateCategory()
	{
		$newCategory = Category::getNewInstance($this->root);
		$newCategory->setValueByLang("name", 'en', 'dump' );
		$newCategory->save();
		$categoryID = $newCategory->getID();
        $this->assertIsA($categoryID, 'integer');
		
		$newCategory->setValueByLang("name", 'en', "New Category " . $newCategory->getID() );
		$newCategory->setFieldValue("handle", "new.category." . $newCategory->getID() );
        $newCategory->save();
        $this->assertTrue($newCategory->isExistingRecord());
        
        ActiveRecord::removeFromPool($newCategory);
        $this->assertFalse($newCategory->isLoaded());
        unset($newCategory);
        
        $category = Category::getInstanceByID($categoryID, true);
        $this->assertTrue($category->isLoaded());
        
        $name = $category->name->get();
        $this->assertEqual($name['en'], "New Category " . $categoryID, "Category's name wasn't properly saved");
        
        $handle = $category->handle->get();
        $this->assertEqual($handle, "new.category." . $categoryID, "Category's handle wasn't properly saved");
	}

	public function testUpdateCategory()
	{
	    $newCategory = Category::getNewInstance($this->root);
		$newCategory->setValueByLang("name", 'en', "New Category");
		$newCategory->setFieldValue("handle", "new.category");
        $newCategory->save();
        $categoryID = $newCategory->getID();
        
        // Reload category
        ActiveRecord::removeFromPool($newCategory);
        $category = Category::getInstanceByID($categoryID);
        $this->assertTrue($category->isExistingRecord());
        $this->assertFalse($category->isLoaded());
        $category->load();
        
        // reload root and check to see if rgt and lft didn't change
	    $rootRgt = $this->root->rgt->get();
	    $rootLft = $this->root->lft->get();
	    ActiveRecord::removeFromPool($this->root);
	    $this->root = Category::getInstanceByID(ActiveTreeNode::ROOT_ID, true);
	    $this->assertEqual($this->root->rgt->get(), $rootRgt);
	    $this->assertEqual($this->root->lft->get(), $rootLft);
        
        // New category rgt should be equal to it's parent's (rgt + 1)
        $this->assertTrue($category->isLoaded());
        $this->assertEqual($category->rgt->get() + 1, $category->getField(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->get()->rgt->get());
	}
	
	public function testMoveCategoryBetweenSiblings()
	{
	    $newCategories = array(0 => null);
	    foreach(range(1, 4) as $i)
	    {
		    $newCategories[$i] = Category::getNewInstance($this->root);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->setFieldValue("handle", "new.category." . $i);
	        $newCategories[$i]->save();
	        $this->assertTrue($newCategories[$i]->isExistingRecord());
	    }

	    $rootRgt = $this->root->rgt->get();
	    $rootLft = $this->root->lft->get();
	    
	    // reload root
	    ActiveRecord::removeFromPool($this->root);
	    $this->root->load();
	    
	    // Make sure everything is created and left and right values are valid
	    $this->assertEqual($this->root->rgt->get(), $rootRgt);
	    $this->assertEqual($this->root->lft->get(), $rootLft);
	    
	    foreach($newCategories as $key => $category)
	    {
//		    if(!$category) continue;
	        $newCategories[2]->moveTo($this->root, $category);
		    
		    // reload root
		    ActiveRecord::removeFromPool($this->root);
		    $this->root->load();
		    
		    // Make sure one category is last child, root lft and rgt shouldn't change
		    $this->assertEqual($this->root->rgt->get(), $rootRgt, "Root rgt should be the same when moving category 3 to ".($category ? $key : 'null')." out of 4 ([".($this->root->rgt->get())."] and [$rootRgt])");
		    $this->assertEqual($this->root->lft->get(), $rootLft, "Root lft should be the same when moving category 3 to ".($category ? $key : 'null')." out of 4 ([".($this->root->lft->get())."] and [$rootLft])");
	    }
	}
	
	public function testMoveCategoryBetweenBranches()
	{
	    $newCategories = array(0 => null);
	    $lastCategory = $this->root;
	    foreach(range(1, 3) as $i)
	    {
		    $newCategories[$i] = Category::getNewInstance($lastCategory);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->setFieldValue("handle", "new.category." . $i);
	        $newCategories[$i]->save();
	        $lastCategory = $newCategories[$i];
	    }
	    
	    $lastCategory = $this->root;
	    foreach(range(4, 6) as $i)
	    {
		    $newCategories[$i] = Category::getNewInstance($lastCategory);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->setFieldValue("handle", "new.category." . $i);
	        $newCategories[$i]->save();
	        $lastCategory = $newCategories[$i];
	    }
	       
	    $lastCategory = $this->root;
	    foreach(range(7, 9) as $i)
	    {
		    $newCategories[$i] = Category::getNewInstance($lastCategory);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->setFieldValue("handle", "new.category." . $i);
	        $newCategories[$i]->save();
	        $lastCategory = $newCategories[$i];
	    }

	    $rootRgt = $this->root->rgt->get();
	    $rootLft = $this->root->lft->get();
	    
	    // reload root
	    ActiveRecord::removeFromPool($this->root);
	    $this->root->load();
	    
	    // Make sure everything is created and left and right values are valid
	    $this->assertEqual($this->root->rgt->get(), $rootRgt);
	    $this->assertEqual($this->root->lft->get(), $rootLft);
	    $parentCatRgt = $newCategories[1]->rgt->get();
	    $parentCatLft = $newCategories[1]->lft->get();
	    $targetCatRgt = $newCategories[4]->rgt->get();
	    $targetCatLft = $newCategories[4]->lft->get();
	    
	    // move one branch inside another
	    $newCategories[4]->moveTo($newCategories[1]);
	    
	    $parentCatRgtAfter = $newCategories[1]->rgt->get();
	    $parentCatLftAfter = $newCategories[1]->lft->get();
	    $targetCatRgtAfter = $newCategories[4]->rgt->get();
	    $targetCatLftAfter = $newCategories[4]->lft->get();
	    $rootRgtAfter = $this->root->rgt->get();
	    $rootLftAfter = $this->root->lft->get();
	    
	    // reload target, parent and root
	    ActiveRecord::removeFromPool($newCategories[1]);
	    $newCategories[1]->load();
	    ActiveRecord::removeFromPool($newCategories[4]);
	    $newCategories[4]->load();
	    
	    // Check if all rgt and lft in database are the same as in objects
	    $this->assertEqual($this->root->rgt->get(), $rootRgtAfter);
	    $this->assertEqual($this->root->lft->get(), $rootLftAfter);
	    $this->assertEqual($newCategories[1]->rgt->get(), $parentCatRgtAfter);
	    $this->assertEqual($newCategories[1]->lft->get(), $parentCatLftAfter);
	    $this->assertEqual($newCategories[4]->rgt->get(), $targetCatRgtAfter);
	    $this->assertEqual($newCategories[4]->lft->get(), $targetCatLftAfter);
	    
	    ActiveRecord::removeFromPool($this->root);
	    $this->root->load();
	    
	    // Check if all lft and rgt are valid
	    $this->assertEqual($this->root->rgt->get(), $rootRgt);
	    $this->assertEqual($this->root->lft->get(), $rootLft);

	    $this->assertEqual($newCategories[1]->rgt->get(), $parentCatRgt + 6);
	    $this->assertEqual($newCategories[1]->lft->get(), $parentCatLft );
	    $this->assertEqual($newCategories[4]->rgt->get(), $parentCatRgt + 6 - 1);
	    $this->assertEqual($newCategories[4]->lft->get(), $parentCatRgt);
	}
}

?>