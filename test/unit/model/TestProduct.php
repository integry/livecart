<?php

echo "<pre>";
require_once("init.php");

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

ActiveRecordModel::beginTransaction();

// create a new category
$productCategory = Category::getNewInstance(Category::getRootNode());
$productCategory->setValueByLang("name", "en", "Demo category branch");
$productCategory->save();

// create a product without attributes
$product = Product::getNewInstance($productCategory);
$product->setValueByLang("name", "en", "Test product...");
$product->setValueByLang("name", "lt", "Bandomasis produktas");
$product->setFieldValue("isEnabled", true);
$product->save();

// create some attributes
$numField = SpecField::getNewInstance($productCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SIMPLE);
$numField->handle->set('numeric.field');
$numField->setValueByLang('name', 'en', 'This would be a numeric field');
$numField->setValueByLang('name', 'lt', 'Cia galima rasyt tik skaicius');
$numField->save();

$textField = SpecField::getNewInstance($productCategory, SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_SIMPLE);
$textField->handle->set('text.field');
$textField->setValueByLang('name', 'en', 'Here goes some free text');
$textField->setValueByLang('name', 'lt', 'Cia bet ka galima irasyt');
$textField->save();

$product->setAttributeValue($numField, 666);
$product->setAttributeValue($textField, array('en' => 'We`re testing here'));

// assign attribute values for product
$product->save();

// modify an attribute
$product->setAttributeValue($numField, 777);
$product->save();

/*
$productSet = ActiveRecord::getRecordSet("Product", new ARSelectFilter());
print_r($productSet->toArray());

$filterSet = Filter::getRecordSetArray(new ARSelectFilter());

print_r($filterSet);
*/

ActiveRecordModel::commit();

echo "OK\n<br/>";
echo "</pre>";

?>