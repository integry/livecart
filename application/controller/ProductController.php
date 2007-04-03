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
        $product = Product::getInstanceByID($this->request->getValue('id'), Product::LOAD_DATA, array('DefaultImage' => 'ProductImage', 'Manufacturer'));    	
        $product->loadSpecification();
        $product->loadPricing();
		        
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
        
		// get related products
		$related = $product->getRelatedProductsWithGroupsArray();
		$rel = array();
		foreach ($related as $r)
		{
			$rel[] = $r['RelatedProduct'];	
		}
		
		ProductPrice::loadPricesForRecordSetArray($rel);
		
		$response = new ActionResponse();
        $response->setValue('product', $productArray);        
        $response->setValue('images', $product->getImageArray());
        $response->setValue('related', $rel);
		$response->setValue('currency', $this->request->getValue('currency', $this->store->getDefaultCurrencyCode())); 

        return $response;        
	} 
}

?>