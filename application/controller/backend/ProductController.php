<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.product.ProductSpecification");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @role admin.store.product
 */
class ProductController extends StoreManagementController 
{
	public function index()
	{
		$category = Category::getInstanceByID($this->request->getValue("id"));
	
		$response = $this->productList($category, new ActionResponse());

		$path = $this->getCategoryPathArray($category);
		$response->setValue("path", $path);
				
		return $response;
	}

	public function lists()
	{
		$id = substr($this->request->getValue("id"), 9);
		return $this->productList(Category::getInstanceByID($id), new XMLResponse());
	}

	protected function productList(Category $category, ActionResponse $response)
	{	
		$filter = new ARSelectFilter();
		$filter->setLimit($this->request->getValue('offset'), $this->request->getValue('page_size'));
	
		// get sort column
		if ($this->request->isValueSet('sort_col'))
		{
		  	$sort = array_shift(explode('_', $this->request->getValue('sort_col')));		  	
		  	$order = $this->request->getValue('sort_dir');

			$sortableLangFields = array(
									'name' => 'name',
									'shortdescription' => 'shortDescription',
									'longdescription' => 'longDescription'
								);
			
			$sortableFields = array(
									'sku' => 'sku',
									'isenabled' => 'isEnabled', 
									'stockcount' => 'stockCount', 
									'datecreated' => 'dateCreated', 
									'dateupdated' => 'dateUpdated', 
									'url' => 'URL',
									'handle' => 'handle',
									'isbestseller' => 'isBestSeller',
									'votesum' => 'voteSum',
									'votecount' => 'voteCount',
									'hits' => 'hits',
									'shippingweight' => 'shippingWeight',
									'minimumquantity' => 'minimumQuantity',
									'shippingsurchargeamount' => 'shippingSurchargeAmount',
									'isseparateshipment' => 'isSeparateShipment',
									'isfreeshipping' => 'isFreeShipping',
									'reservedcount' => 'reservedCount',
									'keywords' => 'keywords'
							  );
			
			if (isset($sortableLangFields[$sort]))
			{
			  	$handle = Product::getLangOrderHandle(new ARFieldHandle('Product', $sortableLangFields[$sort]));
			}
			elseif (isset($sortableFields[$sort]))
			{
				$handle = new ARFieldHandle('Product', $sortableFields[$sort]);  	
			}
			elseif ('manufacturer' == $sort)
			{
				$handle = new ARFieldHandle('Manufacturer', 'name');  	
			}

			if (isset($handle))
			{
			  	$filter->setOrder($handle, $order);
			}
		}	
	
		$productList = $category->getProductSet($filter, Category::LOAD_REFERENCES);
				
		$response->setValue("productList", $productList->toArray());
		$response->setValue("categoryID", $category->getID());
		$response->setValue("offset", $this->request->getValue('offset'));
		$response->setValue("totalCount", $productList->getTotalRecordCount());
		$response->setValue("currency", Store::getInstance()->getDefaultCurrency()->getID());
		return $response;	  	  	
	}

	public function autoComplete()
	{
	  	$f = new ARSelectFilter();
		$resp = array();
				  	
		$field = $this->request->getValue('field');
		
		if (in_array($field, array('sku', 'URL', 'keywords')))
		{
		  	$c = new LikeCond(new ARFieldHandle('Product', $field), $this->request->getValue($field) . '%');
		  	$f->setCondition($c);		  	

		  	$f->setOrder(new ARFieldHandle('Product', $field), 'ASC');
		  	
		  	$results = ActiveRecordModel::getRecordSet('Product', $f);
		  	
		  	foreach ($results as $value)
		  	{
				$resp[] = $value->getFieldValue($field);
			}	  			  
		}
		
		elseif ('name' == $field)
		{
		  	$c = new LikeCond(new ARFieldHandle('Product', $field), '%:"' . $this->request->getValue($field) . '%');
		  	$f->setCondition($c);		  	

			$locale = $this->locale->getLocaleCode();
			$langCond = new LikeCond(Product::getLangSearchHandle(new ARFieldHandle('Product', 'name'), $locale), $this->request->getValue($field) . '%');
			$c->addAND($langCond);
					  	
		  	$f->setOrder(Product::getLangSearchHandle(new ARFieldHandle('Product', 'name'), $locale), 'ASC');
		  	
		  	$results = ActiveRecordModel::getRecordSet('Product', $f);
		  	
		  	foreach ($results as $value)
		  	{
				$resp[] = $value->getValueByLang('name', $locale, Product::NO_DEFAULT_VALUE);
			}	  			  
		}
		  	
		return new AutoCompleteResponse($resp);
	}

	/**
	 * Displays main product information form
	 *
	 * @return unknown
	 */
	public function add()
	{
		$category = Category::getInstanceByID($this->request->getValue("id"), ActiveRecordModel::LOAD_DATA);
		$product = Product::getNewInstance($category);
		
		return $this->productForm($product);		
	}

