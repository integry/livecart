<?php

ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.controller.FrontendController');
ClassLoader::import('application.controller.CategoryController');

/**
 * 
 * @author Integry Systems
 * @package application.controller
 */
class ProductController extends FrontendController
{  	
	public $filters = array();
  	
	public function index()
	{
		$product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA, array('DefaultImage' => 'ProductImage', 'Manufacturer'));		
		$product->loadPricing();

		$this->category = $product->category->get();
		$this->categoryID = $product->category->get()->getID();
		
		// get category path for breadcrumb
		$path = $product->category->get()->getPathNodeArray();
		include_once(ClassLoader::getRealPath('application.helper.smarty') . '/function.categoryUrl.php');
		foreach ($path as $nodeArray)
		{
			$url = createCategoryUrl(array('data' => $nodeArray), $this->application);
			$this->addBreadCrumb($nodeArray['name_lang'], $url);
		}
		
		// add filters to breadcrumb
		CategoryController::getAppliedFilters();

		// for root category products
		if (!isset($nodeArray))
		{
			$nodeArray = array();
		}

		$params = array('data' => $nodeArray, 'filters' => array());
		foreach ($this->filters as $filter)
		{
			$f = $filter->toArray();
			$params['filters'][] = $f;
			$url = createCategoryUrl($params, $this->application);
			$this->addBreadCrumb($f['name_lang'], $url);
		}

		$productArray = $product->toArray();
		ProductSpecification::loadSpecificationForProductArray($productArray);
		
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
		if ($product->manufacturer->get())
		{
			$manFilter = new ManufacturerFilter($product->manufacturer->get()->getID(), $product->manufacturer->get()->name->get());
		}
		
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
		$response->set('catRoute', $catRoute);

		if (isset($manFilter))
		{
			$response->set('manufacturerFilter', $f);
		}

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
			if (!isset($r['RelatedProduct']))
			{
				continue;
			}

			$p = $r['RelatedProduct'];			
			
			// @todo: make ActiveRecord automatically recognize the correct parent object
			$p['DefaultImage'] = $r['DefaultImage'];
			
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
