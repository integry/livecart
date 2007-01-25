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
class ProductController extends StoreManagementController {


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
				 	 
		}
		else
		{
			return new ActionRedirectResponse('backend.product', 'add', array('id' => $this->request->getValue('categoryID')));  	
		}
				
	}
	
	private function productForm(Product $product)
	{
		$specFields = $product->getSpecificationFieldSet();
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
		  	if (in_array($field->type->get(), array(SpecField::TYPE_TEXT_SIMPLE, SpecField::TYPE_TEXT_ADVANCED)))
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
		
		$response = new ActionResponse();
		$response->setValue("languageList", $languages);
		$response->setValue("specFieldList", $specFieldArray);
		$response->setValue("productForm", $form);
		$response->setValue("multiLingualSpecFields", $multiLingualSpecFields);
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
		$validator->addCheck('sku', new IsNotEmptyCheck($this->translate('_err_sku_empty')));		    
		
		// spec field validator
		$specFields = $product->getSpecificationFieldSet()->toArray();			
		foreach ($specFields as $key => $field)
		{
		  	// validate numeric values
			if (SpecField::TYPE_NUMBERS_SIMPLE == $field['type'])
		  	{
				$validator->addCheck($field['fieldName'], new IsNumericCheck($this->translate('_err_numeric')));		    
				$validator->addFilter($field['fieldName'], new NumericFilter());		    
			}
		}
	
		$validator->addCheck('test', new IsNotEmptyCheck($this->translate('_err_empty')));		    
	
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

	public function test()
	{
		$product = ActiveRecordModel::getNewInstance("Product");
		$product->category->set(ActiveTreeNode::getInstanceByID("Category", 53));

		$product->save();
	}
}

?>