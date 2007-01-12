<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import('application.model.category.Category');
ClassLoader::import("application.model.category.CategoryImage");

ClassLoader::import('library.image.ImageManipulator');

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
		$category = Category::getInstanceByID((int)$this->request->getValue('id'));
		$imageArray = $category->getCategoryImagesSet()->toArray();
		
		$languages = array();
		foreach ($this->store->getLanguageArray(false) as $langId)
		{
		  	$languages[$langId] = $this->locale->info()->getOriginalLanguageName($langId);
		}
		
		$response = new ActionResponse();
		$response->setValue('form', $this->buildForm($category->getID()));
		$response->setValue('catId', $category->getID());
		$response->setValue('images', json_encode($imageArray));
		$response->setValue('languageList', $languages);
		return $response;		  
	}
	
	public function upload()
	{	
		$categoryId = $this->request->getValue('catId');
		
		$category = Category::getInstanceByID($categoryId);
			  	
		$validator = $this->buildValidator($categoryId);
		
		if (!$validator->isValid())
		{
		  	$errors = $validator->getErrorList();
			$result = array('error' => $errors['image']);
		}
		else
		{
		  	ActiveRecord::beginTransaction();

			$catImage = CategoryImage::getNewInstance($category);

			$multilingualFields = array("title");
			$catImage->setValueArrayByLang($multilingualFields, $this->store->getDefaultLanguageCode(), $this->store->getLanguageArray(true), $this->request);			
			
			$catImage->save();
			
		  	// resize image
		  	$resizer = new ImageManipulator($_FILES['image']['tmp_name']);
		  	$res = $catImage->resizeImage($resizer);
		  	
			if ($res)
			{
			  	ActiveRecord::commit();
			  	$result = $catImage->toArray();
			}
			else
			{
			  	$result = array('error' => $this->translate('_err_resize'));
				ActiveRecord::rollback();
			}	  			  		  	
		}
		
		$this->setLayout('iframeJs');
		
		$response = new ActionResponse();
		$response->setValue('catId', $categoryId);		
		$response->setValue('result', json_encode($result));		
		return $response;
	}
	
	public function save()
	{
		ActiveRecord::beginTransaction();	  
		
	  	try
		{  
			$image = ActiveRecord::getInstanceById('CategoryImage', $this->request->GetValue('imageId'), true);
			
			$multilingualFields = array("title");
			$image->setValueArrayByLang($multilingualFields, $this->store->getDefaultLanguageCode(), $this->store->getLanguageArray(true), $this->request);			
			$image->save();
			
		  	if ($_FILES['image']['tmp_name'])
		  	{
				$resizer = new ImageManipulator($_FILES['image']['tmp_name']);
				
				if (!$resizer->isValidImage())
				{
				  	throw new InvalidImageException();
				}				
				
				if (!$image->resizeImage($resizer))
				{
				  	throw new ImageResizeException();
				}				
			}
		}
		catch (InvalidImageException $exc)
		{
			$error = $this->translate('_err_not_image');	  	
		}  	
		catch (ImageResizeException $exc)
		{
			$error = $this->translate('_err_resize');	  	
		}  	
		catch (Exception $exc)
		{
			$error = $this->translate('_err_not_found ' . get_class($exc));	  	
		}
		
		$response = new ActionResponse();
		
		if (isset($error))
		{
		  	ActiveRecord::rollback();
		  	$result = array('error' => $error);
		}
		else
		{
			ActiveRecord::commit();
		  	$result = $image->toArray();
		}		
		
		$this->setLayout('iframeJs');
		$response->setValue('catId', $this->request->getValue('catId'));		
		$response->setValue('imageId', $this->request->getValue('imageId'));		
	  	$response->setValue('result', json_encode($result));
		return $response;
	}
	
	/**
	 * Remove an image
	 * @return RawResponse
	 */
	public function delete()
	{  					
		try
		{
		  	CategoryImage::deleteByID($this->request->getValue('id'));
		  	$success = true;
		}
		catch (ARNotFoundException $exc)
		{
			$success = false;  
		}
		
		return new RawResponse($success);
	}	
	
	/**
	 * Save currency order
	 * @return RawResponse
	 */
	public function saveOrder()
	{
	  	$categoryId = $this->request->getValue('categoryId');
	  	
		$order = $this->request->getValue('catImageList_' . $categoryId);
			
		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle('CategoryImage', 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet('CategoryImage', $update);  	
		}

		$resp = new RawResponse();
	  	$resp->setContent($this->request->getValue('draggedId'));
		return $resp;		  	
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

		$manip = new ImageManipulator();
		$imageCheck = new IsImageUploadedCheck($this->translate('_err_not_image'));
		$imageCheck->setFieldName('image');
		$imageCheck->setValidTypes($manip->getValidTypes());
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