<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.CategoryImage");

/**
 * Product Category Image controller
 *
 * @package application.controller.backend
 * @author Rinalds Uzkalns <rinalds@integry.net>
 *
 */
class CategoryImageController extends StoreManagementController
{
	public function index()
	{
		$categoryId = $this->request->getValue('id');
		
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle('CategoryImage', 'categoryID'), $categoryId));
		$filter->setOrder(new ARFieldHandle('CategoryImage', 'position'), 'ASC');
				
		$images = ActiveRecord::getRecordSet('CategoryImage', $filter);
		
		$languages = array();
		foreach ($this->store->getLanguageArray(false) as $langId)
		{
		  	$languages[$langId] = $this->locale->info()->getOriginalLanguageName($langId);
		}
		
		$response = new ActionResponse();
		$response->setValue('form', $this->buildForm($categoryId));
		$response->setValue('images', json_encode($images->toArray()));
		$response->setValue('languageList', $languages);
		return $response;		  
	}
	
	/**
	 * Builds a category image form validator
	 *
	 * @return RequestValidator
	 */
	private function buildValidator($catId)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("categoryImage_".$catId, $this->request);
/*
		foreach ($currencies as $currency)
		{
			$validator->addCheck('rate_' . $currency['ID'], new IsNotEmptyCheck($this->translate('_err_empty')));		  
			$validator->addCheck('rate_' . $currency['ID'], new IsNumericCheck($this->translate('_err_numeric')));		  			
			$validator->addCheck('rate_' . $currency['ID'], new MinValueCheck($this->translate('_err_negative'), 0));
			$validator->addFilter('rate_' . $currency['ID'], new NumericFilter());	
		}
*/
		return $validator;
	}

	/**
	 * Builds a category image form instance
	 *
	 * @return Form
	 */
	private function buildForm($catId)
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildValidator($catId));		
	}	
}	
	  
?>