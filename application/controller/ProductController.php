<?php

ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.controller.FrontendController');
ClassLoader::import('application.controller.CategoryController');
ClassLoader::import('application.model.presentation.CategoryPresentation', true);

/**
 *
 * @author Integry Systems
 * @package application.controller
 */
class ProductController extends FrontendController
{
	public $filters = array();

	public function init()
	{
		parent::init();

		$this->addBlock('PRODUCT-ATTRIBUTE-SUMMARY', 'attributeSummary', 'product/block/attributeSummary');
		$this->addBlock('PRODUCT-PURCHASE', 'purchase', 'product/block/purchase');
			$this->addBlock('PRODUCT-PRICE', 'price', 'product/block/price');
			$this->addBlock('PRODUCT-OPTIONS', 'options', 'product/block/options');
			$this->addBlock('PRODUCT-VARIATIONS', 'variations', 'product/block/variations');
			$this->addBlock('PRODUCT-TO-CART', 'addToCart', 'product/block/toCart');
			$this->addBlock('PRODUCT-ACTIONS', 'actions', 'product/block/actions');

		$this->addBlock('PRODUCT-IMAGES', 'images', 'product/block/images');

		$this->addBlock('PRODUCT-SUMMARY', 'summary', 'product/block/summary');
			$this->addBlock('PRODUCT-MAININFO', 'mainInfo', 'product/block/mainInfo');
			$this->addBlock('PRODUCT-OVERVIEW', 'overview', 'product/block/overview');
			$this->addBlock('PRODUCT-RATING-SUMMARY', 'ratingSummary', 'product/ratingSummary');

		$this->addBlock('PRODUCT-PURCHASE-VARIATIONS', 'purchaseVariations', 'product/block/purchaseVariations');


	}

	public function index()
	{
		$product = Product::getInstanceByID($this->request->get('id'), Product::LOAD_DATA, array('ProductImage', 'Manufacturer', 'Category'));
		$this->product = $product;

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
		$response->set('quantity', $this->getQuantities($product));
		$response->set('currency', $this->request->get('currency', $this->application->getDefaultCurrencyCode()));
		$response->set('catRoute', $catRoute);

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
		$response->set('cartForm', $this->buildAddToCartForm($this->getOptions(), $this->getVariations()));

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

		$response->set('variations', $this->getVariations());

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
			$response->set('bundleSavingPercent', $total ? round(($saving / $total) * 100) : 0);
		}

		// contact form
		if ($this->config->get('PRODUCT_INQUIRY_FORM'))
		{
			$response->set('contactForm', $this->buildContactForm());
		}

		// display theme
		if ($theme = CategoryPresentation::getThemeByProduct($product))
		{
			if ($theme->getTheme())
			{
				$this->application->setTheme($theme->getTheme());
			}

			$response->set('presentation', $theme->toFlatArray());
		}

		// product images
		$images = $product->getImageArray();
		if ($theme && $theme->isVariationImages->get())
		{
			if ($variations = $this->getVariations())
			{
				foreach ($variations['products'] as $prod)
				{
					if (!empty($prod['DefaultImage']))
					{
						$images[] = $prod['DefaultImage'];
					}
				}
			}
		}
		$response->set('images', $images);

		// discounted pricing
		$response->set('quantityPricing', $product->getPricingHandler()->getDiscountPrices($this->user, $this->getRequestCurrency()));
		$response->set('files', $this->getPublicFiles());

