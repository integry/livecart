<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.product.Product");

//ClassLoader::import("library.DataGrid.*");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @role admin.store.product
 */
class ProductController extends StoreManagementController {


	public function index()
	{
		ClassLoader::import("application.model.category.Category");

		$category = Category::getInstanceByID($this->request->getValue("id"));
		$path = $category->getPathNodes();

		$response = new ActionResponse();
		$response->setValue("path", $path);
		return $response;
	}
}
?>