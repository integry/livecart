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
	    $response = new ActionResponse();

	    $response->setValue('id', $this->request->getValue('id'));
	    $response->setValue('categoryID', $this->request->getValue('categoryID'));
	    
	    return $response;
	}
}

?>