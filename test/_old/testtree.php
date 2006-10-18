<?php

require_once("../../framework/ClassLoader.php");
ClassLoader::mountPath(".", "C:\wamp\\www\\k-shop\\");	


ClassLoader::import("library.activerecord.*");
ClassLoader::import("library.*");

ClassLoader::import("application.model.*");

ClassLoader::import("library.simpletest.unit_tester");
ClassLoader::import("library.simpletest.reporter");

ActiveRecord::setDSN("mysql://root@192.168.1.6/K-shop-test");

class TestTree extends UnitTestCase {
	
	var $food_count = 0;
	var $fruit_count = 0;
	var $banana_count = 0;	
	
	var $metal_count = 0;

	function TestTree() {
  		
  		parent::__construct();
	  	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=windows-1257\">";  	
	}		

	function testGetInsert() {
		
		$db = ActiveRecord::getDbConnection();
				
		ClassLoader::import("library.activerecord.util.generator.*");	    	    
		$gen = ARSQLGenerator::getInstance($db);		    	    	   	
		$db->executeUpdate("DROP TABLE IF EXISTS Menu");	
		$db->executeUpdate($gen->generateTableDDL("Menu"));		  			

		$db->executeUpdate("INSERT INTO Menu SET `lft` = '1', `rgt` = '2', `name` = 'food'");
		$this->food_id = $db->getIdGenerator()->getId();
		
		$db->executeUpdate("INSERT INTO Menu SET `lft` = '3', `rgt` = '4', `name` = 'metal'");		
		$this->metal_id = $db->getIdGenerator()->getId();
			
		$gold = Tree::getNewTreeInstance("Menu", $this->metal_id);			
		$gold->name->Set("gold");
		$gold->Save();
		$this->assertTrue($this->objectTest($gold));
				
		$fruit = Tree::getNewTreeInstance("Menu", $this->food_id);			
		$fruit->name->Set("fruit");
		$fruit->Save();		
	
		$this->fruit_id = $fruit->getId();		
		$this->assertTrue($this->objectTest($fruit));
		
		$apple = Tree::getNewTreeInstance("Menu", $fruit);			
		$apple->name->Set("apple");
		$apple->Save();		
		
		$fruit2 = Tree::getTreeInstanceById("Menu", $fruit->getId());	 					
		$this->assertIdentical($fruit, $fruit2);
		
		$banana = Tree::getNewTreeInstance("Menu", $fruit);			
		$banana->name->Set("apple");
		$banana->Save();		
				
		$banana->name->Set("banana");
		$banana->text->Set("You \'ll like them.");
		$banana->Save();
				
		$green = Tree::getNewTreeInstance("Menu", $banana);		
		$green->name->Set("green");
		$green->text->Set("Oh, shut green banana.");
		$green->Save();
		
		$yellow = Tree::getNewTreeInstance("Menu", $banana);		
		$yellow->name->Set("yellow");	
		$yellow->Save();
				
		$list =	Tree::getParentsList("Menu", $yellow);
	  	$this->assertEqual(count($list), 3);
	
		$food = Tree::getTreeInstanceById("Menu", $this->food_id);
		$this->assertTrue($this->objectTest($food));	
		$this->assertTrue($this->objectTest($fruit));		
	}
	
