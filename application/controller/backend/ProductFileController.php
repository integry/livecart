<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @role admin.store.product
 */
class ProductFileController extends StoreManagementController 
{
	public function index()
	{
	    $product = Product::getInstanceByID((int)$this->request->getValue('id'));
	    
	    $response = new ActionResponse();
	    
		$languages = array();
		foreach($this->store->getLanguageList()->toArray() as $language) $languages[$language['ID']] = $language;
		$response->setValue('languages', $languages);
		
	    $response->setValue('productID', $product->getID());
		$response->setValue('productFilesWithGroups', $product->getFilesMergedWithGroupsArray());
	    
	    return $response;
	}

	/**
	 * @return RequestValidator
	 */
    private function buildValidator($existingProductFile = true)
    {
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("productFileValidator", $this->request);

		$validator->addCheck('title_' . $this->store->getDefaultLanguageCode(), new IsNotEmptyCheck('_err_file_title_is_empty'));
		$validator->addCheck('allowDownloadDays', new IsNumericCheck('_err_allow_download_days_should_be_a_number'));
		$validator->addCheck('allowDownloadDays', new IsNotEmptyCheck('_err_allow_download_days_is_empty'));
		if(!$existingProductFile) $validator->addCheck('uploadFile', new IsFileUploadedCheck('_err_file_could_not_be_uploaded_to_the_server'));
		if($existingProductFile) $validator->addCheck('fileName', new IsNotEmptyCheck('_err_fileName_should_not_be_empty'));

		return $validator;
    }
	
	public function save()
	{
	    $response = new ActionResponse();
	    
	    $validator = $this->buildValidator((int)$this->request->getValue('ID'));
	    if($validator->isValid())
	    {
		    $product = Product::getInstanceByID((int)$this->request->getValue('productID'));
	        $uploadFile = $this->request->getValue('uploadFile');
		    
		    if($id = (int)$this->request->getValue('ID'))
		    {
		        $productFile = ProductFile::getInstanceByID($id, ActiveRecord::LOAD_DATA);
		        $productFile->fileName->set($this->request->getValue('fileName'));
		        
		        if($this->request->isValueSet('uploadFile')) {
		            $productFile->storeFile($uploadFile['tmp_name'], $uploadFile['name']);
		        }
		    }
		    else
		    {
		        $productFile = ProductFile::getNewInstance($product, $uploadFile['tmp_name'], $uploadFile['name']);
		    }
		    
		    foreach ($this->store->getLanguageArray(true) as $lang)
	   		{
	   			if ($this->request->isValueSet('title_' . $lang))
	    			$productFile->setValueByLang('title', $lang, $this->request->getValue('title_' . $lang));
	
	   			if ($this->request->isValueSet('description_' . $lang))
	    			$productFile->setValueByLang('description', $lang, $this->request->getValue('description_' . $lang));
	   		}
	   		$productFile->allowDownloadDays->set((int)$this->request->getValue('allowDownloadDays'));
	   		
	   		$productFile->save();
	   		
		    $response->setValue('status', 'success');
		    $response->setValue('productFile', $productFile->toArray());
	    }
	    else
	    {
		    $response->setValue('status', 'failure');
		    $response->setValue('errors', $validator->getErrorList());
	    }
	    
	    return $response;
	}

	public function edit()
	{
	    $productFile = ProductFile::getInstanceByID((int)$this->request->getValue('id'), ActiveRecord::LOAD_DATA);
	    
	    return new JSONResponse($productFile->toArray());
	}

	public function delete()
	{
	    ProductFile::getInstanceByID((int)$this->request->getValue('id'))->delete();
	}
	
	public function download()
	{
	    $productFile = ProductFile::getInstanceByID((int)$this->request->getValue('id'), ActiveRecord::LOAD_DATA);

	    return new ObjectFileResponse($productFile);
	}

	public function sort()
	{ 
        $target = $this->request->getValue('target');    
        preg_match('/_(\d+)$/', $target, $match); // Get group. 

        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            if(empty($key)) continue;
            
            $file = ProductFile::getInstanceByID((int)$key); 
            $file->position->set((int)$position);
            
            if(isset($match[1])) $file->productFileGroup->set(ProductFileGroup::getInstanceByID((int)$match[1])); 
            else $file->productFileGroup->setNull();
            
            $file->save();
        }
        
        return new JSONResponse(array('status' => 'success'));
	}
}
?>