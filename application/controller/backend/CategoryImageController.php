<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Product Category Image controller
 *
 * @package application.controller.backend
 * @author Rinalds Uzkalns <rinalds@integry.net>
 *
 */
class CategoryImageController extends StoreManagementController
{
	public function index()
	{
		$filter = new ARSelectFilter();
		$filter->setOrder(new ARFieldHandle('CategoryImage', 'position'), 'ASC');
		$images = ActiveRecord::getRecordSet('CategoryImage', $filter);
		
		$response = new ActionResponse();
		$response->setValue('images', $images->toArray());
		return $response;		  
	}
}	
	  
?>