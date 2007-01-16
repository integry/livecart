<?php

echo "<pre>";
require_once("init.php");

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

$productCategory = Category::getNewInstance(Category::getRootNode());
$productCategory->setValueByLang("name", "en", "Demo category branch");
$productCategory->save();

$product = Product::getNewInstance($productCategory);
$product->setValueByLang("name", "en", "Test product...");
$product->setValueByLang("name", "lt", "Bandomasis produktas");
$product->setFieldValue("isEnabled", true);

$product->save();


$productSet = ActiveRecord::getRecordSet("Product", new ARSelectFilter());
print_r($productSet->toArray());

$filterSet = Filter::getRecordSetArray(new ARSelectFilter());

print_r($filterSet);

echo "OK\n<br/>";
echo "</pre>";

?>