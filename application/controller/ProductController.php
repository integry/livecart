<?php

ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.controller.FrontendController');
ClassLoader::import('application.controller.CategoryController');
ClassLoader::import('framework.request.validator.Form');
ClassLoader::import('framework.request.validator.RequestValidator');
ClassLoader::import('application.model.presentation.ProductPresentation');

/**
 *
 * @author Integry Systems
 * @package application.controller
 */
class ProductController extends FrontendController
{
	public $filters = array();

	const REVIEWS_PER_PAGE = 1;

	public function index()
	{
		$product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA, array('ProductImage', 'Manufacturer'));

		if (!$product->isEnabled->get() || $product->parent->get())
		{
			throw new ARNotFoundException('Product', $product->getID());
		}

		$product->loadPricing();

		$this->category = $product->getCategory();
		$this->categoryID = $product->getCategory()->getID();

		// get category path for breadcrumb
		$path = $product->category->get()->getPathNodeArray();
		include_once(ClassLoader::getRealPath('application.helper.smarty') . '/function.categoryUrl.php');
		include_once(ClassLoader::getRealPath('application.helper.smarty') . '/function.productUrl.php');
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
		$this->addBreadCrumb($productArray['name_lang'], createProductUrl(array('product' => $productArray), $this->application));

		// allowed shopping cart quantities
		$quantities = range(max($product->minimumQuantity->get(), 1), $product->minimumQuantity->get() + 30);
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
		$options = $product->getOptionsArray();
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

		// variations
		$variations = $product->getVariationData($this->application);
		$response->set('variations', $variations);

		// add to cart form
		$response->set('cartForm', $this->buildAddToCartForm($options, $variations));

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

