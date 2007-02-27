<?php

echo "<pre>";
require_once("init.php");
require_once('../../../../prex/PrexCategory.php');
ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

$prex = new PrexCategory(16405);
$spec = $prex->getSpec();

//print_r($spec);

ActiveRecordModel::beginTransaction();

// create a new category
$category = Category::getNewInstance(Category::getRootNode());
$category->setValueByLang("name", "en", "Notebooks");
$category->save();

$products = array();

foreach ($spec as $groupname => $groupvalues)
{
	$group = SpecFieldGroup::getNewInstance($category);
	$group->setValueByLang('name', 'en', $groupname);
	$group->save();
	
	echo '<h1>' . $groupname . '</h1>';
	
	$fieldValues = array();
	
	foreach ($groupvalues as $attrname => $attrvalues)
	{
		$value = current($attrvalues);  	
		reset($attrvalues);
		
		print_R($value);
		
	echo '<h2>' . $attrname . '</h1>';

		if ('TRUE' == $value || 'FALSE' == $value)
		{
		  	$datatype = SpecField::DATATYPE_TEXT;
		  	$type = SpecField::TYPE_TEXT_SELECTOR;
	echo 'bool';
		}
		elseif (is_array($value))
		{
		  	$datatype = SpecField::DATATYPE_TEXT;
		  	$type = SpecField::TYPE_TEXT_SELECTOR;

			$multi = false;
			foreach ($attrvalues as $val)
			{
				if (count($val) > 1)
				{
				  	$multi = true;
				}  	
			}	  
	echo 'array ('.(int)$multi.')';
		}
		elseif (is_numeric($value))
		{
		  	$datatype = SpecField::DATATYPE_NUMBERS;
		  	$type = SpecField::TYPE_NUMBERS_SIMPLE;		  
	echo 'numeric';
		}
		else
		{
		  	$datatype = SpecField::DATATYPE_TEXT;
		  	$type = SpecField::TYPE_TEXT_SIMPLE;		  	  
	echo 'text';
		}
	
		$field = SpecField::getNewInstance($category, $datatype, $type);	
		$field->setValueByLang('name', 'en', $attrname);
		$field->specFieldGroup->set($group);
		
		if ($field->isSelector() && $multi)
		{
			$field->isMultiValue->set(true);
		}
		
		$field->save();
			
		foreach ($attrvalues as $productId => $value)
		{
			if (!isset($products[$productId]))
			{
			  	$products[$productId] = Product::getNewInstance($category);
			  	$products[$productId]->setValueByLang('name', 'en', $spec['Bendras aprašymas']['Pavadinimas'][$productId]);
			}  	
			
			if ($field->isSimpleNumbers())
			{
				$products[$productId]->setAttributeValue($field, $value + 0);  	
			}

			if ($field->isTextField())
			{
				$products[$productId]->setAttributeValue($field, $value);  	
			}

			if ($field->isSelector())
			{
				if (!is_array($value))
				{
				  	$value = array($value);
				}
				
				foreach ($value as $selval)
				{
				  	if (!isset($fieldValues[$field->getID()][$selval]))
				  	{
						$fieldValues[$field->getID()][$selval] = SpecFieldValue::getNewInstance($field);
						$fieldValues[$field->getID()][$selval]->setValueByLang('value', 'en', $selval);
						$fieldValues[$field->getID()][$selval]->save();
					}

				$products[$productId]->setAttributeValue($field, $fieldValues[$field->getID()][$selval]);  	
				}  
			}
		}
	
	}  
}


foreach ($products as $product)
{
  	$product->isEnabled->set(true);
	$product->save();
}

ActiveRecordModel::commit();
//ActiveRecordModel::rollback();

?>