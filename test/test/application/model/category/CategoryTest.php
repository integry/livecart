<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *
 * @package test.model.category
 * @author Integry Systems
 */
class CategoryTest extends LiveCartTest
{
	/**
	 * Root category
	 * @var Category
	 */
	private $root;

	public function __construct()
	{
		parent::__construct('Testing categories');
	}

	public function getUsedSchemas()
	{
		return array(
			'Category'
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->root = Category::getInstanceByID(ActiveTreeNode::ROOT_ID, true);
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
		Category::reindex();
		$this->root->reload();

		// Get root node info, before it is modified
		$rootLft = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$rootRgt = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$rootID = $this->root->getID();

		// Create new category
		$newCategory = Category::getNewInstance($this->root);
		$newCategory->setValueByLang("name", 'en', 'TEST ' . rand(1, 1000));
		$newCategory->save();
		$this->assertTrue($newCategory->isExistingRecord());

		// Check if rgt and lft fields are calculated properly
		$this->root->reload();
		$this->assertEqual($newCategory->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootRgt);
		$this->assertEqual($newCategory->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt + 1);
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), 1);
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt + 2);

		// Check parrent id
		$this->assertEqual($newCategory->getFieldValue(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->getID(), $rootID);

		// Reload and check again
		$this->root->reload();
		$newCategory->reload();

		// Check
		$this->assertEqual($newCategory->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootRgt);
		$this->assertEqual($newCategory->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt + 1);
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), 1);
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt + 2);
		$this->assertEqual($newCategory->getFieldValue(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->getID(), $rootID);
	}

	public function testDeleteCategory()
	{
		ActiveRecord::clearPool();

		$startingPositions = array();
		$nodes = $this->root->getChildNodes(false, true);
		$nodes->add($this->root);

		$this->root->reload();
		foreach($nodes as $category)
		{
			$startingPositions[$category->getID()] = array(
				'lft'	 => $category->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME),
				'rgt'	 => $category->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME),
				'parent'  => $category == $this->root ? '0' : $category->getFieldValue(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->getID(),
			);
		}

		// new node
		$newCategory = Category::getNewInstance($this->root);
		$newCategory->setValueByLang("name", 'en', 'NEWNODE');
		$newCategory->save();

		// nested nodes
		$nestedNodes = array();
		$lastNode = $newCategory;
		foreach(array() as $i)
		{
			$nestedNodes[$i] = Category::getNewInstance($lastNode);
			$nestedNodes[$i]->setValueByLang("name", 'en', 'TEST ' . rand(1, 1000));
			$nestedNodes[$i]->save();
			$lastNode = $nestedNodes[$i];
		}
		$newCategory->reload();

		// Delete child node
		$newCategory->delete();
		$this->assertFalse($newCategory->isExistingRecord());
		$this->assertFalse($newCategory->isLoaded());

		// Check to see if everything is back to starting values
		$activeTreeNodes = ActiveRecord::retrieveFromPool(get_class($newCategory));
  		foreach($activeTreeNodes as $category)
		{
			try
			{
				$category->reload();
			}
			catch (ARNotFoundException $e)
			{
				continue;
			}

			if (!$category->getID())
			{
				continue;
			}

			$this->assertEqual($category->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $startingPositions[$category->getID()]['lft']);
			$this->assertEqual($category->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $startingPositions[$category->getID()]['rgt']);
			if (!$category->isRoot())
			{
				$this->assertEqual($category->getFieldValue(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->getID(), $startingPositions[$category->getID()]['parent']);
			}
		}
	}

	public function testUpdateCategory()
	{
		$newCategory = Category::getNewInstance($this->root);
		$newCategory->setValueByLang("name", 'en', "New Category");
		$newCategory->save();
		$categoryID = $newCategory->getID();

		$newCategory->reload();

		// reload root and check to see if rgt and lft didn't change
		$rootRgt = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$rootLft = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);

		$this->root->reload();

		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt);
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft);

		// New category rgt should be equal to it's parent's (rgt + 1)
		$this->assertTrue($newCategory->isLoaded());
		$this->assertEqual($newCategory->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME) + 1, $newCategory->getField(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->get()->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));
	}

	public function testMoveCategoryBetweenSiblings()
	{
		$this->root->reload();

		$newCategories = array(0 => null);
		foreach(range(1, 4) as $i)
		{
			$newCategories[$i] = Category::getNewInstance($this->root);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->save();
			$this->assertTrue($newCategories[$i]->isExistingRecord());
		}

		$this->root->reload();
		$rootRgt = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$rootLft = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);

		foreach($newCategories as $key => $category)
		{
			$newCategories[2]->moveTo($this->root, $category);

			$this->root->reload();

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
			$newCategories[$i]->save();
			$lastCategory = $newCategories[$i];
		}

		$lastCategory = $this->root;
		foreach(range(4, 6) as $i)
		{
			$newCategories[$i] = Category::getNewInstance($lastCategory);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->save();
			$lastCategory = $newCategories[$i];
		}

		$lastCategory = $this->root;
		foreach(range(7, 9) as $i)
		{
			$newCategories[$i] = Category::getNewInstance($lastCategory);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->save();
			$lastCategory = $newCategories[$i];
		}

		$lastCategory = $this->root;
		foreach(range(10, 12) as $i)
		{
			$newCategories[$i] = Category::getNewInstance($lastCategory);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->save();
			$lastCategory = $newCategories[$i];
		}

		$startingPositions = array();
		foreach($newCategories as $category)
		{
			if(!$category) continue;
			$startingPositions[$category->getID()] = array(
				'lft'	 => $category->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME),
				'rgt'	 => $category->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME),
				'parent'  => $category->getFieldValue(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->getID(),
			);
		}

		$rootRgt = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$rootLft = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);

		// reload root
		$this->root->reload();

		// Make sure everything is created and left and right values are valid
