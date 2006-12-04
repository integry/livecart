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

	/**
	 * Shows products search form.
	 * @return ActionResponse
	 */
	/*
    public function index()
    {
		$count = Product::getProductsCount();

	  	$filter = new DataGridFilter("Product", $this->request->toArray());
		$filter->selector()->addField("Product.sku", "sku");
		$filter->selector()->addField("Product.URL", "URL");

		$filter->sorter()->addField("Product.sku", "sku");
		$filter->sorter()->addField("Product.URL", "URL");
		$filter->pager()->setOptions($count, 10);

		$display = new DataGridFilterDisplayer($filter);

		$recordSet = Product::getRecordSet("Product", $filter->getArSelectFilter(), true);

		//datagrid
		$grid =	new DataGridArrayDisplayer();
		$grid->setDataSource(Product::getArrayFromArSet($recordSet, "en"));
		$grid->setSortedFields($filter->sorter()->getFields());

		$grid->addColumn("ID", "ID", "", "width: 30px");
		$grid->addColumnComplex(array("ID", "name"), get_class($this), 'formatProduct', "Product", "width: 250px");
		$grid->addColumn("sku", "sku", "", "width: 50px");


		//response
		$response = new ActionResponse();
		$response->setValue('filter', $display->display());
		$response->setValue('grid', $grid->display());

		//application rendering
		$app = Application::getInstance();
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/document.js");
		$app->getRenderer()->appendValue("BODY_ONLOAD", $display->displayOnLoad(1, 1));
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/DataGrid/datagrid.js");

		return $response;
	}
	*/
}
?>