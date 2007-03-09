<?php

ClassLoader::import('application.model.product.Product');

/**
 * 
 *
 * @package application.controller
 */
class ProductController extends FrontendController
{  	
    public $filters = array();
  	
	public function index()
	{
        $product = Product::getInstanceByID($this->request->getValue('id'), Product::LOAD_DATA);    	
        $product->loadSpecification();
        
		$this->category = $product->category->get();
		
        // get category path for breadcrumb
		$path = $product->category->get()->getPathNodeSet();
		include_once(ClassLoader::getRealPath('application.helper') . '/function.categoryUrl.php');
		foreach ($path as $node)
		{
			$nodeArray = $node->toArray();
			$url = smarty_function_categoryUrl(array('data' => $nodeArray), false);
			$this->addBreadCrumb($nodeArray['name_lang'], $url);
		}
        
		// add filters to breadcrumb
		CategoryController::getAppliedFilters();

		$params = array('data' => $nodeArray, 'filters' => array());
		foreach ($this->filters as $filter)
		{
			$f = $filter->toArray();
			$params['filters'][] = $f;
			$url = smarty_function_categoryUrl($params, false);
			$this->addBreadCrumb($f['name_lang'], $url);
		}

        $productArray = $product->toArray();
        
        // add product title to breacrumb
        $this->addBreadCrumb($productArray['name_lang'], '');
        
        $response = new ActionResponse();
        $response->setValue('product', $productArray);        
        return $response;        
	} 
}

?>