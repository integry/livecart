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
        $product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA, array('DefaultImage' => 'ProductImage', 'Manufacturer'));    	
        $product->loadSpecification();
        $product->loadPricing();
		        
		$this->category = $product->category->get();
		$this->categoryID = $product->category->get()->getID();
		
        // get category path for breadcrumb
		$path = $product->category->get()->getPathNodeSet();
		include_once(ClassLoader::getRealPath('application.helper') . '/function.categoryUrl.php');
		foreach ($path as $node)
		{
			$nodeArray = $node->toArray();
			$url = createCategoryUrl(array('data' => $nodeArray), $this->application);
			$this->addBreadCrumb($nodeArray['name_lang'], $url);
		}
        
		// add filters to breadcrumb
		CategoryController::getAppliedFilters();

		$params = array('data' => $nodeArray, 'filters' => array());
		foreach ($this->filters as $filter)
		{
			$f = $filter->toArray();
			$params['filters'][] = $f;
			$url = createCategoryUrl($params, $this->application);
			$this->addBreadCrumb($f['name_lang'], $url);
		}

        $productArray = $product->toArray();

        // attribute summary
        $productArray['listAttributes'] = array();
        foreach ($productArray['attributes'] as $attr)
        {
            if ($attr['SpecField']['isDisplayedInList'] && (!empty($attr['value']) || !empty($attr['values']) || !empty($attr['value_lang'])))
            {
                $productArray['listAttributes'][] = $attr;
            }
        }

        // add product title to breacrumb
        $this->addBreadCrumb($productArray['name_lang'], '');
        		
        // allowed shopping cart quantities
        $quantities = range(max($product->minimumQuantity->get(), 1), 30);
        $quantity = array_combine($quantities, $quantities);		
		
		// manufacturer filter
		$f = new ManufacturerFilter($product->manufacturer->get()->getID(), $product->manufacturer->get()->name->get());
		
		// get category page route
        end($this->breadCrumb);
        $last = prev($this->breadCrumb);
        $catRoute = $this->router->getRouteFromUrl($last['url']);

		$response = new ActionResponse();
        $response->set('product', $productArray);        
        $response->set('category', $productArray['Category']);        
        $response->set('images', $product->getImageArray());
        $response->set('related', $this->getRelatedProducts($product));
        $response->set('quantity', $quantity);
        $response->set('cartForm', $this->buildAddToCartForm());        
		$response->set('currency', $this->request->get('currency', $this->application->getDefaultCurrencyCode())); 
        $response->set('manufacturerFilter', $f);
        $response->set('catRoute', $catRoute);
        return $response;        
	} 
	
	/**
	 * @return Form
	 */
	private function buildAddToCartForm()
	{
		ClassLoader::import("framework.request.validator.Form");        
        $form = new Form(new RequestValidator("addToCart", $this->request));
        $form->enableClientSideValidation(false);
        return $form;
    }	
    
    private function getRelatedProducts(Product $product)
    {
		// get related products
		$related = $product->getRelatedProductsWithGroupsArray();

		$rel = array();
		foreach ($related as $r)
		{
			$p = $r['RelatedProduct'];
            if (isset($r['ProductRelationshipGroup']))
            {
                $p['ProductRelationshipGroup'] = $r['ProductRelationshipGroup'];                
            }
            $rel[] = $p;	
		}
		
        ProductPrice::loadPricesForRecordSetArray($rel);

		// sort related products into groups
        $byGroup = array();
		foreach ($rel as $r)
		{
            $groupID = isset($r['ProductRelationshipGroup']) ? $r['ProductRelationshipGroup']['ID'] : 0;
            $byGroup[$groupID][] = $r;
        }
        
        return $byGroup;
    }
}

?>