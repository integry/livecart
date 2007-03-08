<?php

ClassLoader::import('application.model.product.Product');

/**
 * 
 *
 * @package application.controller
 */
class ProductController extends FrontendController
{
	public function index()
	{
        $product = Product::getInstanceByID($this->request->getValue('id'), Product::LOAD_DATA);    	
        $product->loadSpecification();
	} 
}

?>