<?php

require_once("../../framework/ClassLoader.php");
ClassLoader::mountPath(".", "C:\wamp\\www\\k-shop\\");	

ClassLoader::import("library.activerecord.ActiveRecord");
ClassLoader::import("application.model.*");
ClassLoader::import("application.model.product.*");

//ClassLoader::import("library.Locale.*");
//ClassLoader::import("library.Locale.Languages");


ClassLoader::import("library.simpletest.unit_tester");
ClassLoader::import("library.simpletest.reporter");

ActiveRecord::setDSN("mysql://root@192.168.1.6/K-shop");

class TestMulti extends UnitTestCase {
  
  	function testSetup() {
	    
	    $this->db = ActiveRecord::getDbConnection();		
		$this->db->executeUpdate("TRUNCATE table Catalog");
		$this->db->executeUpdate("TRUNCATE table CatalogLangData");
	}
  	
  	function testInsert() { 		

		$food = TreeCatalog::getNewTreeInstance();				
		$food->lang("en")->name->set("Food");						
		$food->save();
				
		$fruit = TreeCatalog::getNewTreeInstance($food);			
		$fruit->lang("en")->name->Set("fruit");
		$fruit->Save();	
		
		$apple = TreeCatalog::getNewTreeInstance($fruit->getId());			
		$apple->lang("en")->name->Set("apple");
		$apple->Save();
  	} 	
  	
	function ntestCache() {
	  
	  	$food = TreeCatalog::getTreeInstanceById(1);
	  	$this->assertEqual($food->lang("en")->name->get("Food"), 'Food');	  	
	}
	  
	function testLoad() {
	  	  	
		//METAL ID = 4
		$this->db->executeUpdate("INSERT INTO Catalog SET `lft` = '7', `rgt` = '8'");
		$this->db->executeUpdate("INSERT INTO CatalogLangData SET `catalogID` = '4', `languageID` = 'en', `name` = 'metal'");
		//GOLD ID = 5
		$this->db->executeUpdate("UPDATE Catalog SET lft = lft + 2 WHERE lft >= 8");
		$this->db->executeUpdate("UPDATE Catalog SET rgt = rgt + 2 WHERE rgt >= 8");
		$this->db->executeUpdate("INSERT INTO Catalog SET `parent` = '4', `lft` = '8', `rgt` = '9'");
		$this->db->executeUpdate("INSERT INTO CatalogLangData SET `catalogID` = '5', `languageID` = 'en', `name` = 'gold'");
		
		
				
		$metal = TreeCatalog::getTreeInstanceById(4);
		
		$gold = TreeCatalog::getTreeInstanceById(5);
		$gold = TreeCatalog::getTreeInstanceById(5);
		
		$this->assertEqual($gold->lft->get(), 8);		
		$this->assertEqual($metal->lang("en")->name->get(), "metal");
		$this->assertEqual($gold->lang("en")->name->get(), "gold");	  
		
		
		foreach ($metal as $child) {
		  
			if ($child->getId() == 5) {
			  
			 	$gold = $child;
				$this->assertEqual($gold->lang("en")->name->get(), "gold");
			}
		}

		$gold = TreeCatalog::getTreeInstanceById(5);	
		$gold->lang("en")->description->set("Yellow color gold.");
		$gold->save();			
	}	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	  
	    	
}


$test = &new TestMulti();
$test->run(new HtmlReporter());


?>