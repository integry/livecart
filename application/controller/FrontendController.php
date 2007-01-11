<?php

ClassLoader::import("application.controller.BaseController");

/**
 * Base class for all front-end related controllers
 *
 * @package application.controller
 */
abstract class FrontendController extends BaseController 
{
	protected $categoryID;
	
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

		$currentCategory = Category::getInstanceByID($this->categoryID);		
		$path = $currentCategory->getPathNodeArray();
		
		print_r($path);
		exit;
		
		$response = new BlockResponse();
		$response->setValue('categories', $categories->toArray());
		$response->setValue('currentID', $this->categoryID);
		return $response;
	}
}

?>