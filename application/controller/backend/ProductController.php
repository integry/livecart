<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.filter.FilterGroup");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.product.ProductSpecification");
ClassLoader::import("application.helper.ActiveGrid");

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
		$this->rebuildMenuLangFile();		
        $category = Category::getInstanceByID($this->request->getValue("id"), Category::LOAD_DATA);
	
		$availableColumns = $this->getAvailableColumns($category);
		$displayedColumns = $this->getDisplayedColumns($category);
		
		// sort available columns by display state (displayed columns first)
		$displayedAvailable = array_intersect_key($availableColumns, $displayedColumns);
		$notDisplayedAvailable = array_diff_key($availableColumns, $displayedColumns);		
		$availableColumns = array_merge($displayedAvailable, $notDisplayedAvailable);
			
		//$response = $this->productList($category, new ActionResponse());
		$response = new ActionResponse();
        $response->setValue("massForm", $this->getMassForm());
        $response->setValue("displayedColumns", $displayedColumns);
        $response->setValue("availableColumns", $availableColumns);
		$response->setValue("categoryID", $category->getID());
		$response->setValue("offset", $this->request->getValue('offset'));
		$response->setValue("totalCount", '55');
		$response->setValue("currency", $this->store->getDefaultCurrency()->getID());

		$path = $this->getCategoryPathArray($category);
		$response->setValue("path", $path);
				
		return $response;
	}

	protected function getAvailableColumns(Category $category)
	{
		// get available columns
		$productSchema = ActiveRecordModel::getSchemaInstance('Product');
		
		$availableColumns = array();
		foreach ($productSchema->getFieldList() as $field)
		{
			$fieldType = $field->getDataType();
			
			if ($field instanceof ARForeignKeyField)
			{
			  	continue;
			}		            
			if ($field instanceof ARPrimaryKeyField)
			{
			  	continue;
			}		            
			elseif ($fieldType instanceof ARBool)
			{
			  	$type = 'bool';
			}	  
			elseif ($fieldType instanceof ARNumeric)
			{
				$type = 'numeric';	  	
			}			
			else
			{
			  	$type = 'text';
			}
			
			$availableColumns['Product.' . $field->getName()] = $type;
		}		
		
		$availableColumns['Manufacturer.name'] = 'text';
		$availableColumns['ProductPrice.price'] = 'numeric';

		foreach ($availableColumns as $column => $type)
		{
			$availableColumns[$column] = array('name' => $this->translate($column), 'type' => $type);	
		}

		// specField columns
		$fields = $category->getSpecificationFieldSet(Category::INCLUDE_PARENT);
		foreach ($fields as $field)
		{
			if (!$field->isMultiValue->get())
			{				
				$fieldArray = $field->toArray();
				$availableColumns['specField.' . $field->getID()] = array
					(
						'name' => $fieldArray['name_lang'], 
						'type' => $field->isSimpleNumbers() ? 'numeric' : 'text'
					);				
			}
		}		

		return $availableColumns;
	}
	
	protected function getDisplayedColumns(Category $category)
	{	
		// get displayed columns
		$displayedColumns = $this->getSessionData('columns');		

		if (!$displayedColumns)
		{
			$displayedColumns = array('Product.ID', 'Product.sku', 'Product.name', 'Manufacturer.name', 'ProductPrice.price', 'Product.stockCount', 'Product.isEnabled');				
		}
		
		$availableColumns = $this->getAvailableColumns($category);
		$displayedColumns = array_intersect_key(array_flip($displayedColumns), $availableColumns);	

		// product ID is always passed as the first column
		$displayedColumns = array_merge(array('Product.ID' => 'numeric'), $displayedColumns);
				
		// set field type as value
		foreach ($displayedColumns as $column => $foo)
		{
			if (is_numeric($displayedColumns[$column]))
			{
				$displayedColumns[$column] = $availableColumns[$column]['type'];					
			}
		}

		return $displayedColumns;		
	}
	
	public function changeColumns()
	{		
		$columns = array_keys($this->request->getValue('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.product', 'index', array('id' => $this->request->getValue('category')));
	}
	
	public function lists()
	{
		$id = substr($this->request->getValue("id"), 9);
		$category = Category::getInstanceByID($id, Category::LOAD_DATA);

		$filter = new ARSelectFilter();
		
		$cond = new EqualsOrMoreCond(new ARFieldHandle('Category', 'lft'), $category->lft->get());
		$cond->addAND(new EqualsOrLessCond(new ARFieldHandle('Category', 'rgt'), $category->rgt->get()));
		$filter->setCondition($cond);

        $filter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . Store::getInstance()->getDefaultCurrencyCode() . '")', 'ID');
		
        new ActiveGrid($this->request, $filter);
        					
        $recordCount = true;

		$productArray = ActiveRecordModel::getRecordSetArray('Product', $filter, array('DefaultImage' => 'ProductImage', 'Category', 'Manufacturer'), $recordCount);
        
        // load specification and price data
        ProductSpecification::loadSpecificationForRecordSetArray($productArray);
    	ProductPrice::loadPricesForRecordSetArray($productArray);
    	
		$displayedColumns = $this->getDisplayedColumns($category);
		
    	$currency = Store::getInstance()->getDefaultCurrency()->getID();

    	$data = array();
        foreach ($productArray as $product)
    	{
            $record = array();
            foreach ($displayedColumns as $column => $type)
            {
                list($class, $field) = explode('.', $column, 2);
                if ('Product' == $class)
                {
					$value = isset($product[$field]) ? $product[$field] : '';
                }
                else if ('ProductPrice' == $class)
                {
					$value = isset($product['price_' . $currency]) ? $product['price_' . $currency] : 0;
                }
                else if ('specField' == $class)
                {
					$value = $product['attributes'][$field]['value_lang'];
				}
                else
                {
                    $value = $product[$class][$field];
                }     
				
				if ('bool' == $type)
				{
					$value = $value ? $this->translate('_yes') : $this->translate('_no');
				}
				
				$record[] = $value;
            }
            
            $data[] = $record;
        }
    	
    	$return = array();
    	$return['columns'] = array_keys($displayedColumns);
    	$return['totalCount'] = $recordCount;
    	$return['data'] = $data;
    	
    	return new JSONResponse($return);	  	  	
	}

    protected function getMassForm()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");
		ClassLoader::import("framework.request.validator.Form");
        		
		$validator = new RequestValidator("productFormValidator", $this->request);
		
		$validator->addFilter('set_price', new NumericFilter(''));
		$validator->addFilter('set_stock', new NumericFilter(''));
		$validator->addFilter('inc_price', new NumericFilter(''));
		$validator->addFilter('inc_stock', new NumericFilter(''));
		$validator->addFilter('set_minimumQuantity', new NumericFilter(''));
		$validator->addFilter('set_shippingSurchargeAmount', new NumericFilter(''));				
		
        return new Form($validator);                
    }

    public function processMass()
    {        
		$filter = new ARSelectFilter();
		
        $category = Category::getInstanceByID($this->request->getValue('id'), Category::LOAD_DATA);
        $cond = new EqualsOrMoreCond(new ARFieldHandle('Category', 'lft'), $category->lft->get());
		$cond->addAND(new EqualsOrLessCond(new ARFieldHandle('Category', 'rgt'), $category->rgt->get()));
		
        $filter->setCondition($cond);
        $filter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . Store::getInstance()->getDefaultCurrencyCode() . '")', 'ID');
		
		$filters = (array)json_decode($this->request->getValue('filters'));
		$this->request->setValue('filters', $filters);
		
        $grid = new ActiveGrid($this->request, $filter, 'Product');
        					
		$products = ActiveRecordModel::getRecordSet('Product', $filter, Product::LOAD_REFERENCES);
		
        $act = $this->request->getValue('act');
		$field = array_pop(explode('_', $act, 2));
		
		if ('manufacturer' == $act)
		{
			$manufacturer = Manufacturer::getInstanceByName($this->request->getValue('manufacturer'));
		}
		else if ('price' == $act || 'inc_price' == $act)
		{
			ProductPrice::loadPricesForRecordSet($products);	
			$baseCurrency = Store::getInstance()->getDefaultCurrencyCode();
			$price = $this->request->getValue($act);
			$currencies = Store::getInstance()->getCurrencySet();
		}
		else if ('addRelated' == $act)
		{
			$relatedProduct = Product::getInstanceBySKU($this->request->getValue('related'));
			if (!$relatedProduct)
			{
				return new JSONResponse(0);
			}			
		}            

        foreach ($products as $product)
		{
            if (substr($act, 0, 7) == 'enable_')
            {
                $product->setFieldValue($field, 1);    
            }        
            else if (substr($act, 0, 8) == 'disable_')
            {
                $product->setFieldValue($field, 0);                 
            }
            else if (substr($act, 0, 4) == 'set_')
            {
                $product->setFieldValue($field, $this->request->getValue('set_' . $field));                    
            }
            else if ('delete' == $act)
            {
				$product->delete();
			}
			else if ('manufacturer' == $act)
			{
				$product->manufacturer->set($manufacturer);
			}
			else if ('price' == $act)
			{
				$product->setPrice($baseCurrency, $price);
			}
			else if ('inc_price' == $act)
			{
				$pricing = $product->getPricingHandler();
				foreach ($currencies as $currency)
				{
					if ($pricing->isPriceSet($currency))
					{
						$p = $pricing->getPrice($currency);
						$p->increasePriceByPercent($price);						
					}	
				}
			}
			else if ('inc_stock' == $act)
			{
				$product->stockCount->set($product->stockCount->get() + $this->request->getValue($act));
			}        
			else if ('addRelated' == $act)
			{
				$product->addRelatedProduct($relatedProduct);
			}            
            
			$product->save();
        }		
		
		return new JSONResponse($this->request->getValue('act'));	
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
		  	$f->setLimit(20);
		  	
		  	$query = new ARSelectQueryBuilder();
		  	$query->setFilter($f);
		  	$query->includeTable('Product');
		  	$query->addField('DISTINCT(Product.' . $field . ')');
		  	
		  	$results = ActiveRecordModel::getDataBySQL($query->createString());
		  	
		  	foreach ($results as $value)
		  	{
				$resp[] = $value[$field];
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
		  	$f->setLimit(20);
			  		  	
		  	$results = ActiveRecordModel::getRecordSet('Product', $f);
		  	
		  	foreach ($results as $value)
		  	{
				$resp[$value->getValueByLang('name', $locale, Product::NO_DEFAULT_VALUE)] = true;
			}
			
			$resp = array_keys($resp);	  			  
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
		  	$product->loadPricing();
		  	$product->loadSpecification();
		  	$arr = $product->toArray();
		}

		$validator = $this->buildValidator($product);
		if ($validator->isValid())
		{
			$needReload = 0;
				
			// create new specField values
			if ($this->request->isValueSet('other'))
			{
				$other = $this->request->getValue('other');
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
			}
			
			$product->loadRequestData($this->request);
			
			$product->save();
									
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
        	        $productFormData["{$attr['SpecField']['fieldName']}"] = $attr['value'];
        	        foreach(Store::getInstance()->getLanguageArray() as $lang)
        	        {
        	            if (isset($attr['value_' . $lang]))
        	            {
							$productFormData["{$attr['SpecField']['fieldName']}_{$lang}"] = $attr['value_' . $lang];
						}						
        	        }
        	    }
        	    else
        	    {
        	    	$productFormData[$attr['SpecField']['fieldName']] = $attr['value'];
        	    }   
        	}
        	
        	if (isset($productFormData['Manufacturer']['name']))
        	{
				$productFormData['manufacturer'] = $productFormData['Manufacturer']['name'];
			}        	
		}
        
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
		$prevGroupID = -6541;

		foreach ($specFieldArray as $field)
		{
			$groupID = isset($field['SpecFieldGroup']['ID']) ? $field['SpecFieldGroup']['ID'] : '';
			if((int)$groupID && $prevGroupID != $groupID) 
			{
				$prevGroupID = $groupID;
			}
			
			$specFieldsByGroup[$groupID][] = $field;
		}		
							
		$response = new ActionResponse();
		$response->setValue("cat", $product->category->get()->getID());
		$response->setValue("languageList", $languages);
		$response->setValue("specFieldList", $specFieldsByGroup);
		$response->setValue("productForm", $form);
		$response->setValue("multiLingualSpecFieldss", $multiLingualSpecFields);
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
		if ($this->request->getValue('save') && !$product->isExistingRecord() && !$this->request->getValue('autosku'))
		{
			$validator->addCheck('sku', new IsNotEmptyCheck($this->translate('_err_sku_empty')));		    		  
		}
		
		// check if entered SKU is unique
		if ($this->request->getValue('sku') && $this->request->getValue('save') && (!$product->isExistingRecord() || ($this->request->isValueSet('sku') && $product->getFieldValue('sku') != $this->request->getValue('sku'))))
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
	    $product = Product::getInstanceById($this->request->getValue('id'), ActiveRecord::LOAD_DATA, array('DefaultImage' => 'ProductImage', 'Manufacturer', 'Category'));
		$response = $this->productForm($product);
		return $response;
	}

	public function inventory()
	{
	    $response = new ActionResponse();

	    $product = Product::getInstanceById($this->request->getValue('id'), ActiveRecordModel::LOAD_DATA, ActiveRecord::LOAD_REFERENCES);
		
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

	
	public function countTabsItems() {
	  	ClassLoader::import('application.model.product.*');
	  	$product = Product::getInstanceByID((int)$this->request->getValue('id'), ActiveRecord::LOAD_DATA);
	    
	  	return new JSONResponse(array(
	        'tabProductRelationship' => $product->getRelationships(false)->getTotalRecordCount()
	    ));
	}
}

?>