	function testDelete() {

		$food = Tree::getTreeInstanceById("Menu", $this->food_id);		  	    
	  	$fruit = Tree::getTreeInstanceById("Menu", $this->fruit_id);
  
	  	//$all = Tree::getAllTree("Menu");				  		  	
			  	
	  	$egs = Tree::getNewTreeInstance("Menu", $fruit);		
		$egs->name->Set("egs");	
		$egs->Save();

		$birds = Tree::getNewTreeInstance("Menu", $egs);		
		$birds->name->Set("birds");	
		$birds->Save();		
		
		$crocodiles = Tree::getNewTreeInstance("Menu", $egs);		
		$crocodiles->name->Set("crocodiles");	
		$crocodiles->Save();		
			
		$this->assertTrue($this->objectTest($food));	
		$this->assertTrue($this->objectTest($fruit));

		Tree::delete("Menu", $egs->getId());		
						
		$silver = Tree::getNewTreeInstance("Menu", $food);		
		$silver->name->Set("silver");	
		$silver->Save();
		
		$spoon = Tree::getNewTreeInstance("Menu", $silver);			  	
		$spoon->name->Set("spoon");	
		$spoon->Save();
				
		$houses = Tree::getNewTreeInstance("Menu");		
		$houses->name->set("houses");
		$houses->Save();

		$this->silver_id = $silver->getId();	
		
		$mandarina = Tree::getNewTreeInstance("Menu", $houses);		
		$mandarina->name->set("mandarina");
		$mandarina->Save();

		$orange = Tree::getNewTreeInstance("Menu", $mandarina);		
		$orange->name->set("orange");
		$orange->Save();

		$this->orange_id = $orange->getId();
		$this->mandarina_id = $mandarina->getId();
	}
		
	function testModify() {
	  	
	  	$all = Tree::getAllTree("Menu");	
		    	
	  	$silver = Tree::getTreeInstanceById("Menu", $this->silver_id);		
	  	$mandarina = Tree::getTreeInstanceById("Menu", $this->mandarina_id);		
				
		//$silver->changeParent($this->metal_id);		
		Tree::modifyTreeParent("Menu", $silver, $this->metal_id);		
		
		$this->assertTrue($this->objectTest($silver));	
		$this->assertTrue($this->objectTest(Tree::getTreeInstanceById("Menu", $this->fruit_id)));	
		$this->assertTrue($this->objectTest(Tree::getTreeInstanceById("Menu", $this->food_id)));	
		$this->assertTrue($this->objectTest(Tree::getTreeInstanceById("Menu", $this->metal_id)));	
		
	//	$mandarina->changeParent(Tree::getTreeInstanceById("Menu", 4));			
		Tree::modifyTreeParent("Menu", $mandarina->getId(), 4);		
		
		$mandarina->text->set("Bla bla");
		$mandarina->save();
		
		$this->assertTrue($this->objectTest($mandarina));
		$this->assertTrue($this->objectTest(Tree::getTreeInstanceById("Menu", $this->fruit_id)));	
		$this->assertTrue($this->objectTest(Tree::getTreeInstanceById("Menu", $this->food_id)));	
		$this->assertTrue($this->objectTest(Tree::getTreeInstanceById("Menu", $this->metal_id)));
	
		$all->ShowChildren();
	}
	
	function testList() {
	  	
	  	$list =	Tree::getParentsList("Menu", $this->orange_id);
	  	$this->assertEqual(count($list), 3);	  	
	}
	
	function objectTest($tree) {
	  
	  	$db = ActiveRecord::getDbConnection();
	  
	  	$res = $db->executeQuery("SELECT * FROM Menu WHERE id = ".$tree->getId());
		$res->next();
		
		if ($res->getString('name') != $tree->name->get()) {
		  
		  	return false;
		} else if ($res->getInt('parent') != $tree->parent->get()) {
		  
		  	echo 'Bad parent : expected: '.$tree->parent->get().'. Was: '.$res->getInt('parent')."<br>\n";
		  	return false;
		} else if ($res->getInt('lft') != $tree->lft->get()) {
		  
		  	echo 'Bad left : expected: '.$tree->lft->get().'. Was: '.$res->getInt('lft')."<br>\n";
		  	return false;
		} else if ($res->getInt('rgt') != $tree->rgt->get()) {
		  
		  	echo 'Bad right : expected: '.$tree->rgt->get().'. Was: '.$res->getInt('rgt')."<br>\n";
		  	return false;
		} 
		
		$res = $db->executeQuery("SELECT COUNT(*) AS suma FROM Menu WHERE parent = ".$tree->getId());
		$res->next();
		
		if ($res->getInt('suma') != $tree->getChildrenCount()) {
		 
		 	echo 'Bad children count : expected: '.$tree->getChildrenCount().'. Was: '.$res->getInt('suma')."<br>\n";
		  	return false;
		} 
		
		return true;
	}
	
}  


$test = &new TestTree();
$test->run(new HtmlReporter());











?>