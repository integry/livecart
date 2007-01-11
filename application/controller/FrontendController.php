<?php

ClassLoader::import("application.controller.BaseController");

/**
 * Base class for all front-end related controllers
 *
 * @package application.controller
 */
abstract class FrontendController extends BaseController 
{
	public function init()
	{
	  	$this->setLayout('frontend');
	  	$this->addBlock('CATEGORY_BOX', 'boxCategory', 'block/box/category');
	}
	
	protected function boxCategoryBlock()
	{
		ClassLoader::import('application.model.category.Category');
		
		$rootCategory = Category::getInstanceByID(1);
		$categories = $rootCategory->getSubcategorySet();

		$response = new BlockResponse();
		$response->setValue('categories', $categories->toArray());
		return $response;
	}
}

?>