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
 */
class CategoryImageController extends ObjectImageController
{
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
    
    public function index()
    {
        return parent::index();
    }
		
    public function upload()
    {
        return parent::upload();
    }

	public function save()
	{
        return parent::save();
    }
	
	public function delete()
	{
        return parent::delete();
    }

	public function saveOrder()
	{
        return parent::saveOrder();
    }    
}	
	  
?>