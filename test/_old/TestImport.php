<?php

//echo 'asd';exit;

echo "<pre>";
include("../Initialize.php");
include('../../../prex/PrexCategory.php');
ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

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

$droppedAttributes = array('EAN Code', 'Country', 'Name', 'Warranty', 'New');

ActiveRecordModel::beginTransaction();
$products = array();

$byType = array();

foreach ($categoryIDs as $id)
{
	$prex = new PrexCategory($id);
	$spec = $prex->getSpec();
	
	echo '<h1>' . $prex->getName() . '</h1>';
	
	// create a new category
	$category = Category::getNewInstance(Category::getRootNode());
	$category->setValueByLang("name", "en", $prex->getName());
	$category->isEnabled->set(1);
//	$category->save();
	
	foreach ($spec as $groupname => $groupvalues)
	{		
		break;
		$group = SpecFieldGroup::getNewInstance($category);
		$group->setValueByLang('name', 'en', prex_translate($groupname));
//		$group->save();
		
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
			
//			print_R($value);
			
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
	
			else if ('TRUE' == $value || 'FALSE' == $value)
			{
			  	$datatype = SpecField::DATATYPE_TEXT;
			  	$type = SpecField::TYPE_TEXT_SELECTOR;
//		echo 'bool';
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
//		echo 'array ('.(int)$multi.')';
			}
			elseif (is_numeric($value))
			{
			  	$datatype = SpecField::DATATYPE_NUMBERS;
			  	$type = SpecField::TYPE_NUMBERS_SIMPLE;		  
//		echo 'numeric';
			}
			else
			{
			  	$datatype = SpecField::DATATYPE_TEXT;
			  	$type = SpecField::TYPE_TEXT_SIMPLE;		  	  
//		echo 'text';
			}
		
			$field = SpecField::getNewInstance($category, $datatype, $type);	
			$field->setValueByLang('name', 'en', prex_translate($attrname));
			$field->isDisplayedInList->set(1);
			$field->specFieldGroup->set($group);
			
			if ($field->isSelector() && $multi)
			{
				$field->isMultiValue->set(true);
			}
			
//			$field->save();
				
			foreach ($attrvalues as $productId => $value)
			{
				if (!isset($products[$productId]))
				{
				  	$products[$productId] = Product::getNewInstance($category);
				  	$products[$productId]->setValueByLang('name', 'en', $spec['Bendras aprašymas']['Pavadinimas'][$productId]);
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
							$fieldValues[$field->getID()][$selval] = SpecFieldValue::getNewInstance($field);
							$fieldValues[$field->getID()][$selval]->setValueByLang('value', 'en', $selval);
//							$fieldValues[$field->getID()][$selval]->save();
						}
	
					$products[$productId]->setAttributeValue($field, $fieldValues[$field->getID()][$selval]);  	
					}  
				}
			}
		
		}  
	}  
	
	// value type adjustments
	foreach ($spec as $group => $fields)
	{
	  	foreach ($fields as $name => $type)
	  	{
			if ((count(array_unique($spec[$group][$name])) / count($spec[$group][$name]) < 0.2)
				&& !$prex->getFieldType($group, $name)
				)
			{
			  	$prex->setFieldType($group, $name, 'singleSelect', $value);
			}
		}  					
	}	


	foreach ($prex->fieldTypes as $group => $fields)
	{
	  	foreach ($fields as $name => $type)
	  	{
			$byType[$type][$name] = $spec[$group][$name];
		}  					
	}

/*
	$sorted = array();
	foreach ($prex->fieldTypes as $group => $fields)
	{
	  	echo '<h2>' . prex_translate($group) . '</h2>';
	  	
	  	foreach ($fields as $name => $type)
	  	{
			
			$sorted[$group][$name] = true;
					
			if (in_array(prex_translate($name), $droppedAttributes))
			{
				continue;  
			}

		  	echo '<h3>' . prex_translate($name) .'(' . $type . ')</h3>';	
			  
			// sample values
			for ($k = 0; $k < 5; $k++)
			{
				print_r(next($spec[$group][$name]));  
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			} 			     
		}  					
	}
	
	echo '<h2>== Text Values ==</h2>';
	  	
	echo '<div style="background-color: #EEEEEE;">';
	  	
	// text attributes
	foreach ($spec as $group => $fields)
	{
	  	foreach ($fields as $name => $values)
	  	{
			if (isset($sorted[$group][$name]))
			{
			  	continue;
			}    
			
		  	echo '<h3>' . prex_translate($name) .'(' . $type . ')</h3>';	
			  
			// sample values
			for ($k = 0; $k < 5; $k++)
			{
				print_r(next($spec[$group][$name]));  
				echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			} 			     			
		}		
	}
	
	echo '</div>';	
	*/
	
}

foreach ($byType as $type => $fields)
{
  	echo '<h1>' . $type . '</h1>';
  	foreach ($fields as $name => $values)
  	{
	    echo '<h2>' . $name . '</h2>';
	    print_r($values);
	}
}


exit;

foreach ($products as $product)
{
  	$product->isEnabled->set(true);
	$product->save();
}

//file_put_contents('prex_dict.php', var_export(PrexProduct::$translations, true));
//print_r(PrexProduct::$translations);

//ActiveRecordModel::commit();
ActiveRecordModel::rollback();

exit;

?>