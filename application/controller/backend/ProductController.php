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
 * @role product
 */
class ProductController extends StoreManagementController 
{
	public function index()
	{
        $category = Category::getInstanceByID($this->request->get("id"), Category::LOAD_DATA);
	
		$availableColumns = $this->getAvailableColumns($category);
		$displayedColumns = $this->getDisplayedColumns($category);
		
		// sort available columns by display state (displayed columns first)
		$displayedAvailable = array_intersect_key($availableColumns, $displayedColumns);
		$notDisplayedAvailable = array_diff_key($availableColumns, $displayedColumns);		
		$availableColumns = array_merge($displayedAvailable, $notDisplayedAvailable);
			
		//$response = $this->productList($category, new ActionResponse());
		$response = new ActionResponse();
        $response->set("massForm", $this->getMassForm());
        $response->set("displayedColumns", $displayedColumns);
        $response->set("availableColumns", $availableColumns);
		$response->set("categoryID", $category->getID());
		$response->set("offset", $this->request->get('offset'));
		$response->set("totalCount", '0');
		$response->set("currency", $this->application->getDefaultCurrency()->getID());
		$response->set("filters", $this->request->get('filters'));

		$path = $this->getCategoryPathArray($category);
		$response->set("path", $path);
				
		return $response;
	}

