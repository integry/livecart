<?php

ClassLoader::import("application.controller.backend.abstract.ObjectImageController");
ClassLoader::import('application.model.category.Category');
ClassLoader::import("application.model.category.CategoryImage");

/**
 * Product Category Image controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 * @role category
 */
class CategoryImageController extends ObjectImageController
{
	/**
	 * @role update
	 */
    public function index()
    {
        return parent::index();
    }

	/**
	 * @role update
	 */
    public function upload()
    {
        return parent::upload();
    }

	/**
	 * @role update
	 */
	public function save()
	{
        return parent::save();
    }

	/**
	 * @role update
	 */
	public function delete()
	{
        return parent::delete();
    }

	/**
	 * @role sort
	 */
	public function saveOrder()
	{
        return parent::saveOrder();
    }    

	protected function getModelClass()
	{
        return 'CategoryImage';
    }
    
	protected function getOwnerClass()
	{
        return 'Category';
    }
    
    protected function getForeignKeyName()
    {
		return 'categoryID';
	}   
    	
}	
	  
?>