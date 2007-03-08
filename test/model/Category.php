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
	    $_POST['SHOW_QUERIES'] = false;
	    
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
	    $this->root->markAsNotLoaded();
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
        
        $newCategory->markAsNotLoaded();
        $this->assertFalse($newCategory->isLoaded());
        $newCategory->load();
        $this->assertTrue($newCategory->isLoaded());
        
        $name = $newCategory->name->get();
        $this->assertEqual($name['en'], "New Category " . $categoryID, "Category's name wasn't properly saved");
        
        $handle = $newCategory->handle->get();
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
        $newCategory->markAsNotLoaded();
        $this->assertTrue($newCategory->isExistingRecord());
        $this->assertFalse($newCategory->isLoaded());
        $newCategory->load();
        
        // reload root and check to see if rgt and lft didn't change
	    $rootRgt = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $rootLft = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $this->root->markAsNotLoaded();
	    $this->root->load();
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt);
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft);
        
        // New category rgt should be equal to it's parent's (rgt + 1)
        $this->assertTrue($newCategory->isLoaded());
        $this->assertEqual($newCategory->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME) + 1, $newCategory->getField(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->get()->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));
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

	    $rootRgt = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $rootLft = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    
	    // reload root
	    $this->root->markAsNotLoaded();
	    $this->root->load();
	    
	    // Make sure everything is created and left and right values are valid
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt);
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft);
	    
	    foreach($newCategories as $key => $category)
	    {
	        $newCategories[2]->moveTo($this->root, $category);
		    
		    // reload root
		    $this->root->markAsNotLoaded();
		    $this->root->load();
		    
		    // Make sure one category is last child, root lft and rgt shouldn't change
		    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt, "Root rgt should be the same when moving category 3 to ".($category ? $key : 'null')." out of 4 ([".($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME))."] and [$rootRgt])");
		    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft, "Root lft should be the same when moving category 3 to ".($category ? $key : 'null')." out of 4 ([".($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME))."] and [$rootLft])");
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
			$newCategories[$i]->setFieldValue("handle", "TEST.CATEGORY." . $i);
	        $newCategories[$i]->save();	        
	        $lastCategory = $newCategories[$i];
	    }
	    	    
	    $lastCategory = $this->root;
	    foreach(range(4, 6) as $i)
	    {
		    $newCategories[$i] = Category::getNewInstance($lastCategory);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->setFieldValue("handle", "TEST.CATEGORY." . $i);
	        $newCategories[$i]->save();
	        $lastCategory = $newCategories[$i];
	    }
	       
	    $lastCategory = $this->root;
	    foreach(range(7, 9) as $i)
	    {
		    $newCategories[$i] = Category::getNewInstance($lastCategory);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->setFieldValue("handle", "TEST.CATEGORY." . $i);
	        $newCategories[$i]->save();
	        $lastCategory = $newCategories[$i];
	    }
	       
	    $lastCategory = $this->root;
	    foreach(range(10, 12) as $i)
	    {
		    $newCategories[$i] = Category::getNewInstance($lastCategory);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->setFieldValue("handle", "TEST.CATEGORY." . $i);
	        $newCategories[$i]->save();
	        $lastCategory = $newCategories[$i];
	    }

	    $startingPositions = array();
	    foreach($newCategories as $category)
	    {
	        if(!$category) continue;
	        $startingPositions[$category->getID()] = array(
				'lft'     => $category->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME),
				'rgt'     => $category->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME),
				'parent'  => $category->getFieldValue(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->getID(),
	        );
	    }
	    
	    $rootRgt = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $rootLft = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    
	    // reload root
	    $this->root->markAsNotLoaded();
	    $this->root->load();
	    
	    // Make sure everything is created and left and right values are valid
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt);
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft);
	    $parentCatRgt = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $parentCatLft = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $targetCatRgt = $newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $targetCatLft = $newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    
	    /**
	     * Move one branch inside another
	     */ 
	    $newCategories[4]->moveTo($newCategories[1]);
	    
	    $parentCatRgtAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $parentCatLftAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $targetCatRgtAfter = $newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $targetCatLftAfter = $newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $rootRgtAfter = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $rootLftAfter = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    
	    // reload target, parent and root
	    $newCategories[1]->markAsNotLoaded();
	    $newCategories[1]->load();
	    $newCategories[4]->markAsNotLoaded();
	    $newCategories[4]->load();

	    // Check if all rgt and lft in database are the same as in objects
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgtAfter);
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLftAfter);
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgtAfter);
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLftAfter);
	    $this->assertEqual($newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $targetCatRgtAfter);
	    $this->assertEqual($newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $targetCatLftAfter);
	    
	    $this->root->markAsNotLoaded();
	    $this->root->load();
	    
	    // Check if all lft and rgt are valid
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt);
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft);

	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 6);
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLft );
	    $this->assertEqual($newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 6 - 1);
	    $this->assertEqual($newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatRgt);
	    
	    $parentCatRgt = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $parentCatLft = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $targetCatRgt = $newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $targetCatLft = $newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
    
	    /**
	     * move another branch inside
	     */ 
	    $newCategories[7]->moveTo($newCategories[1]);
	    
	    $parentCatRgtAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $parentCatLftAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $targetCatRgtAfter = $newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $targetCatLftAfter = $newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $rootRgtAfter = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $rootLftAfter = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    
	    // reload target, parent and root
	    $newCategories[1]->markAsNotLoaded();
	    $newCategories[1]->load();
	    $newCategories[7]->markAsNotLoaded();
	    $newCategories[7]->load();
	    $this->root->markAsNotLoaded();
	    $this->root->load();
	    
	    // Check if all rgt and lft in database are the same as in objects
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgtAfter);
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLftAfter);
	    
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgtAfter);
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLftAfter);
	    $this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $targetCatRgtAfter);
	    $this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $targetCatLftAfter);
	    
	    // Check if all lft and rgt are valid
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt);
	    $this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft);
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 6);
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLft );
	    $this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 6 - 1);
	    $this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatRgt);
	    
	    /**
	     * Move category to another branch before node
	     */ 	    
	    $parentCatRgt = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $parentCatLft = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $targetCatRgt = $newCategories[11]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $targetCatLft = $newCategories[11]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $beforeCatRgt = $newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $beforeCatLft = $newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);

	    $newCategories[11]->moveTo($newCategories[1], $newCategories[7]);
	    
	    $parentCatRgtAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $parentCatLftAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $targetCatRgtAfter = $newCategories[11]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $targetCatLftAfter = $newCategories[11]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    $beforeCatRgtAfter = $newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
	    $beforeCatLftAfter = $newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
	    
	    // reload target, parent and before
	    $newCategories[1]->markAsNotLoaded();
	    $newCategories[1]->load();
	    $newCategories[7]->markAsNotLoaded();
	    $newCategories[7]->load();
	    $newCategories[11]->markAsNotLoaded();
	    $newCategories[11]->load();
	    
	    // Check if all rgt and lft in database are the same as in objects
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgtAfter);
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLftAfter);
	    $this->assertEqual($newCategories[11]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $targetCatRgtAfter);
	    $this->assertEqual($newCategories[11]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $targetCatLftAfter);
	    $this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $beforeCatRgtAfter);
	    $this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $beforeCatLftAfter);
	    	    
	    // Check if all lft and rgt are valid
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 4);
	    $this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLft );
	    $this->assertEqual($newCategories[11]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $beforeCatLft + 4 - 1);
	    $this->assertEqual($newCategories[11]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $beforeCatLft);
	    $this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $beforeCatRgt + 4);
	    $this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $beforeCatLft + 4);

	    /**
	     * Put all categories back to their starting positions
	     */	    
	    $newCategories[11]->moveTo($newCategories[10]);
	    $newCategories[4]->moveTo($this->root);
	    $newCategories[7]->moveTo($this->root);
	    $newCategories[10]->moveTo($this->root);    
	    foreach($newCategories as $category)
	    {
	        if(!$category) continue;
	        
	        $this->assertEqual($category->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $startingPositions[$category->getID()]['lft']);
	        $this->assertEqual($category->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $startingPositions[$category->getID()]['rgt']);
	        $this->assertEqual($category->getFieldValue(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->getID(), $startingPositions[$category->getID()]['parent']);
	    }
	}
}

?>