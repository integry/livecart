<?php

ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.controller.FrontendController');
ClassLoader::import('application.controller.CategoryController');
ClassLoader::import('framework.request.validator.Form');
ClassLoader::import('framework.request.validator.RequestValidator');

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

		$product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA, array('ProductImage', 'Manufacturer'));
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
		//ProductSpecification::loadSpecificationForProductArray($productArray);

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

		// ratings
		if ($this->config->get('ENABLE_RATINGS'))
		{
			if ($product->ratingCount->get() > 0)
			{
				// rating summaries
				ClassLoader::import('application.model.product.ProductRatingSummary');
				$response->set('rating', ProductRatingSummary::getProductRatingsArray($product));
			}

			ClassLoader::import('application.model.category.ProductRatingType');
			$ratingTypes = ProductRatingType::getProductRatingTypeArray($product);
			$response->set('ratingTypes', $ratingTypes);
			$response->set('ratingForm', $this->buildRatingForm($ratingTypes, $product));
			$response->set('isRated', $this->isRated($product));
			$response->set('isLoginRequiredToRate', $this->isLoginRequiredToRate());
			$response->set('isPurchaseRequiredToRate', $this->isPurchaseRequiredToRate($product));
		}

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

		// ratings
		if ($this->config->get('ENABLE_RATINGS'))
		{
			$response->set('ratings', ProductRatingSummary::getProductRatingsArray($product));
		}

		// display theme
		if ($theme = ProductPresentation::getThemeByProduct($product))
		{
			$this->application->setTheme($theme->getTheme());
		}

		return $response;
	}

	public function rate()
	{
		$product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA);
		$ratingTypes = ProductRatingType::getProductRatingTypes($product);
		$validator = $this->buildRatingValidator($ratingTypes->toArray(), $product);
		if ($validator->isValid())
		{
			foreach ($ratingTypes as $type)
			{
				$rating = ProductRating::getNewInstance($product, $type);
				$rating->rating->set($this->request->get('rating_'  . $type->getID()));
				$rating->save();
			}

			setcookie('rating_' . $product->getID(), true, strtotime('+6 months'), $this->router->getBaseDirFromUrl());

			$msg = $this->translate('_msg_rating_added');
			$redirect = new ActionRedirectResponse('product', 'index', array('id' => $product->getID()));

			if ($this->isAjax())
			{
				return new JSONResponse(array('message' => $msg), 'success');
			}
			else
			{
				$this->setMessage($msg);
				return $redirect;
			}
		}
		else
		{
			if ($this->isAjax())
			{
				return new JSONResponse(array('errors' => $validator->getErrorList()));
			}
			else
			{
				return $redirect;
			}
		}
	}

	public function buildAddToCartValidator($options)
	{
		$validator = new RequestValidator("addToCart", $this->getRequest());

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
		$form = new Form($this->buildAddToCartValidator($options));
		$form->enableClientSideValidation(false);

		return $form;
	}

	private function buildRatingForm($ratingTypes, Product $product)
	{
		return new Form($this->buildRatingValidator($ratingTypes, $product));
	}

	private function buildRatingValidator($ratingTypes, Product $product)
	{
		$validator = new RequestValidator("productRating", $this->getRequest());

		// option validation
		foreach ($ratingTypes as $type)
		{
			$validator->addCheck('rating_' . $type['ID'], new IsNotEmptyCheck($this->translate('_err_no_rating_selected')));
		}

		if ($this->isRated($product))
		{
			$validator->addCheck('rating', new VariableCheck(true, new IsEmptyCheck($this->translate('_err_already_rated'))));
		}

		$validator->addCheck('rating', new VariableCheck($this->isLoginRequiredToRate(), new IsEmptyCheck($this->maketext('_msg_rating_login_required', $this->router->createUrl(array('controller' => 'user', 'action' => 'login'))))));
		$validator->addCheck('rating', new VariableCheck($this->isPurchaseRequiredToRate($product), new IsEmptyCheck($this->translate('_msg_rating_purchase_required'))));

		return $validator;
	}

	private function getRatingTypes(Product $product)
	{

	}

	private function isRated(Product $product)
	{
		return !empty($_COOKIE['rating_' . $product->getID()]);
	}

	private function isLoginRequiredToRate()
	{
		return $this->user->isAnonymous() && !$this->config->get('ENABLE_ANONYMOUS_RATINGS');
	}

	private function isPurchaseRequiredToRate(Product $product)
	{
		if ($this->config->get('REQUIRE_PURCHASE_TO_RATE'))
		{
			if ($this->user->isAnonymous())
			{
				return true;
			}

			if (!is_null($this->isPurchaseRequiredToRate))
			{
				return $this->isPurchaseRequiredToRate;
			}

			ClassLoader::import('application.model.order.CustomerOrder');
			$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('OrderedItem', 'productID'), $product->getID()));
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
			$f->setLimit(1);

			$this->isPurchaseRequiredToRate = ActiveRecordModel::getRecordCount('OrderedItem', $f, array('CustomerOrder')) < 1;
		}
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
