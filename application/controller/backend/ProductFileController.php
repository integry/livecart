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

	public function save()
	{
	    $response = new ActionResponse();
	    $product = Product::getInstanceByID((int)$this->request->getValue('productID'));
	        $uploadFile = $this->request->getValue('uploadFile');
	    
	    if($id = (int)$this->request->getValue('ID'))
	    {
	        echo 'aaaaa';
	    }
	    else
	    {
	        $productFile = ProductFile::getNewInstance($product, $uploadFile['tmp_name'], $uploadFile['name']);
	    }
	    
   		    print_r($_POST);
	    foreach ($this->store->getLanguageArray(true) as $lang)
   		{
   			if ($this->request->isValueSet('title_' . $lang))
    			$productFile->setValueByLang('title', $lang, $this->request->getValue('title_' . $lang));

   			if ($this->request->isValueSet('description_' . $lang))
    			$productFile->setValueByLang('description', $lang, $this->request->getValue('description_' . $lang));
   		}
   		$productFile->allowDownloadDays->set((int)$this->request->getValue('allowDownloadDays'));
   		
   		$productFile->save();
   		   		
	    $response->setValue('status', 'failure');
	    $response->setValue('errors', array('field_1' => 'description_1', 'field_2' => 'description_2'));
	    $response->setValue('ID', rand(1, 1000));
	    
	    return $response;
	}
}

?>