	public function save()
	{
	  	// get Product instance
		if ($this->request->getValue('id') == 0)
	  	{
		    $product = Product::getNewInstance(Category::getInstanceByID($this->request->getValue('categoryID'), ActiveRecordModel::LOAD_DATA));
		}
		else
		{
		  	$product = Product::getInstanceByID($this->request->getValue('id'), ActiveRecordModel::LOAD_DATA);
		  	$product->loadSpecification();
		}
		$validator = $this->buildValidator($product);
		if ($validator->isValid())
		{
			// create new specField values
			$other = $this->request->getValue('other');
			$needReload = 0;
			foreach ($other as $fieldID => $values)
			{
				$field = SpecField::getInstanceByID($fieldID);

				if (is_array($values))
				{
					// multiple select
					foreach ($values as $value)
					{
					  	if ($value)
					  	{
							$fieldValue = SpecFieldValue::getNewInstance($field);
						  	$fieldValue->setValueByLang('value', $this->store->getDefaultLanguageCode(), $value);
						  	$fieldValue->save();
						  	
						  	$this->request->setValue('specItem_' . $fieldValue->getID(), 'on');				    
							$needReload = 1;
						}
					}  					  
				}
				else
				{
					// single select
					if ('other' == $this->request->getValue('specField_' . $fieldID))
					{
						$fieldValue = SpecFieldValue::getNewInstance($field);
					  	$fieldValue->setValueByLang('value', $this->store->getDefaultLanguageCode(), $values);
					  	$fieldValue->save();
					  	
					  	$this->request->setValue('specField_' . $fieldID, $fieldValue->getID());    
						$needReload = 1;					  
					}					  
				}
			}
			
			// set data
			$product->loadRequestData($this->request);
								
			ActiveRecordModel::beginTransaction();
			$product->save();
			ActiveRecordModel::commit();
									
			if ($this->request->getValue('afterAdding') == 'new')
			{
				return new JSONResponse(array('status' => 'success', 'addmore' => 1, 'needReload' => $needReload));			  	
			}
			else
			{
				return new JSONResponse(array('status' => 'success'));		
			}
		}
		else
		{
			// reset validator data (as we won't need to restore the form)
			$validator->restore();
			
			return new JSONResponse(array('status' => 'failure', 'errors' => $validator->getErrorList()));
		}
				
	}
	
	private function productForm(Product $product)
	{
		$specFields = $product->getSpecificationFieldSet(ActiveRecordModel::LOAD_REFERENCES);
		$specFieldArray = $specFields->toArray();
				
		// set select values
		$selectors = SpecField::getSelectorValueTypes();
		foreach ($specFields as $key => $field)
		{
		  	if (in_array($field->type->get(), $selectors))
		  	{
				$values = $field->getValuesSet()->toArray();				
				$specFieldArray[$key]['values'] = array('' => '');
				foreach ($values as $value)
				{
					$specFieldArray[$key]['values'][$value['ID']] = $value['value_lang'];  	
				}
				
				if (!$field->isMultiValue->get())
				{
					$specFieldArray[$key]['values']['other'] = $this->translate('_enter_other');				  
				}
			}
		}
		
		// get multi language spec fields		
		$multiLingualSpecFields = array();
		foreach ($specFields as $key => $field)
		{
		  	if ($field->isTextField())
		  	{
				$multiLingualSpecFields[] = $field->toArray();
			}
		}

		$form = $this->buildForm($product);
		
		$productFormData = $product->toArray();
		
		if($product->isLoaded())
		{
        	$product->loadSpecification();
        	foreach($product->getSpecification()->toArray() as $attr)
        	{
        	    if(in_array($attr['SpecField']['type'], SpecField::getSelectorValueTypes()))
        	    {
        	        if(1 == $attr['SpecField']['isMultiValue'])
        		    {
        		        foreach($attr['valueIDs'] as $valueID)
        		        {
        		            $productFormData["specItem_$valueID"] = "on";
        		        }
        		    }
        		    else
        		    {
        		        $productFormData["{$attr['SpecField']['fieldName']}"] = $attr['ID'];
        		    }
        	    } 
        	    else if(in_array($attr['SpecField']['type'], SpecField::getMultilanguageTypes()))
        	    {
        	        $productFormData["{$attr['SpecField']['fieldName']}"] = $attr['value_lang'];
        	        foreach($attr['value'] as $lang => $translatedValue)
        	        {
        	            $productFormData["{$attr['SpecField']['fieldName']}_{$lang}"] = $translatedValue;
        	        }
        	    }
        	    else
        	    {
        	        $productFormData["{$attr['SpecField']['fieldName']}"] = $attr['value'];
        	    }   
        	}
        	
        	$productFormData['manufacturer'] = $productFormData['Manufacturer']['name'];
        	
		}
		print_r($productFormData);
        $form->setData($productFormData);
		
		$languages = array();
		foreach ($this->store->getLanguageArray() as $lang)
		{
			$languages[$lang] = $this->locale->info()->getOriginalLanguageName($lang);
		}
		
		// product types
		$types = array(0 => $this->translate('_tangible'),
					   1 => $this->translate('_intangible'),	
					  );

		// arrange SpecFields's into groups
		$specFieldsByGroup = array();
		foreach ($specFieldArray as $field)
		{
			$specFieldsByGroup[$field['SpecFieldGroup']['ID']][] = $field;
		}
		
		$response = new ActionResponse();
		$response->setValue("cat", $product->category->get()->getID());
		$response->setValue("languageList", $languages);
		$response->setValue("specFieldList", $specFieldsByGroup);
		$response->setValue("productForm", $form);
		$response->setValue("multiLingualSpecFields", $multiLingualSpecFields);
		$response->setValue("productTypes", $types);
		$response->setValue("baseCurrency", Store::getInstance()->getDefaultCurrency()->getID());
		$response->setValue("otherCurrencies", Store::getInstance()->getCurrencyArray(Store::EXCLUDE_DEFAULT_CURRENCY));
		$productData = $product->toArray();
		if (empty($productData['ID']))
		{
			$productData['ID'] = 0;  	
		}
		$response->setValue("product", $productData);
		return $response; 	
	}
	
