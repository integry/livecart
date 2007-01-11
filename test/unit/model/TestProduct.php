<?php

require_once("init.php");

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

$productCategory = Category::getNewInstance(Category::getRootNode());
$productCategory->setValueByLang("name", "en", "Demo category branch");
$productCategory->save();

$product = Product::getNewInstance($productCategory);
$product->setValueByLang("name", "en", "Test product...");
$product->setValue("isEnabled", true);

$product->save();

?>