	public function changeColumns()
	{		
		$columns = array_keys($this->request->get('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.product', 'index', array('id' => $this->request->get('id')));
	}
	
	public function lists()
	{
		$id = substr($this->request->get("id"), 9);
		$category = Category::getInstanceByID($id, Category::LOAD_DATA);

		$filter = new ARSelectFilter();
		
		$cond = new EqualsOrMoreCond(new ARFieldHandle('Category', 'lft'), $category->lft->get());
		$cond->addAND(new EqualsOrLessCond(new ARFieldHandle('Category', 'rgt'), $category->rgt->get()));
		$filter->setCondition($cond);

        $filter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');
		
        new ActiveGrid($this->application, $filter);
        					
        $recordCount = true;

		$productArray = ActiveRecordModel::getRecordSetArray('Product', $filter, array('Category', 'Manufacturer'), $recordCount);
        
		$displayedColumns = $this->getDisplayedColumns($category);
		
        // load specification data
        foreach ($displayedColumns as $column => $type)
        {
            if($column == 'hiddenType') continue;
            
            list($class, $field) = explode('.', $column, 2);
            if ('specField' == $class)
            {
                ProductSpecification::loadSpecificationForRecordSetArray($productArray);                    
                break;
            }
        }

        // load price data
    	ProductPrice::loadPricesForRecordSetArray($productArray);
		
    	$currency = $this->application->getDefaultCurrency()->getID();

    	$data = array();

		foreach ($productArray as $product)
    	{
            $record = array();
            foreach ($displayedColumns as $column => $type)
            {
                if($column == 'hiddenType') 
                {
                    $record[] = $product['type'];
                    continue;
                }
                
                list($class, $field) = explode('.', $column, 2);
                if ('Product' == $class)
                {
					$value = isset($product[$field . '_lang']) ? 
                                $product[$field . '_lang'] : (isset($product[$field]) ? $product[$field] : '');
                }
                else if ('ProductPrice' == $class)
                {
					$value = isset($product['price_' . $currency]) ? $product['price_' . $currency] : 0;
                }
                else if ('specField' == $class)
                {
					$value = isset($product['attributes'][$field]['value_lang']) ? $product['attributes'][$field]['value_lang'] : '';
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

	/**
	 * @role mass
	 */
    public function processMass()
    {        
		$filter = new ARSelectFilter();
		
        $category = Category::getInstanceByID($this->request->get('id'), Category::LOAD_DATA);
        $cond = new EqualsOrMoreCond(new ARFieldHandle('Category', 'lft'), $category->lft->get());
		$cond->addAND(new EqualsOrLessCond(new ARFieldHandle('Category', 'rgt'), $category->rgt->get()));
		
        $filter->setCondition($cond);
        $filter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');
		
		$filters = (array)json_decode($this->request->get('filters'));
		$this->request->set('filters', $filters);
		
        $grid = new ActiveGrid($this->application, $filter, 'Product');
        $filter->setLimit(0);
        					
		$products = ActiveRecordModel::getRecordSet('Product', $filter, Product::LOAD_REFERENCES);
		
        $act = $this->request->get('act');
		$field = array_pop(explode('_', $act, 2));
		
		if ('manufacturer' == $act)
		{
			$manufacturer = Manufacturer::getInstanceByName($this->request->get('manufacturer'));
		}
		else if ('price' == $act || 'inc_price' == $act)
		{
			ProductPrice::loadPricesForRecordSet($products);	
			$baseCurrency = $this->application->getDefaultCurrencyCode();
			$price = $this->request->get($act);
			$currencies = $this->application->getCurrencySet();
		}
		else if ('addRelated' == $act)
		{
			$relatedProduct = Product::getInstanceBySKU($this->request->get('related'));
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
                $product->setFieldValue($field, $this->request->get('set_' . $field));                    
            }
            else if ('delete' == $act)
            {
				Product::deleteById($product->getID());
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
				$product->stockCount->set($product->stockCount->get() + $this->request->get($act));
			}        
			else if ('addRelated' == $act)
			{
				$product->addRelatedProduct($relatedProduct);
			}            
            
			$product->save();
        }		
		
		return new JSONResponse(array('act' => $this->request->get('act')), 'success', $this->translate('_mass_action_succeed'));	
    }	

	public function autoComplete()
	{
	  	$f = new ARSelectFilter();
		$f->setLimit(20);
		  	
		$resp = array();
				  	
		$field = $this->request->get('field');
		
		if (in_array($field, array('sku', 'URL', 'keywords')))
		{
		  	$c = new LikeCond(new ARFieldHandle('Product', $field), $this->request->get($field) . '%');
		  	$f->setCondition($c);		  	

			$f->setOrder(new ARFieldHandle('Product', $field), 'ASC');
		  	
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
		
		else if ('name' == $field)
		{
		  	$c = new LikeCond(new ARFieldHandle('Product', $field), '%:"' . $this->request->get($field) . '%');
		  	$f->setCondition($c);		  	

			$locale = $this->locale->getLocaleCode();
			$langCond = new LikeCond(Product::getLangSearchHandle(new ARFieldHandle('Product', 'name'), $locale), $this->request->get($field) . '%');
			$c->addAND($langCond);
					  	
		  	$f->setOrder(Product::getLangSearchHandle(new ARFieldHandle('Product', 'name'), $locale), 'ASC');
			  		  	
		  	$results = ActiveRecordModel::getRecordSet('Product', $f);
		  	
		  	foreach ($results as $value)
		  	{
				$resp[$value->getValueByLang('name', $locale, Product::NO_DEFAULT_VALUE)] = true;
			}
			
			$resp = array_keys($resp);
		}
		
		else if ('specField_' == substr($field, 0, 10))
		{
            list($foo, $id) = explode('_', $field);
        
            $handle = new ARFieldHandle('SpecificationStringValue', 'value');
			$locale = $this->locale->getLocaleCode();
            $searchHandle = MultiLingualObject::getLangSearchHandle($handle, $locale);

		  	$f->setCondition(new EqualsCond(new ARFieldHandle('SpecificationStringValue', 'specFieldID'), $id));
            $f->mergeCondition(new LikeCond($handle, '%:"' . $this->request->get($field) . '%'));            
			$f->mergeCondition(new LikeCond($searchHandle, $this->request->get($field) . '%'));
					  	
		  	$f->setOrder($searchHandle, 'ASC');
			  		  	
		  	$results = ActiveRecordModel::getRecordSet('SpecificationStringValue', $f);
		  	
		  	foreach ($results as $value)
		  	{
				$resp[$value->getValueByLang('value', $locale, Product::NO_DEFAULT_VALUE)] = true;
			}

			$resp = array_keys($resp);
        }
		  	
		return new AutoCompleteResponse($resp);
	}

	/**
	 * Displays main product information form
	 *
	 * @role create
	 * 
	 * @return ActionResponse
	 */
	public function add()
	{
		$category = Category::getInstanceByID($this->request->get("id"), ActiveRecordModel::LOAD_DATA);
		
		$response = $this->productForm(Product::getNewInstance($category, ''));		
		if ($this->config->get('AUTO_GENERATE_SKU'))
		{
            $response->get('productForm')->set('autosku', true);
        }
		return $response;
	}

	/**
	 * @role create
	 */
	public function create()
	{
	    $product = Product::getNewInstance(Category::getInstanceByID($this->request->get('categoryID')), $this->translate('_new_product'));
	    
	    $response = $this->save($product);
	    
	    if ($response instanceOf ActionResponse)
	    {
            $response->get('productForm')->clearData();
            $response->set('id', $product->getID());
            return $response;
        }
        else
        {
            return $response;
        }
	}
	
	/**
	 * @role update
	 */
	public function update()
	{
	  	$product = Product::getInstanceByID($this->request->get('id'), ActiveRecordModel::LOAD_DATA);
	  	$product->loadPricing();
	  	$product->loadSpecification();
	  	
	  	return $this->save($product);
	}

	public function basicData()
	{
	    $product = Product::getInstanceById($this->request->get('id'), ActiveRecord::LOAD_DATA, array('DefaultImage' => 'ProductImage', 'Manufacturer', 'Category'));
		$response = $this->productForm($product);
		$response->set('counters', $this->countTabsItems()->getData());
		return $response;
	}

	public function countTabsItems() {
	  	ClassLoader::import('application.model.product.*');
	  	$product = Product::getInstanceByID((int)$this->request->get('id'), ActiveRecord::LOAD_DATA);
	    
	  	return new JSONResponse(array(
	        'tabProductRelationship' => $product->getRelationships(false)->getTotalRecordCount(),
	        'tabProductFiles' => $product->getFiles(false)->getTotalRecordCount(),
	        'tabProductImages' => count($product->getImageArray()),
	    ));
	}
	
	public function info()
	{
	    $product = Product::getInstanceById($this->request->get('id'), ActiveRecord::LOAD_DATA, array('DefaultImage' => 'ProductImage', 'Manufacturer', 'Category'));
        $response = new ActionResponse();
        $response->set('product', $product->toArray());
        return $response;        
    }

	protected function getAvailableColumns(Category $category)
	{
		// get available columns
		$productSchema = ActiveRecordModel::getSchemaInstance('Product');
		
		$availableColumns = array();
		foreach ($productSchema->getFieldList() as $field)
		{
			$type = ActiveGrid::getFieldType($field);
            
			if (!$type)
			{
                continue;
            }
			
			$availableColumns['Product.' . $field->getName()] = $type;
		}		
		
		$availableColumns['Manufacturer.name'] = 'text';
		$availableColumns['ProductPrice.price'] = 'numeric';
        $availableColumns['hiddenType'] = 'numeric';

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
			$displayedColumns = array('Product.ID', 'hiddenType','Product.sku', 'Product.name', 'Manufacturer.name', 'ProductPrice.price', 'Product.stockCount', 'Product.isEnabled');				
		}
		
		$availableColumns = $this->getAvailableColumns($category);
		$displayedColumns = array_intersect_key(array_flip($displayedColumns), $availableColumns);	

		// product ID is always passed as the first column
        $displayedColumns = array_merge(array('hiddenType' => 'numeric'), $displayedColumns);
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
	
	private function save(Product $product)
	{
		$validator = $this->buildValidator($product);
		if ($validator->isValid())
		{
			$needReload = 0;
				
			// create new specField values
			if ($this->request->isValueSet('other'))
			{
				$other = $this->request->get('other');
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
							  	$fieldValue->setValueByLang('value', $this->application->getDefaultLanguageCode(), $value);
							  	$fieldValue->save();
							  	
							  	$this->request->set('specItem_' . $fieldValue->getID(), 'on');				    
								$needReload = 1;
							}
						}  					  
					}
					else
					{
						// single select
						if ('other' == $this->request->get('specField_' . $fieldID))
						{
							$fieldValue = SpecFieldValue::getNewInstance($field);
						  	$fieldValue->setValueByLang('value', $this->application->getDefaultLanguageCode(), $values);
						  	$fieldValue->save();
						  	
						  	$this->request->set('specField_' . $fieldID, $fieldValue->getID());    
							$needReload = 1;					  
						}					  
					}
				}				
			}
			
			$product->loadRequestData($this->request);			
			$product->save();
			
			$response = $this->productForm($product);
			
		    $response->setHeader('Cache-Control', 'no-cache, must-revalidate');
		    $response->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
		    $response->setHeader('Content-type', 'text/javascript');
							
			return $response;
		}
		else
		{
			// reset validator data (as we won't need to restore the form)
			$validator->restore();
			
			return new JSONResponse(array('errors' => $validator->getErrorList(), 'failure', $this->translate('_could_not_save_product_information')));
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
        	        foreach($this->application->getLanguageArray() as $lang)
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
		foreach ($this->application->getLanguageArray() as $lang)
		{
			$languages[$lang] = $this->locale->info()->getOriginalLanguageName($lang);
		}
		
		// product types
		$types = array(0 => $this->translate('_tangible'),
					   1 => $this->translate('_intangible'),	
					  );

        // default product type
        if (!$product->isLoaded())
        {
            $product->type->set(substr($this->config->get('DEFAULT_PRODUCT_TYPE'), -1));
            $form->set('type', $product->type->get());
        }
    
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
		$response->set("cat", $product->category->get()->getID());
		$response->set("specFieldList", $specFieldsByGroup);
		$response->set("productForm", $form);
		$response->set("path", $product->category->get()->getPathNodeArray());
		$response->set("multiLingualSpecFieldss", $multiLingualSpecFields);
		$response->set("productTypes", $types);
		$response->set("baseCurrency", $this->application->getDefaultCurrency()->getID());
		$response->set("otherCurrencies", $this->application->getCurrencyArray(LiveCart::EXCLUDE_DEFAULT_CURRENCY));
		$productData = $product->toArray();
		if (empty($productData['ID']))
		{
			$productData['ID'] = 0;  	
		}
		$response->set("product", $productData);
		
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
		if ($this->request->get('save') && !$product->isExistingRecord() && !$this->request->get('autosku'))
		{
			$validator->addCheck('sku', new IsNotEmptyCheck($this->translate('_err_sku_empty')));		    		  
		}
		
		// check if entered SKU is unique
		if ($this->request->get('sku') && $this->request->get('save') && (!$product->isExistingRecord() || ($this->request->isValueSet('sku') && $product->getFieldValue('sku') != $this->request->get('sku'))))
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
				if (!($field->isSelector() && $field->isMultiValue->get()))
				{
					$validator->addCheck($fieldname, new IsNotEmptyCheck($this->translate('_err_specfield_required'))); 
				}
				else
				{
					ClassLoader::import('application.helper.check.SpecFieldIsValueSelectedCheck');
					$validator->addCheck($fieldname, new SpecFieldIsValueSelectedCheck($this->translate('_err_specfield_multivaluerequired'), $field, $this->request));		    
				}			
			}
		}  
		
		// validate price input in all currencies
		if(!$product->isExistingRecord()) 
		{
			ClassLoader::import('application.controller.backend.ProductPriceController');
            ProductPriceController::addPricesValidator($validator);
			ProductPriceController::addShippingValidator($validator);
			ProductPriceController::addInventoryValidator($validator);        
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
		$defaultLang = $this->application->getDefaultLanguageCode();

		foreach ($pathNodes as $node)
		{
			$path[] = $node->getValueByLang('name', $defaultLang);
		}
		return $path;
	}
}

?>