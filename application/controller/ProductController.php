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
		ClassLoader::import('application.model.presentation.ProductPresentation');

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

		// filter empty attributes
		foreach ($productArray['attributes'] as $key => $attr)
		{
			if ((empty($attr['value']) && empty($attr['values']) && empty($attr['value_lang'])))
			{
				unset($productArray['attributes'][$key]);
			}
		}

		// attribute summary
		$productArray['listAttributes'] = array();
		foreach ($productArray['attributes'] as $key => $attr)
		{
			if ($attr['SpecField']['isDisplayedInList'])
			{
				$productArray['listAttributes'][] = $attr;
			}

			if (!$attr['SpecField']['isDisplayed'])
			{
				unset($productArray['attributes'][$key]);
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
		$response->set('quantity', $quantity);
		$response->set('currency', $this->request->get('currency', $this->application->getDefaultCurrencyCode()));
		$response->set('catRoute', $catRoute);

		// product options
		$options = $product->getOptions(true)->toArray();
		$response->set('allOptions', $options);

		foreach ($options as $key => $option)
		{
			if (!$option['isDisplayed'])
			{
				unset($options[$key]);
			}
		}
		$response->set('options', $options);

		// add to cart form
		$response->set('cartForm', $this->buildAddToCartForm($options));

		// related products
		$related = $this->getRelatedProducts($product);

		// items purchased together
		$together = $product->getProductsPurchasedTogether($this->config->get('NUM_PURCHASED_TOGETHER'), true);

		$spec = array();
		foreach ($related as $key => $group)
		{
			foreach ($related[$key] as $i => &$prod)
			{
				$spec[] =& $related[$key][$i];
			}
		}

		foreach ($together as &$prod)
		{
			$spec[] =& $prod;
		}

		ProductSpecification::loadSpecificationForRecordSetArray($spec);

		$response->set('related', $related);
		$response->set('together', $together);

		if (isset($manFilter))
		{
			$response->set('manufacturerFilter', $manFilter);
		}

		/*
		// display theme
		if ($theme = ProductPresentation::getThemeByProduct($product))
		{
			$this->application->setTheme($theme->getTheme());
		}
		*/

		return $response;
	}

	public function buildAddToCartValidator($options)
	{
		ClassLoader::import("framework.request.validator.Form");
		$validator = new RequestValidator("addToCart", $this->request);

		// option validation
		foreach ($options as $option)
		{
			if ($option['isRequired'])
			{
				$validator->addCheck('option_' . $option['ID'], new IsNotEmptyCheck($this->translate('_err_option_' . $option['type'])));
			}
		}

		return $validator;
	}

	/**
	 * @return Form
	 */
	private function buildAddToCartForm($options)
	{
		ClassLoader::import("framework.request.validator.Form");

		$form = new Form($this->buildAddToCartValidator($options));
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
