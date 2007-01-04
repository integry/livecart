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
		$response->setValue('catId', $categoryId);
		$response->setValue('images', json_encode($images->toArray()));
		$response->setValue('languageList', $languages);
		return $response;		  
	}
	
	public function upload()
	{
		$categoryId = $this->request->getValue('catId');	  	
		$validator = $this->buildValidator($categoryId);
		
		if (!$validator->isValid())
		{
		  	$errors = $validator->getErrorList();
			$result = array('error' => $errors['image']);
		}
		else
		{
		  	// process upload
		  	$result = array();
		  	
		  	// resize image
		  	
		  	// create a record in DB
		  	
		  	// set image properties in array
			  		  	
		}
		
		$this->setLayout('iframeJs');
		
		$response = new ActionResponse();
		$response->setValue('catId', $categoryId);		
		$response->setValue('result', json_encode($result));		
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

		$uploadCheck = new IsFileUploadedCheck($this->translate('_err_not_uploaded'));
		$uploadCheck->setFieldName('image');
		$validator->addCheck('image', $uploadCheck);

		$imageCheck = new IsImageUploadedCheck($this->translate('_err_not_image'));
		$imageCheck->setFieldName('image');
		$imageCheck->setValidTypes(array('JPEG', 'GIF'));
		$validator->addCheck('image', $imageCheck);
		
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