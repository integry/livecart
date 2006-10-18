<?php

require_once("../../framework/ClassLoader.php");
ClassLoader::mountPath(".", "C:\wamp\\www\\k-shop\\");	
ClassLoader::mountPath("framework", "C:\wamp\\www\\framework\\");

ClassLoader::import("library.activerecord.*");
ClassLoader::import("application.model.user.*");

ClassLoader::import("library.simpletest.unit_tester");
ClassLoader::import("library.simpletest.reporter");

ClassLoader::import("framework.request.*");
ClassLoader::import("library.datagrid.*");

class TestDataGrid extends UnitTestCase {
  
  	function testFilter() {
  	 
	 	$request = new Request();
	 	$request->setValueArray(array('datagrid_filter_0' => 'User.email',
		 							  'datagrid_cond_0' => 'contains',
									  'datagrid_value_0]' => 'den',
									  'datagrid_filter_1' => 'User.creationDate',
									  'datagrid_cond_1' => '>=',
									  'datagrid_value_1' => '2005-05-05'));
	 	
	 	
	 	$arr = $request->toArray();
	 	
	 	$filter = new DataGridFilter($arr);
	  
		$filter->Selector()->addField("User.email", "E-mail");
		$filter->Selector()->addField("User.nickName", "Nick name");
		$filter->Selector()->addField("User.creationDate", "Creation Date");
		
		$filter->getConditions(); 
	 	
	 	
  	}
  	
}


$test = &new TestDataGrid();
$test->run(new HtmlReporter());


?>