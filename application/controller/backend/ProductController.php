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
		$path = $this->getCategoryPathArray($category);

		$response = new ActionResponse();
		$response->setValue("path", $path);

		$productList = $category->getProductArray();
		$response->setValue("productList", $productList);
		$response->setValue("categoryID", $this->request->getValue("id"));
		return $response;
	}

	public function autoComplete()
	{
	  	$f = new ARSelectFilter();
		$resp = array();
				  	
		if ($this->request->getValue('field') == 'sku')
		{
		  	$c = new LikeCond(new ARFieldHandle('Product', 'sku'), $this->request->getValue('sku'));
		  	$f->setCondition($c);		  	
		  	
		  	$results = ActiveRecordModel::getRecordSetArray('Product', $f);
		  	
		  	foreach ($results as $value)
		  	{
				$resp[] = $value['sku'];
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
		}
		
		$validator = $this->buildValidator($product);
		
		if ($validator->isValid())
		{
			// set data
			$product->loadRequestData($this->request);
								
			ActiveRecordModel::beginTransaction();
			$product->save();
//			ActiveRecordModel::rollback();
			ActiveRecordModel::commit();
									
			echo '<pre>';
			print_r($product->toArray());
			echo '</pre>';
		}
		else
		{
			return new ActionRedirectResponse('backend.product', 'add', array('id' => $this->request->getValue('categoryID')));  	
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
		$form->setData($product->toArray());

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
	
	private function buildValidator(Product $product)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");
		
		$validator = new RequestValidator("productFormValidator", $this->request);
		
		$validator->addCheck('name', new IsNotEmptyCheck($this->translate('_err_name_empty')));		    
		
		// check if SKU is entered if not autogenerating
		if (!$this->request->getValue('autosku') && $this->request->getValue('save'))
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
		$specFields = $product->getSpecificationFieldSet(ActiveRecordModel::LOAD_REFERENCES)->toArray();	

		foreach ($specFields as $key => $field)
		{
		  	// validate numeric values
			if (SpecField::TYPE_NUMBERS_SIMPLE == $field['type'])
		  	{
				$validator->addCheck($field['fieldName'], new IsNumericCheck($this->translate('_err_numeric')));		    
				$validator->addFilter($field['fieldName'], new NumericFilter());		    
			}

		  	// validate required fields
			if ($field['isRequired'])
		  	{
				$validator->addCheck($field['fieldName'], new IsNotEmptyCheck($this->translate('_err_specfield_required')));		    
			}
		}
	
		// inventory validation
		$validator->addCheck('stockCount', new IsNotEmptyCheck($this->translate('_err_stock_required')));		    
		$validator->addCheck('stockCount', new IsNumericCheck($this->translate('_err_stock_not_numeric')));		  
		$validator->addCheck('stockCount', new MinValueCheck($this->translate('_err_stock_negative'), 0));	
		$validator->addFilter('stockCount', new NumericFilter());		    

		// price in base currency
		$baseCurrency = Store::getInstance()->getDefaultCurrency()->getID();
		$validator->addCheck('price_' . $baseCurrency, new IsNotEmptyCheck($this->translate('_err_price_empty')));		    		

		// validate price input in all currencies
		$currencies = Store::getInstance()->getCurrencyArray();
		foreach ($currencies as $currency)
		{
			$validator->addCheck('price_' . $currency, new IsNumericCheck($this->translate('_err_price_invalid')));		  		  	
			$validator->addCheck('price_' . $currency, new MinValueCheck($this->translate('_err_price_negative'), 0));		  
			$validator->addFilter('price_' . $currency, new NumericFilter());		    
		}
			
		// shipping related numeric field validations
		$validator->addCheck('shippingSurcharge', new IsNumericCheck($this->translate('_err_surcharge_not_numeric')));		  
		$validator->addFilter('shippingSurcharge', new NumericFilter());		    
						
		$validator->addCheck('minimumQuantity', new IsNumericCheck($this->translate('_err_quantity_not_numeric')));		  
		$validator->addCheck('minimumQuantity', new MinValueCheck($this->translate('_err_quantity_negative'), 0));	
		$validator->addFilter('minimumQuantity', new NumericFilter());		    

		$validator->addFilter('shippingHiUnit', new NumericFilter());		    
		$validator->addCheck('shippingHiUnit', new IsNumericCheck($this->translate('_err_weight_not_numeric')));		  
		$validator->addCheck('shippingHiUnit', new MinValueCheck($this->translate('_err_weight_negative'), 0));	

		$validator->addFilter('shippingLoUnit', new NumericFilter());				
		$validator->addCheck('shippingLoUnit', new IsNumericCheck($this->translate('_err_weight_not_numeric')));		  
		$validator->addCheck('shippingLoUnit', new MinValueCheck($this->translate('_err_weight_negative'), 0));	
							
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
}

?>