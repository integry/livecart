<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import('library.image.ImageManipulator');

/**
 *	Implements common logic for handling image uploads and management for various business objects
 *	like products and product categories.
 */
abstract class ObjectImageController extends StoreManagementController
{
    abstract protected function getModelClass();
    abstract protected function getOwnerClass();
    abstract protected function getForeignKeyName();
	    
    public function index()
	{
		$owner = ActiveRecordModel::getInstanceByID($this->getOwnerClass(), (int)$this->request->getValue('id'));
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle($this->getModelClass(), $this->getForeignKeyName()), $owner->getID()));
		$filter->setOrder(new ARFieldHandle($this->getModelClass(), 'position'));
		
		$imageArray = ActiveRecordModel::getRecordSetArray($this->getModelClass(), $filter);
				
		$response = new ActionResponse();
		$response->setValue('form', $this->buildForm($owner->getID()));
		$response->setValue('ownerId', $owner->getID());
		$response->setValue('images', json_encode($imageArray));
		return $response;		  
	}    
	
	public function upload()
	{	
		$ownerId = $this->request->getValue('ownerId');
		
		$owner = ActiveRecordModel::getInstanceByID($this->getOwnerClass(), $ownerId);
			  	
		$validator = $this->buildValidator($ownerId);
		
		if (!$validator->isValid())
		{
		  	$errors = $validator->getErrorList();
			$result = array('error' => $errors['image']);
		}
		else
		{
		  	ActiveRecord::beginTransaction();

			$catImage = call_user_func_array(array($this->getModelClass(), 'getNewInstance'), array($owner));

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
		$response->setValue('ownerId', $ownerId);		
		$response->setValue('result', json_encode($result));		
		return $response;
	}	
	
	public function save()
	{
		ActiveRecord::beginTransaction();	  
		
	  	try
		{  
			$image = ActiveRecord::getInstanceById($this->getModelClass(), $this->request->getValue('imageId'), true);
			
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
		$response->setValue('ownerId', $this->request->getValue('ownerId'));		
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
			call_user_func_array(array($this->getModelClass(), 'deleteByID'), array($this->request->getValue('id')));
		  	$success = true;
		}
		catch (ARNotFoundException $exc)
		{
			$success = false;  
		}
		
		return new RawResponse($success);
	}		
	
	/**
	 * Save image order
	 * @return RawResponse
	 */
	public function saveOrder()
	{
	  	$ownerId = $this->request->getValue('ownerId');
	  	
		$order = $this->request->getValue('catImageList_' . $ownerId, $this->request->getValue('prodImageList_' . $ownerId));
			
		foreach ($order as $key => $value)
		{
			$update = new ARUpdateFilter();
			$update->setCondition(new EqualsCond(new ARFieldHandle($this->getModelClass(), 'ID'), $value));
			$update->addModifier('position', $key);
			ActiveRecord::updateRecordSet($this->getModelClass(), $update);  	
		}

        // set owners main image
        if (isset($order[0]))
        {
            $owner = ActiveRecordModel::getInstanceByID($this->getOwnerClass(), $ownerId);
            $owner->defaultImage->set(ActiveRecordModel::getInstanceByID($this->getModelClass(), $order[0]));
            $owner->save();            
        }

		$resp = new RawResponse();
	  	$resp->setContent($this->request->getValue('draggedId'));
		return $resp;		  	
	}				
	
	/**
	 * Builds an image upload form validator
	 *
	 * @return RequestValidator
	 */
	protected function buildValidator($catId)
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator($this->getModelClass() . "_" . $catId, $this->request);

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
	protected function buildForm($catId)
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildValidator($catId));		
	}	
}

?>