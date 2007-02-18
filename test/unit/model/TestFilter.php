<?php

echo "<pre>";
require_once("init.php");

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

$currentCategory = Category::getRootNode();

ActiveRecord::getDBConnection()->executeQuery("TRUNCATE specificationdatevalue");
ActiveRecord::getDBConnection()->executeQuery("TRUNCATE specificationnumericvalue");
ActiveRecord::getDBConnection()->executeQuery("TRUNCATE specificationitem");

// get category filter groups
$filterGroupSet = $currentCategory->getFilterGroupSet();
$filterGroups = $filterGroupSet->toArray(true);

// get group filters
$ids = array();
foreach ($filterGroups as $group)
{
  	$ids[] = $group['ID'];
}		

$filterCond = new INCond(new ARFieldHandle('Filter', 'filterGroupID'), $ids);
$filterFilter = new ARSelectFilter();
$filterFilter->setCondition($filterCond);
$filterFilter->setOrder(new ARFieldHandle('Filter', 'filterGroupID'));
$filterFilter->setOrder(new ARFieldHandle('Filter', 'position'));

$filters = ActiveRecord::getRecordSet('Filter', $filterFilter, true);
					
// sort filters by group
$sorted = array();
foreach ($filters as $filter)
{
	$sorted[$filter->filterGroup->get()->getID()][] = $filter->toArray();
}

// assign sorted filters to group arrays
foreach ($filterGroups as &$group)
{
  	if (isset($sorted[$group['ID']]))
  	{
	    $group['filters'] = $sorted[$group['ID']];
	}
}

$rangeField = SpecField::getInstanceByID(6);
$dateField = SpecField::getInstanceByID(7);

$selField = SpecField::getInstanceByID(8);
$selValues = $selField->getValuesSet();

$multiField = SpecField::getInstanceByID(10, true);
$multiValues = $multiField->getValuesSet();

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
$counts = $productFilter->getCountByFilters($filters);

print_r($counts);

?>