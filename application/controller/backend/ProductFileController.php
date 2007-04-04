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
}

?>