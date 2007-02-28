<?php
require_once('../Initialize.php');

echo "<pre>";

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

// create a single value select attribute
$singleSel = SpecField::getNewInstance($productCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SELECTOR);
$singleSel->handle->set('single.sel');
$singleSel->setValueByLang('name', 'en', 'Select one value');
$singleSel->setValueByLang('name', 'lt', 'Pasirinkite viena reiksme');
$singleSel->save();

// create some numeric values for the select
$value1 = SpecFieldValue::getNewInstance($singleSel);
$value1->setValueByLang('value', 'en', '20');
$value1->save();

$value2 = SpecFieldValue::getNewInstance($singleSel);
$value2->setValueByLang('value', 'en', '30');
$value2->save();

// assign the select value to product
$product->setAttributeValue($singleSel, $value1);
$product->save();

// assign a different select value
$product->setAttributeValue($singleSel, $value2);
$product->save();

// create yet another single value select attribute
$anotherSel = SpecField::getNewInstance($productCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SELECTOR);
$anotherSel->setValueByLang('name', 'en', 'Select another value');
$anotherSel->setValueByLang('name', 'lt', 'Pasirinkite kita reiksme');
$anotherSel->save();

// create some numeric values for the select
$avalue1 = SpecFieldValue::getNewInstance($anotherSel);
$avalue1->setValueByLang('value', 'en', '20');
$avalue1->save();

// attempt to assign second selectors value to the first selector
try
{
	$product->setAttributeValue($singleSel, $avalue1);  
}
catch (Exception $e)
{
  	echo 'OK: didn`t let assign value from another selector - ' . $e->getMessage() . '<Br>';
}

// now play nicely and assign the second selector value to second selector and set the first selector value back
$product->setAttributeValue($anotherSel, $avalue1);  
$product->setAttributeValue($singleSel, $value1);
$product->save();

// assign Lithuanian value for the text field
$product->setAttributeValueByLang($textField, 'lt', 'Na, kaip, atrodo, veikia!');
$product->save();

// remove the numeric value altogether
$product->setAttributeValue($numField, NULL);
$product->save();

// changed my mind - I want that value back
$product->setAttributeValue($numField, 222);
$product->save();

// now lets remove that second select value
$product->removeAttribute($anotherSel);
$product->save();

// and set it back immediately
$product->setAttributeValue($anotherSel, $avalue1);  
$product->save();

// create a multiple value select attribute
$multiSel = SpecField::getNewInstance($productCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SELECTOR);
$multiSel->isMultiValue->set(true);
$multiSel->setValueByLang('name', 'en', 'Select multiple values');
$multiSel->setValueByLang('name', 'lt', 'Pasirinkite kelias reiksmes');
$multiSel->save();

$values = array();
for ($k = 0; $k < 5; $k++)
{
  	$inst = SpecFieldValue::getNewInstance($multiSel);
	$inst->setValueByLang('value', 'en', $k);
	$inst->save();
	$values[] = $inst;
}

// assign the multiselect values
$product->setAttributeValue($multiSel, $values[1]);  
$product->setAttributeValue($multiSel, $values[3]); 
$product->save();

// assign one more multiselect value
$product->setAttributeValue($multiSel, $values[2]); 
$product->save();

// remove the first multiselect value
$product->removeAttributeValue($multiSel, $values[1]); 
$product->save();

// try to assign a value from a different selector
try
{
	$product->setAttributeValue($multiSel, $avalue1); 
}
catch (Exception $e)
{
  	echo 'OK: didn`t let assign multi-value from another selector - ' . $e->getMessage() . '<Br>';
}

// remove the multiselect value altogether
$product->removeAttribute($multiSel);
$product->save();

// create a multiple text value select attribute
$multiText = SpecField::getNewInstance($productCategory, SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_SELECTOR);
$multiText->isMultiValue->set(true);
$multiText->setValueByLang('name', 'en', 'Select multiple TEXT values');
$multiText->setValueByLang('name', 'lt', 'Pasirinkite kelias TEKSTO reiksmes');
$multiText->save();


$inst = SpecFieldValue::getNewInstance($multiText);
$inst->setValueByLang('value', 'en', 'First value');
$inst->setValueByLang('value', 'lt', 'Pirma reiksme');
$inst->save();

$product->setAttributeValue($multiText, $inst); 

$inst = SpecFieldValue::getNewInstance($multiText);
$inst->setValueByLang('value', 'en', 'Second value');
$inst->setValueByLang('value', 'lt', 'Antra reiksme');
$inst->save();


$product->setAttributeValue($multiText, $inst); 

ActiveRecord::removeFromPool($product);

$product = Product::getInstanceByID($product->getID(), true);

// prices
$product->loadSpecification();
foreach (Store::getInstance()->getCurrencyArray() as $currency)
{
	$product->setPrice($currency, 111);
}



$arr = $product->toArray();
print_r($arr);


//ActiveRecordModel::commit();
ActiveRecordModel::rollback();

echo "OK\n<br/>";
echo "</pre>";
exit;
?>