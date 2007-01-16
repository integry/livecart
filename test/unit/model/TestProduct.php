<?php

echo "<pre>";
require_once("init.php");

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

/*
$productCategory = Category::getNewInstance(Category::getRootNode());
$productCategory->setValueByLang("name", "en", "Demo category branch");
$productCategory->save();

$product = Product::getNewInstance($productCategory);
$product->setValueByLang("name", "en", "Test product...");
$product->setFieldValue("isEnabled", true);

$product->save();
*/

$productSet = ActiveRecord::getRecordSet("Product", new ARSelectFilter());
print_r($productSet->toArray());

$filterSet = Filter::getRecordSet(new ARSelectFilter());

print_r($filterSet->toArray());

echo "OK\n<br/>";
echo "</pre>";

?>