<?php

ClassLoader::import("application.controller.backend.abstract.ObjectImageController");
ClassLoader::import('application.model.product.Product');
ClassLoader::import("application.model.product.ProductImage");

/**
 * Product Image controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 * @role product
 */
class ProductImageController extends ObjectImageController
{
	protected function getModelClass()
	{
        return 'ProductImage';
    }
    
	protected function getOwnerClass()
	{
        return 'Product';
    }
    
    protected function getForeignKeyName()
    {
		return 'productID';
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