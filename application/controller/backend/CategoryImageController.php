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
	private $imageSizes = array(0 => array(50, 80),
								1 => array(80, 150),
								2 => array(300, 400),
								);
	
	public function index()
	{
		$categoryId = $this->request->getValue('id');
		
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle('CategoryImage', 'categoryID'), $categoryId));
		$filter->setOrder(new ARFieldHandle('CategoryImage', 'position'), 'ASC');
				
		$images = ActiveRecord::getRecordSet('CategoryImage', $filter);
		$imageArray = $images->toArray();
		
		foreach ($images as $id => $image)
		{
			$imageArray[$id]['paths'] = array();
			foreach ($this->imageSizes as $key => $value)
		  	{
				$imageArray[$id]['paths'][$key] = $image->getPath($key);					
			}			
		}
		
		$languages = array();
		foreach ($this->store->getLanguageArray(false) as $langId)
		{
		  	$languages[$langId] = $this->locale->info()->getOriginalLanguageName($langId);
		}
		
		$response = new ActionResponse();
		$response->setValue('form', $this->buildForm($categoryId));
		$response->setValue('catId', $categoryId);
		$response->setValue('images', json_encode($imageArray));
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
		  	// get current max image position
		  	$filter = new ARSelectFilter();
		  	$filter->setCondition(new EqualsCond(new ARFieldHandle('CategoryImage', 'categoryID'), $categoryId));
		  	$filter->setOrder(new ARFieldHandle('CategoryImage', 'position'), 'DESC');
		  	$filter->setLimit(1);
		  	$maxPosSet = ActiveRecord::getRecordSet('CategoryImage', $filter);
			if ($maxPosSet->size() > 0)
			{
				$maxPos = $maxPosSet->get(0)->position->get() + 1;  	
			}
			else
			{
			  	$maxPos = 0;
			}			  
				
			// process upload...
			
		  	ActiveRecord::beginTransaction();
			
			$catImage = ActiveRecord::getNewInstance('CategoryImage');
		  	$catImage->category->set(Category::getInstanceById($categoryId));
			$catImage->position->set($maxPos);			  								

			$multilingualFields = array("title");
			$catImage->setValueArrayByLang($multilingualFields, $this->store->getDefaultLanguageCode(), $this->store->getLanguageArray(true), $this->request);			
			
			$catImage->save();
			
		  	// resize image
		  	$resizer = new ImageManipulator($_FILES['image']['tmp_name']);
		  	
		  	$publicRoot = ClassLoader::getRealPath('public') . '/';
			  
			foreach ($this->imageSizes as $key => $size)
		  	{
				$filePath = $publicRoot . $catImage->getPath($key);
				$res = $resizer->resize($size[0], $size[1], $filePath);
				if (!$res)
				{
				  	break;
				}
			}
		  	
		  	$result = array();

			if ($res)
			{
			  	ActiveRecord::commit();
			  	$result = $catImage->toArray();
			  	
			  	$result['paths'] = array();
				foreach ($this->imageSizes as $key => $value)
			  	{
					$result['paths'][$key] = $catImage->getPath($key);					
				}
			}
			else
			{
			  	$result['error'] = $this->translate('_err_resize');
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
				  	throw new ImageException();
				}				
				
				if (!$this->resizeImage($image, $resizer))
				{
				  	throw new ImageException();
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
			$error = $this->translate('_err_not_found');	  	
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
		  	$result['paths'] = array();
			foreach ($this->imageSizes as $key => $value)
		  	{
				$result['paths'][$key] = $image->getPath($key);					
			}
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
		$id = $this->request->getValue('id');
		
		try
	  	{
			// make sure the record exists
			$inst = ActiveRecord::getInstanceById('CategoryImage', $id, true);
			
			// delete image files
			foreach ($this->imageSizes as $key => $value)
		  	{
				unlink($inst->getPath($key));					
			}			
			
			$success = $id;
			
			ActiveRecord::deleteByID('CategoryImage', $id);
		}
		catch (Exception $exc)
		{			  	
		  	$success = false;
		}
		  		  
		$resp = new RawResponse();
	  	$resp->setContent($success);
		return $resp;
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
	
	private function resizeImage(CategoryImage $image, $resizer)
	{
	  	$publicRoot = ClassLoader::getRealPath('public') . '/';
		  
		foreach ($this->imageSizes as $key => $size)
	  	{
			$filePath = $publicRoot . $image->getPath($key);
			$res = $resizer->resize($size[0], $size[1], $filePath);
			if (!$res)
			{
			  	break;
			}
		}
		
		return $res;	  
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