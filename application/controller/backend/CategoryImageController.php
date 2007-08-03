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
        if(parent::delete())
        {
            return new JSONResponse(false, 'success');
        }
        else
        {
            return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_category_image'));
        }
    }

	/**
	 * @role sort
	 */
	public function saveOrder()
	{
        parent::saveOrder();
        
        return new JSONResponse(false, 'success', $this->translate('_category_images_were_successfully_reordered'));
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