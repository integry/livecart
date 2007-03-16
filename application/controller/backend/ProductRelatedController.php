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
class ProductRelatedController extends StoreManagementController 
{
	public function index()
	{		
	    $response = new ActionResponse();

	    $response->setValue('id', $this->request->getValue('id'));
	    $response->setValue('categoryID', $this->request->getValue('categoryID'));
		$response->setValue("productID", $this->request->getValue('id'));
	    
	    return $response;
	}
	
	public function selectProduct()
	{
	    $response = new ActionResponse();	    
	    
		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());
		$response->setValue("categoryList", $categoryList->toArray($this->store->getDefaultLanguageCode()));
		
		return $response;
	}
	
	public function related()
	{
	    //if()
	    $response = new ActionResponse();
	    
	    $response->setValue('product', Product::getInstanceByID((int)$this->request->getValue('id'), ActiveRecord::LOAD_DATA, ActiveRecord::LOAD_REFERENCES)->toArray());
	    
	    return $response;
	}
}

?>