//		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt);
//		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft);
		$parentCatRgt = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$parentCatLft = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$targetCatRgt = $newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$targetCatLft = $newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);

		// Move one branch inside another
		$newCategories[4]->moveTo($newCategories[1]);

		$parentCatRgtAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$parentCatLftAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$targetCatRgtAfter = $newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$targetCatLftAfter = $newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$rootRgtAfter = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$rootLftAfter = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);

		// reload target, parent and root
		$newCategories[1]->reload();
		$newCategories[4]->reload();

		// Check if all rgt and lft in database are the same as in objects
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgtAfter);
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLftAfter);
		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgtAfter);
		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLftAfter);
		$this->assertEqual($newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $targetCatRgtAfter);
		$this->assertEqual($newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $targetCatLftAfter);

		$this->root->reload();

		// Check if all lft and rgt are valid
//		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt);
//		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft);

//		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 6);
//		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLft );
//		$this->assertEqual($newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 6 - 1);
//		$this->assertEqual($newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatRgt);

		$parentCatRgt = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$parentCatLft = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$targetCatRgt = $newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$targetCatLft = $newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);

		// move another branch inside
		$newCategories[7]->moveTo($newCategories[1]);

		$parentCatRgtAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$parentCatLftAfter = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$targetCatRgtAfter = $newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$targetCatLftAfter = $newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$rootRgtAfter = $this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$rootLftAfter = $this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);

		// reload target, parent and root
		$newCategories[1]->reload();
		$newCategories[7]->reload();
		$this->root->reload();

		// Check if all rgt and lft in database are the same as in objects
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgtAfter);
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLftAfter);

		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgtAfter);
		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLftAfter);
		$this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $targetCatRgtAfter);
		$this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $targetCatLftAfter);

		// Check if all lft and rgt are valid
/*
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $rootRgt);
		$this->assertEqual($this->root->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $rootLft);
		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 6);
		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLft );
		$this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 6 - 1);
		$this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatRgt);
*/
		// Move category to another branch before node
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
		$newCategories[1]->reload();
		$newCategories[7]->reload();
		$newCategories[11]->reload();

		// Check if all rgt and lft in database are the same as in objects
		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgtAfter);
		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLftAfter);
		$this->assertEqual($newCategories[11]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $targetCatRgtAfter);
		$this->assertEqual($newCategories[11]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $targetCatLftAfter);
		$this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $beforeCatRgtAfter);
		$this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $beforeCatLftAfter);

		// Check if all lft and rgt are valid
