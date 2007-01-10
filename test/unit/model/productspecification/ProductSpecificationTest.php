<?php

	require_once("../init.php");

	$product = ActiveRecordModel::getInstanceByID("Product", 1, Product::LOAD_DATA);
	$prodSpec = new ProductSpecification($product);

	$prodCategory = $product->category->get();
	$specFields = $prodCategory->getSpecificationFieldSet();

	$values = $specFields->get(1)->getValueArray();

	echo "<pre>";
	print_r($specFields->toArray());
	print_r($values);
	echo "</pre>";

	echo "Done!";
?>