		// reviews
		if ($this->config->get('ENABLE_REVIEWS') && $product->reviewCount->get() && ($numReviews = $this->config->get('NUM_REVIEWS_IN_PRODUCT_PAGE')))
		{
			$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('ProductReview', 'isEnabled'), true));
			$f->setLimit($numReviews);
			$reviews = $product->getRelatedRecordSetArray('ProductReview', $f);
			$this->pullRatingDetailsForReviewArray($reviews);
			$response->set('reviews', $reviews);
		}

		// bundled products
		if ($product->isBundle())
		{
			$bundledProducts = array();
			foreach (ProductBundle::getBundledProductArray($product) as $bundled)
			{
				$bundledProducts[] = $bundled['RelatedProduct'];
			}

			ProductPrice::loadPricesForRecordSetArray($bundledProducts);
			$response->set('bundledProducts', $bundledProducts);

			$currency = Currency::getInstanceByID($this->getRequestCurrency());
			$total = ProductBundle::getTotalBundlePrice($product, $currency);
			$response->set('bundleTotal', $currency->getFormattedPrice($total));

			$saving = $total - $product->getPrice($currency);
			$response->set('bundleSavingTotal', $currency->getFormattedPrice($saving));
			$response->set('bundleSavingPercent', round(($saving / $total) * 100));
		}

		// contact form
		if ($this->config->get('PRODUCT_INQUIRY_FORM'))
		{
			$response->set('contactForm', $this->buildContactForm());
		}

		// display theme
		if ($theme = ProductPresentation::getThemeByProduct($product))
		{
			$this->application->setTheme($theme->getTheme());
		}

		// discounted pricing
		$response->set('quantityPricing', $product->getPricingHandler()->getDiscountPrices($this->user, $this->getRequestCurrency()));

		$this->product = $product;

		return $response;
	}

	public function rate()
	{
		$product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA);
		$ratingTypes = ProductRatingType::getProductRatingTypes($product);

		$validator = $this->buildRatingValidator($ratingTypes->toArray(), $product, true);

		$redirect = new ActionRedirectResponse('product', 'index', array('id' => $product->getID()));

		if ($validator->isValid())
		{
			$msg = $this->translate('_msg_rating_added');

			if ($this->isAddingReview())
			{
				$review = ProductReview::getNewInstance($product, $this->user);
				$review->loadRequestData($this->request);
				$review->ip->set($this->request->getIpLong());

				// approval status
				$approval = $this->config->get('APPROVE_REVIEWS');
				$review->isEnabled->set(('APPROVE_REVIEWS_AUTO' == $approval) || (('APPROVE_REVIEWS_USER' == $approval) && !$this->user->isAnonymous()));

				$review->save();

				$msg = $this->translate('_msg_review_added');
			}

			foreach ($ratingTypes as $type)
			{
				$rating = ProductRating::getNewInstance($product, $type, $this->user);
				$rating->rating->set($this->request->get('rating_'  . $type->getID()));
				if (isset($review))
				{
					$rating->review->set($review);
				}
				$rating->ip->set($this->request->getIpLong());
				$rating->save();
			}

			if ($this->isAjax())
			{
				$response = new JSONResponse(array('message' => $msg), 'success');
			}
			else
			{
				$this->setMessage($msg);
				$response = $redirect;
			}

			$response->setCookie('rating_' . $product->getID(), true, strtotime('+6 months'), $this->router->getBaseDirFromUrl());
			return $response;
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

	public function reviews()
	{
		if (!$this->config->get('ENABLE_REVIEWS'))
		{
			throw new HTTPStatusException(404);
		}

		$response = $this->index();

		$page = $this->request->get('page', 1);
		$perPage = $this->config->get('REVIEWS_PER_PAGE');
		$offsetStart = ($this->request->get('page', 1) - 1) * $perPage;

		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('ProductReview', 'isEnabled'), true));
		$f->setLimit($perPage, $offsetStart);
		$f->setOrder(new ARFieldHandle('ProductReview', 'dateCreated'), 'DESC');
		$reviews = $this->product->getRelatedRecordSetArray('ProductReview', $f);
		$this->pullRatingDetailsForReviewArray($reviews);

		$response->set('reviews', $reviews);
		$response->set('offsetStart', $offsetStart + 1);
		$response->set('offsetEnd', min($offsetStart + $perPage, $this->product->reviewCount->get()));
		$response->set('page', $page);
		$response->set('perPage', $perPage);
		$response->set('url', $this->router->createUrl(array('controller' => 'product', 'action' => 'reviews', 'id' => $this->product->getID(), 'page' => '_000_')));

		$this->addBreadCrumb($this->translate('_reviews'), '');

		return $response;
	}

	public function sendContactForm()
	{
		$product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA);
		$redirect = new ActionRedirectResponse('product', 'index', array('id' => $product->getID()));

		$validator = $this->buildContactValidator();
		if ($validator->isValid())
		{
			$email = new Email($this->application);
			$email->setTemplate('contactForm/productInquiry');
			$email->setFrom($this->request->get('email'), $this->request->get('name'));
			$email->setTo($this->config->get('NOTIFICATION_EMAIL'), $this->config->get('STORE_NAME'));
			$email->set('message', $this->request->get('msg'));
			$email->set('product', $product->toArray());
			$email->send();

			$msg = $this->translate('_inquiry_form_sent');
			if ($this->isAjax())
			{
				$response = new JSONResponse(array('message' => $msg), 'success');
			}
			else
			{
				$this->setMessage($msg);
				$response = $redirect;
			}

			return $response;
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

	public function buildAddToCartValidator($options, $variations)
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

		if (isset($variations['variations']))
		{
			foreach ($variations['variations'] as $variation)
			{
				$validator->addCheck('variation_' . $variation['ID'], new IsNotEmptyCheck($this->translate('_err_option_0')));
			}
		}

		return $validator;
	}

	private function pullRatingDetailsForReviewArray(&$reviews)
	{
		$ids = $indexes = array();
		foreach ($reviews as $index => $review)
		{
			$ids[] = $review['ID'];
			$indexes[$review['ID']] = $index;
		}

		$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductRating', 'reviewID'), $ids));
		$f->setOrder(new ARFieldHandle('ProductRatingType', 'position'));
		$ratings = ActiveRecordModel::getRecordSetArray('ProductRating', $f, array('ProductRatingType'));

		foreach ($ratings as $rating)
		{
			$review =& $reviews[$indexes[$rating['reviewID']]];
			if (!isset($review['ratings']))
			{
				$review['ratings'] = array();
			}

			$review['ratings'][] = $rating;
		}
	}

	/**
	 * @return Form
	 */
	private function buildAddToCartForm($options, $variations)
	{
		$form = new Form($this->buildAddToCartValidator($options, $variations));
		$form->enableClientSideValidation(false);

		return $form;
	}

	private function buildRatingForm($ratingTypes, Product $product)
	{
		return new Form($this->buildRatingValidator($ratingTypes, $product));
	}

	private function buildRatingValidator($ratingTypes, Product $product, $isRating = false)
	{
		$validator = new RequestValidator("productRating", $this->getRequest());

		// option validation
		foreach ($ratingTypes as $type)
		{
			$validator->addCheck('rating_' . $type['ID'], new IsNotEmptyCheck($this->translate('_err_no_rating_selected')));
		}

		if ($this->isRated($product, $isRating))
		{
			$validator->addCheck('rating', new VariableCheck(true, new IsEmptyCheck($this->translate('_err_already_rated'))));
		}

		$validator->addCheck('rating', new VariableCheck($this->isLoginRequiredToRate(), new IsEmptyCheck($this->maketext('_msg_rating_login_required', $this->router->createUrl(array('controller' => 'user', 'action' => 'login'))))));
		$validator->addCheck('rating', new VariableCheck($this->isPurchaseRequiredToRate($product), new IsEmptyCheck($this->translate('_msg_rating_purchase_required'))));

		if ($this->isAddingReview())
		{
			$validator->addCheck('nickname', new IsNotEmptyCheck($this->translate('_err_no_review_nickname')));
			$validator->addCheck('title', new IsNotEmptyCheck($this->translate('_err_no_review_summary')));
			$validator->addCheck('text', new IsNotEmptyCheck($this->translate('_err_no_review_text')));
		}

		return $validator;
	}

	private function isAddingReview()
	{
		return $this->config->get('ENABLE_REVIEWS') && ($this->config->get('REVIEWS_WITH_RATINGS') || ($this->request->get('nickname') || $this->request->get('title') || $this->request->get('text')));
	}

	private function isRated(Product $product, $isRating = false)
	{
		if (!empty($_COOKIE['rating_' . $product->getID()]))
		{
			return true;
		}

		if ($isRating)
		{
			ClassLoader::importNow("application.helper.getDateFromString");

			$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('ProductRating', 'productID'), $product->getID()));
			if (!$this->user->isAnonymous())
			{
				$cond = new EqualsCond(new ARFieldHandle('ProductRating', 'userID'), $this->user->getID());
			}
			else
			{
				if ($hours = $this->config->get('RATING_SAME_IP_TIME'))
				{
					$cond = new EqualsCond(new ARFieldHandle('ProductRating', 'ip'), $this->request->getIPLong());
					$cond->addAnd(new MoreThanCond(new ARFieldHandle('ProductRating', 'dateCreated'), getDateFromString('-' . $hours . ' hours')));
				}
			}

			if (isset($cond))
			{
				$f->mergeCondition($cond);
	//var_dump(ActiveRecordModel::getRecordSetArray('ProductRating', $f));
				return ActiveRecordModel::getRecordCount('ProductRating', $f) > 0;
			}
		}
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

			if (is_null($this->isPurchaseRequiredToRate))
			{
				ClassLoader::import('application.model.order.CustomerOrder');
				$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
				$f->mergeCondition(new EqualsCond(new ARFieldHandle('OrderedItem', 'productID'), $product->getID()));
				$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
				$f->setLimit(1);

				$this->isPurchaseRequiredToRate = ActiveRecordModel::getRecordCount('OrderedItem', $f, array('CustomerOrder')) < 1;
			}

			return $this->isPurchaseRequiredToRate;
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

	private function buildContactForm()
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildContactValidator());
	}

	public function buildContactValidator(Request $request = null)
	{
		$this->loadLanguageFile('ContactForm');
		ClassLoader::import("framework.request.validator.RequestValidator");

		$request = $request ? $request : $this->request;

		$validator = new RequestValidator("productContactForm", $request);
		$validator->addCheck('name', new IsNotEmptyCheck($this->translate('_err_name')));
		$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_email')));
		$validator->addCheck('msg', new IsNotEmptyCheck($this->translate('_err_message')));
		$validator->addCheck('surname', new MaxLengthCheck('Please do not enter anything here', 0));

		return $validator;
	}
}

?>