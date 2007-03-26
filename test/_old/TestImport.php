<style>
    .singleSelect
    {
        background-color: #DDFFFF;
    }
    .multiSelect
    {
        background-color: #FFDDFF;
    }
    .numeric
    {
        background-color: #FFFFDD;
    }
</style>
<?php

//echo 'asd';exit;

echo "<pre>";
include("../Initialize.php");
include('../../../../prex/PrexCategory.php');
ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

// download all data
//$prex = new PrexCategory(1); exit;

include cachedir . 'prex_dict.php';

$categoryIDs = array();
$i = new DirectoryIterator(cachedir . '/cat');
foreach ($i as $key => $value)
{
  	if (!$value->isDot())
  	{
	    $categoryIDs[] = (int)(string)$value;
	}
}

$droppedAttributes = array('EAN Code', 'Country', 'Name', 'Warranty', 'New', 'Other Functions');

ActiveRecordModel::beginTransaction();
$products = array();

$byType = array();

$categoryNames = array();

foreach ($categoryIDs as $ccid => $id)
{
	$prex = new PrexCategory($id);
	$spec = $prex->getSpec();
	
	echo '<h1>' . $prex->getName() . '</h1>';

    $categoryNames[$prex->getName()] = '';	

	// create a new category
	$category = Category::getNewInstance(Category::getRootNode());
	$category->setValueByLang("name", "en", prex_translate($prex->getName()));
	$category->isEnabled->set(1);
	$category->save();
	
	foreach ($spec as $groupname => $groupvalues)
	{		
		$group = SpecFieldGroup::getNewInstance($category);
		$group->setValueByLang('name', 'en', prex_translate($groupname));
		$group->save();
		
//		echo '<h1>' . $groupname . '</h1>';	print_r($groupvalues);
		
		$fieldValues = array();
		
		foreach ($groupvalues as $attrname => $attrvalues)
		{	
			if (in_array(prex_translate($attrname), $droppedAttributes))
			{
				continue;  
			}
			
			$value = current($attrvalues);  	
			reset($attrvalues);
			
//		echo '<h2>' . $attrname . '</h1>';
			$type = $prex->getFieldType($groupname, $attrname);
			
			if ($type == 'numeric')
			{
			  	$datatype = SpecField::DATATYPE_NUMBERS;
			  	$type = SpecField::TYPE_NUMBERS_SIMPLE;		  		  
			}
			else if ($type == 'singleSelect')
			{
			  	$datatype = SpecField::DATATYPE_TEXT;
			  	$type = SpecField::TYPE_TEXT_SELECTOR;			  
				$multi = false;			  
			}
			else if ($type == 'multiSelect')
			{
			  	$datatype = SpecField::DATATYPE_TEXT;
			  	$type = SpecField::TYPE_TEXT_SELECTOR;
				$multi = true;			  
			}
			else
			{
			  	$datatype = SpecField::DATATYPE_TEXT;
			  	$type = SpecField::TYPE_TEXT_SIMPLE;		  	                  
            }
			
			$field = SpecField::getNewInstance($category, $datatype, $type);	
			$field->setValueByLang('name', 'en', prex_translate($attrname));
			$field->setValueByLang('valuePrefix', 'en', $prex->getPrefix($groupname, $attrname));
			$field->setValueByLang('valueSuffix', 'en', $prex->getSuffix($groupname, $attrname));
			$field->isDisplayedInList->set(1);
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
				  	$sp = $spec;
                    $bendras = array_shift($sp);
				  	$name = $bendras['Pavadinimas'];                    
                    $productName = $name[$productId];
                    if (is_array($productName))
                    {
                        $productName = array_shift($productName);
                    }
                    $products[$productId]->setValueByLang('name', 'en', $productName);
					$products[$productId]->sku->set($productId);
				}  	
				
				if ($field->isSimpleNumbers())
				{
					$products[$productId]->setAttributeValue($field, $value + 0);  	
				}
	
				else if ($field->isTextField())
				{
					$products[$productId]->setAttributeValueByLang($field, 'en', $value);  	
				}
	
				else if ($field->isSelector())
				{
					if (!is_array($value))
					{
					  	$value = array($value);
					}
					
					foreach ($value as $selval)
					{
					  	if (!isset($fieldValues[$field->getID()][$selval]))
					  	{
							$name = $selval;
                            if ('FALSE' == $name)
                            {
                                $name = 'No';
                            }
                            else if ('TRUE' == $name)
                            {
                                $name = 'Yes';
                            }
                            
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
echo 'Category ' . $ccid . ' ('.count($categoryIDs).')';
}

foreach ($products as $key => $product)
{
  	$product->isEnabled->set(true);
	echo 'Saving product ' . $key . '<br>';
    $product->save();
}

//print_r(PrexProduct::$translations);

ActiveRecordModel::commit();
//ActiveRecordModel::rollback();

exit;

?>