<?php

	require_once("../../../../framework/ClassLoader.php");

	ClassLoader::mountPath(".", "C:\\projects\\livecart\\");
	ClassLoader::import("application.model.ActiveRecordModel");
	ClassLoader::import("application.model.system.*");
	ClassLoader::import("application.model.category.*");
	ClassLoader::import("application.model.product.*");
	ClassLoader::import("library.activerecord.ActiveRecord");

	ActiveRecordModel::setDSN("mysql://root@192.168.1.6/livecart_test");

	$product = ActiveRecordModel::getInstanceByID("Product", 1, Product::LOAD_DATA);
	$prodSpec = new ProductSpecification($product);

	$prodCategory = $product->category->get();
	$specFields = $prodCategory->getSpecificationFieldSet();

	$values = $specFields->get(1)->getValueArray();
	//$prodSpec->setProperty($specFields->get(0), );

	echo "<pre>";
	print_r($specFields->toArray());
	print_r($values);
	echo "</pre>";


	echo "Done!";
?>