	/**
	 * @return RequestValidator
	 */
	private function buildValidator(Product $product)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");
		
		$validator = new RequestValidator("productFormValidator", $this->request);
		
		$validator->addCheck('name', new IsNotEmptyCheck($this->translate('_err_name_empty')));		    
		
		// check if SKU is entered if not autogenerating
		if ($this->request->getValue('save') && (($product->isExistingRecord() && $product->getFieldValue('sku') != $this->request->getValue('sku')) || !$this->request->getValue('autosku')))
		{
			$validator->addCheck('sku', new IsNotEmptyCheck($this->translate('_err_sku_empty')));		    		  
		}
		
		// check if entered SKU is unique
		if ($this->request->getValue('sku') && $this->request->getValue('save'))
		{
			ClassLoader::import('application.helper.check.IsUniqueSkuCheck');
			$validator->addCheck('sku', new IsUniqueSkuCheck($this->translate('_err_sku_not_unique'), $product));
		}
			
		// spec field validator
		$specFields = $product->getSpecificationFieldSet(ActiveRecordModel::LOAD_REFERENCES);	

		foreach ($specFields as $key => $field)
		{
			$fieldname = $field->getFormFieldName();
				
		  	// validate numeric values
			if (SpecField::TYPE_NUMBERS_SIMPLE == $field->type->get())
		  	{
				$validator->addCheck($fieldname, new IsNumericCheck($this->translate('_err_numeric')));		    
				$validator->addFilter($fieldname, new NumericFilter());		    
			}

		  	// validate required fields
			if ($field->isRequired->get())
		  	{
				if (!$field->isSelector())
				{
					$validator->addCheck($fieldname, new IsNotEmptyCheck($this->translate('_err_specfield_required'))); 
				}
				else
				{
					ClassLoader::import('application.helper.check.SpecFieldIsValueSelectedCheck');
					$validator->addCheck($fieldname, new SpecFieldIsValueSelectedCheck($this->translate('_err_specfield_requiredaaaaaaaa'), $field, $this->request));		    
				}			
			}
		}  
		
		// validate price input in all currencies
		if(!$product->isExistingRecord()) 
		{
			ProductPricing::addPricesValidator($validator);
			ProductPricing::addShippingValidator($validator);
			
			// inventory validation
			$validator->addCheck('stockCount', new IsNotEmptyCheck($this->translate('_err_stock_required')));		    
			$validator->addCheck('stockCount', new IsNumericCheck($this->translate('_err_stock_not_numeric')));		  
			$validator->addCheck('stockCount', new MinValueCheck($this->translate('_err_stock_negative'), 0));	
			$validator->addFilter('stockCount', new NumericFilter());	
		}
		
		return $validator;
	}

	private function buildForm(Product $product)
	{
		ClassLoader::import("framework.request.validator.Form");

		$form = new Form($this->buildValidator($product));
		return $form;
	}

	/**
	 * Gets path to a current node (including current node)
	 *
	 * Overloads parent method
	 * @return array
	 */
	private function getCategoryPathArray(Category $category)
	{
		$path = array();
		$pathNodes = $category->getPathNodeSet(Category::INCLUDE_ROOT_NODE);
		$defaultLang = $this->store->getDefaultLanguageCode();

		foreach ($pathNodes as $node)
		{
			$path[] = $node->getValueByLang('name', $defaultLang);
		}
		return $path;
	}

	
	public function basicData()
	{
	    $product = Product::getInstanceById($this->request->getValue('id'), ActiveRecordModel::LOAD_DATA, ActiveRecord::LOAD_REFERENCES);
		
		$response = $this->productForm($product);

		return $response;
	}

	public function inventory()
	{
	    $response = new ActionResponse();

	    $response->setValue('id', $this->request->getValue('id'));
	    $response->setValue('categoryID', $this->request->getValue('categoryID'));
	    
	    return $response;
	}

	public function options()
	{
	    $response = new ActionResponse();

	    $response->setValue('id', $this->request->getValue('id'));
	    $response->setValue('categoryID', $this->request->getValue('categoryID'));
	    
	    return $response;
	}
}

?>