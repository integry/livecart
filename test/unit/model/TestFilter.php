<?php

echo "<pre>";
require_once("init.php");

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

$currentCategory = Category::getRootNode();

ActiveRecord::beginTransaction();

// numeric input
$rangeField = SpecField::getNewInstance($currentCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SIMPLE);
$rangeField->save();

// date input
$dateField = SpecField::getNewInstance($currentCategory, SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_DATE);
$dateField->save();

// select single value
$selField = SpecField::getNewInstance($currentCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SELECTOR);
$selField->save();

for ($k = 0; $k <= 4; $k++)
{
	$value = SpecFieldValue::getNewInstance($selField);  	
	$value->value->set(rand(0, 40));
	$value->save();
}
$selValues = $selField->getValuesSet();

// select multiple values
$multiField = SpecField::getNewInstance($currentCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SELECTOR);
$multiField->save();
for ($k = 0; $k <= 5; $k++)
{
	$value = SpecFieldValue::getNewInstance($multiField);  	
	$value->value->set(rand(0, 40));
	$value->save();
}
$multiValues = $multiField->getValuesSet();

// create numeric filter
$fg = FilterGroup::getNewInstance($rangeField);
$fg->save();

$ranges = array(array(0, 100), array(101, 200), array(201, 340), array(100, 500));

foreach ($ranges as $range)
{
	$f = Filter::getNewInstance($fg);
	$f->rangeStart->set($range[0]);
	$f->rangeEnd->set($range[1]);
	$f->save();  
}

// create date filter
$fg = FilterGroup::getNewInstance($dateField);
$fg->save();

for ($k = 2006; $k <= 2008; $k++)
{
	$f = Filter::getNewInstance($fg);
	$f->rangeDateStart->set($k . '-01-01');
	$f->rangeDateEnd->set($k . '-12-31');
	$f->save();  
}

$products = Product::getRecordSet('Product', new ARSelectFilter());

foreach ($products as $product)
{
	$product->setAttributeValue($rangeField, rand(1, 340));
	$product->setAttributeValue($dateField, rand(2006, 2008) . '-05-05');
	$product->setAttributeValue($selField, $selValues->get(rand(0, $selValues->size() - 1)));
	
	for ($k = 0; $k < $multiValues->size(); $k++)
	{
		if (rand(0,1))
		{
			$product->setAttributeValue($multiField, $multiValues->get($k));			
		}	
	}
	
	$product->save();	
}

$productFilter = new ProductFilter($currentCategory, $currentCategory->getProductFilter(new ARSelectFilter()));
$productCount = new ProductCount($productFilter);

$counts = $productCount->getCountByFilters();

print_r($counts);

ActiveRecord::rollback();

?>