		// additional categories
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('Category', 'lft'));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('Category', 'isEnabled') , true));

		$pathC = new OrChainCondition();
		$pathF = new ARSelectFilter($pathC);
		$categories = array();
		foreach ($product->getRelatedRecordSetArray('ProductCategory', $f, array('Category')) as $cat)
		{
			$categories[] = array($cat['Category']);

			$cond = new OperatorCond(new ARFieldHandle('Category', 'lft'), $cat['Category']['lft'], "<");
			$cond->addAND(new OperatorCond(new ARFieldHandle('Category', 'rgt'), $cat['Category']['rgt'], ">"));
			$pathC->addAnd($cond);
		}

		if ($categories)
		{
			$pathF->setOrder(new ARFieldHandle('Category', 'lft') , 'DESC');
			$pathF->mergeCondition(new EqualsCond(new ARFieldHandle('Category', 'isEnabled'), true));
			foreach (ActiveRecordModel::getRecordSetArray('Category', $pathF, array('Category')) as $parent)
			{
				foreach ($categories as &$cat)
				{
					if (($cat[0]['lft'] > $parent['lft']) && ($cat[0]['rgt'] < $parent['rgt']) && ($parent['ID'] > Category::ROOT_ID))
					{
						$cat[] = $parent;
					}
				}
			}

			foreach ($categories as &$cat)
			{
				$cat = array_reverse($cat);
			}

			$response->set('additionalCategories', $categories);
		}

		return $response;
	}

	public function priceBlock()
	{
		return new BlockResponse();
	}

	public function addToCartBlock()
	{
		return new BlockResponse();
	}

	public function optionsBlock()
	{
		$response = new BlockResponse();
		$response->set('allOptions', $this->getOptions(true));
		$response->set('options', $this->getOptions());
		return $response;
	}

	public function variationsBlock()
	{
		return new BlockResponse('variations', $this->getVariations());
	}

	public function overviewBlock()
	{
		return new BlockResponse();
	}

	public function actionsBlock()
	{
		return new BlockResponse();
	}

	public function purchaseBlock()
	{
		return new BlockResponse();
	}

	public function imagesBlock()
	{
		return new BlockResponse();
	}

	public function summaryBlock()
	{
		return new BlockResponse();
	}

	public function mainInfoBlock()
	{
		return new BlockResponse();
	}

	public function ratingSummaryBlock()
	{
		return new BlockResponse();
	}

	public function attributeSummaryBlock()
	{
		return new BlockResponse();
	}

	public function purchaseVariationsBlock()
	{
		$variations = $this->getVariations();

		if (!$variations)
		{
			return null;
		}

		$prefixes = $ids = array();
		foreach ($variations['products'] as $product)
		{
			$prefixes[] = 'product_' . $product['ID'] . '_';
			$ids[] = $product['ID'];
		}

		// load product instances
		Product::getRecordSet(select(in('Product.ID', $ids)));
		foreach ($variations['products'] as $product)
		{
			$quant = $this->getQuantities(Product::getInstanceByID($product['ID']));
			$quant = array('' => 0) + $quant;
			$quantities[$product['ID']] = $quant;
		}

		// check if there price is the same for all variations
		$curr = $this->getRequestCurrency();
		$samePrice = true;
		foreach ($variations['products'] as $product)
		{
			if (!empty($product['price_' . $curr]) && (0 != $product['price_' . $curr]))
			{
				$samePrice = false;
				break;
			}
		}

		$response = new BlockResponse('variations', $variations);
		$response->set('cartForm', $this->buildAddToCartForm($this->getOptions(), array(), $prefixes));
		$response->set('quantities', $quantities);
		$response->set('samePrice', $samePrice);
		return $response;
	}

	private function getOptions($all = false)
	{
		if (!isset($this->allOptions))
		{
			$this->allOptions = $this->product->getOptionsArray();

			$this->options = $this->allOptions;
			foreach ($this->options as $key => $option)
			{
				if (!$option['isDisplayed'])
				{
					unset($this->options[$key]);
				}
			}
		}

		return $all ? $this->allOptions : $this->options;
	}

	public function getVariations()
	{
		// variations
		if (!isset($this->variations))
		{
			$this->variations = $this->product->getVariationData($this->application);
		}

		return $this->variations;
	}

	public function publicFilesBlock()
	{

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

			$response->setCookie('rating_' . $product->getID(), true, strtotime('+' . $this->config->get('RATING_SAME_IP_TIME') . ' hours'), $this->router->getBaseDirFromUrl());
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

	public function buildAddToCartValidator($options, $variations, $prefix = '')
	{
		$validator = $this->getValidator("addToCart", $this->getRequest());

		$prefixes = (array)$prefix;

		// option validation
		foreach ($prefixes as $prefix)
		{
			foreach ($options as $option)
			{
				if ($option['isRequired'])
				{
					$optField = $prefix . 'option_' . $option['ID'];
					OrderController::addOptionValidation($validator, $option, $optField);
				}
			}

			if (isset($variations['variations']))
			{
				foreach ($variations['variations'] as $variation)
				{
					$validator->addCheck($prefix . 'variation_' . $variation['ID'], new IsNotEmptyCheck($this->translate('_err_option_0')));
				}
			}

			$validator->addCheck($prefix . 'count', new IsNumericCheck(''));
			$validator->addFilter($prefix . 'count', new NumericFilter());
		}

		return $validator;
	}

	/**
	 * Allowed shopping cart quantities
	 */
	private function getQuantities(Product $product)
	{
		$maxOrderable = $product->getMaxOrderableCount();
		$maxQuant = $product->getMinimumQuantity() + (19 * $product->getQuantityStep());
		$maxOrderable = is_null($maxOrderable) ? $maxQuant : min($maxQuant, $maxOrderable);

		$fractionalStep = $this->product->getParentValue('fractionalStep');
		$quantities = range($product->getMinimumQuantity(), $maxOrderable, max($fractionalStep, 1));

		return array_combine($quantities, $quantities);
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
	private function buildAddToCartForm($options, $variations, $prefix = '')
	{
		return new Form($this->buildAddToCartValidator($options, $variations, $prefix));
	}

	private function buildRatingForm($ratingTypes, Product $product)
	{
		return new Form($this->buildRatingValidator($ratingTypes, $product));
	}

	private function buildRatingValidator($ratingTypes, Product $product, $isRating = false)
	{
		$validator = $this->getValidator("productRating", $this->getRequest());

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

	private function getPublicFiles()
	{
		$f = select(eq('ProductFile.isPublic', true));
		$f->setOrder(f('ProductFileGroup.position'));
		$f->setOrder(f('ProductFile.position'));

		return ActiveRecordModel::getRecordSetArray('ProductFile', $f, array('ProductFileGroup'));
	}

	private function buildContactForm()
	{
		return new Form($this->buildContactValidator());
	}

	public function buildContactValidator(Request $request = null)
	{
		$this->loadLanguageFile('ContactForm');

		$request = $request ? $request : $this->request;

		$validator = $this->getValidator("productContactForm", $request);
		$validator->addCheck('name', new IsNotEmptyCheck($this->translate('_err_name')));
		$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_email')));
		$validator->addCheck('msg', new IsNotEmptyCheck($this->translate('_err_message')));
		$validator->addCheck('surname', new MaxLengthCheck('Please do not enter anything here', 0));

		return $validator;
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function getProduct()
	{
		return $this->product;
	}
}

?>