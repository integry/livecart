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
     * @role update
     */
	public function saveOrder()
	{
        return parent::saveOrder();
    }    

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
    
}	
?>