<?php

require_once("../../framework/ClassLoader.php");
ClassLoader::mountPath(".", "C:\wamp\\www\\k-shop\\");	

ClassLoader::import("library.activerecord.ActiveRecord");
ClassLoader::import("application.model.*");
ClassLoader::import("application.model.locale.*");




ClassLoader::import("library.simpletest.unit_tester");
ClassLoader::import("library.simpletest.reporter");

ActiveRecord::setDSN("mysql://root@192.168.1.6/K-shop-test");

class TestLanguage extends UnitTestCase {  
  
  	function ntestSetup() {
  		
  		$ar = array('aaa"aa\\');
  		
  		echo serialize($ar);
  		print_r(unserialize(serialize($ar)));
  		
  		return;
  		$lang = new LanguageSetup('c:\wamp\www\projectengine\application\languages\en\\');	    
	    $lang->setup();	  
		    
	    $loc_en = Locale::GetInstance("en");	    	    
	    $loc_lt = Locale::GetInstance("lt");
	    
	    $this->assertEqual(count($loc_lt->getAllDefinitions()), count($loc_en->getAllDefinitions()));
  	}
  	
  	function ntestAdd(){
	    
	    Languages::add("lt");
	}
  
  	function testLoad() {	    
		  
	  	$loc1 = Locale::getInstance("en");
	  	$loc2 = Locale::getInstance("en");
	  	
	  	$loc3 = Locale::getInstance("lt");  	
	  	
	  	$this->assertEqual($loc1, $loc2);
	  	$this->assertIdentical($loc1, $loc2);
		
		//$this->assertTrue(count($loc1->getAllDefinitions() > 0));
		//$this->assertEqual($loc1->translate('_hello_world'), "Hello World");		
		
		$this->assertTrue(count($loc2->getCountries()));
		$this->assertEqual($loc1->getCountry('lt'), "Lithuania");		
		
		$this->assertEqual($loc3->getCountry('lt'), "Lietuva");
		
		$this->assertTrue(count($loc2->getLanguages()));
		$this->assertEqual($loc1->getLanguage('lt'), "Lithuanian");
		
		$this->assertEqual($loc3->getCurrency('ltl'), "Litas");
	}  
}


$test = &new TestLanguage();
$test->run(new HtmlReporter());


?>