/*
		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $parentCatRgt + 4);
		$this->assertEqual($newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $parentCatLft );
		$this->assertEqual($newCategories[11]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $beforeCatLft + 4 - 1);
		$this->assertEqual($newCategories[11]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $beforeCatLft);
		$this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $beforeCatRgt + 4);
		$this->assertEqual($newCategories[7]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $beforeCatLft + 4);
*/
		//  Put all categories back to their starting positions
		$newCategories[11]->moveTo($newCategories[10]);
		$newCategories[4]->moveTo($this->root);
		$newCategories[7]->moveTo($this->root);
		$newCategories[10]->moveTo($this->root);
		foreach($newCategories as $category)
		{
			if(!$category) continue;

			//$this->assertEqual($category->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME), $startingPositions[$category->getID()]['lft']);
			//$this->assertEqual($category->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME), $startingPositions[$category->getID()]['rgt']);
			$this->assertEqual($category->getFieldValue(ActiveTreeNode::PARENT_NODE_FIELD_NAME)->getID(), $startingPositions[$category->getID()]['parent']);
		}
	}

	public function testMoveCategoryUpAndDown()
	{
		$newCategories = array();

		$newCategories[0] = Category::getNewInstance($this->root);
		$newCategories[0]->setValueByLang("name", 'en', "New Category " . 0 );
		$newCategories[0]->save();

		foreach(range(1, 4) as $i)
		{
			$newCategories[$i] = Category::getNewInstance($newCategories[0]);
			$newCategories[$i]->setValueByLang("name", 'en', "New Category " . $i );
			$newCategories[$i]->save();
		}


		// Move right
		$lft = $newCategories[3]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$rgt = $newCategories[3]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$nextLft = $newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$nextRgt = $newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);

		$newCategories[3]->moveRight();

		$this->assertEqual($lft + 2, $newCategories[3]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($rgt + 2, $newCategories[3]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));
		$this->assertEqual($nextLft - 2, $newCategories[4]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($nextRgt - 2, $newCategories[4]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));

		// Move right when the node is already a last child
		$lft = $newCategories[3]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$rgt = $newCategories[3]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);

		$newCategories[3]->moveRight();

		$this->assertEqual($lft, $newCategories[3]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($rgt, $newCategories[3]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));

		// Move left
		$lft = $newCategories[2]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$rgt = $newCategories[2]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$prevLft = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$prevRgt = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);

		$newCategories[2]->moveLeft();

		$this->assertEqual($lft - 2, $newCategories[2]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($rgt - 2, $newCategories[2]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));
		$this->assertEqual($prevLft + 2, $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($prevRgt + 2, $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));

		// Move left when the node is already a first child
		$lft = $newCategories[2]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$rgt = $newCategories[2]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);

		$newCategories[2]->moveLeft();

		$this->assertEqual($lft, $newCategories[2]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($rgt, $newCategories[2]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));

		// Circle when moving left
		$lft = $newCategories[2]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$rgt = $newCategories[2]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$nextLft = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$nextRgt = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);

		$newCategories[2]->moveLeft(ActiveTreeNode::MOVE_CIRCLE);

		$this->assertEqual($lft + 6, $newCategories[2]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($rgt + 6, $newCategories[2]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));
		$this->assertEqual($nextLft - 2, $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($nextRgt - 2, $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));


		// Circle when moving right
		$lft = $newCategories[2]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$rgt = $newCategories[2]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);
		$nextLft = $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME);
		$nextRgt = $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME);

		$newCategories[2]->moveRight(ActiveTreeNode::MOVE_CIRCLE);

		$this->assertEqual($lft - 6, $newCategories[2]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($rgt - 6, $newCategories[2]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));
		$this->assertEqual($nextLft + 2, $newCategories[1]->getFieldValue(ActiveTreeNode::LEFT_NODE_FIELD_NAME));
		$this->assertEqual($nextRgt + 2, $newCategories[1]->getFieldValue(ActiveTreeNode::RIGHT_NODE_FIELD_NAME));
	}

	public function testActiveProductCountWhenChangingCategoryAvailability()
	{
		$this->root->reload();

		$rootActiveProductCount = $this->root->activeProductCount->get();
		$rootAotalProductCount = $this->root->totalProductCount->get();
		$rootAvailableProductCount = $this->root->availableProductCount->get();

		$subCategory = Category::getNewInstance($this->root);
		$subCategory->setValueByLang("name", 'en', "New Category 1" );
		$subCategory->isEnabled->set(false);
		$subCategory->activeProductCount->set(1);
		$subCategory->totalProductCount->set(1);
		$subCategory->availableProductCount->set(1);
		$subCategory->save();

		$this->root->reload();

		$this->assertEqual($rootActiveProductCount, $this->root->activeProductCount->get());

		$subCategory->isEnabled->set(true);
		$subCategory->save();

		$this->root->reload();

		$this->assertEqual($rootActiveProductCount + 1, $this->root->activeProductCount->get());
	}
}

?>