<?php
	
	require_once("../../framework/ClassLoader.php");
	
	ClassLoader::mountPath(".", "C:\projects\\K-Shop\\");
	ClassLoader::mountPath("framework", "C:\projects\\framework\\");
	
	ClassLoader::import("framework.request.*");
	ClassLoader::import("framework.request.validator.*");
	
	$validator = new RequestValidator("testFrm", new Request());
	
	$nameField = new RequestElement("name");
	$nameField->addCheck(new IsRequiredCheck("You must fill in catalog name field!"));
	
	$descrField = new RequestElement("description");
	
	$validator->register($nameField);
	$validator->register($descrField);
	
	$validator->process();
?>