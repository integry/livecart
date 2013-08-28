<?php


/**
 *
 * @author Integry Systems
 * @package application.controller
 */
class ProductController extends CatalogController
{
	public $filters = array();

	public function initAction()
	{
		parent::init();

		$this->addBlock('PRODUCT-ATTRIBUTE-SUMMARY', 'attributeSummary', 'product/block/attributeSummary');
		$this->addBlock('PRODUCT-PURCHASE', 'purchase', 'product/block/purchase');
			$this->addBlock('PRODUCT-PRICE', 'price', 'product/block/price');
			$this->addBlock('PRODUCT-RECURRING', 'recurring', 'product/block/recurring');
			$this->addBlock('PRODUCT-UP-SELL', 'upSell', 'product/block/upsell');
			$this->addBlock('PRODUCT-OPTIONS', 'options', 'product/block/options');
			$this->addBlock('PRODUCT-VARIATIONS', 'variations', 'product/block/variations');
			$this->addBlock('PRODUCT-TO-CART', 'addToCart', 'product/block/toCart');
			
		$this->addBlock('PRODUCT-IMAGES', 'images', 'product/block/images');
		$this->addBlock('PRODUCT-NAVIGATION', 'navigation', 'product/block/navigation');

		$this->addBlock('PRODUCT-SUMMARY', 'summary', 'product/block/summary');
			$this->addBlock('PRODUCT-MAININFO', 'mainInfo', 'product/block/mainInfo');
			$this->addBlock('PRODUCT-OVERVIEW', 'overview', 'product/block/overview');
			$this->addBlock('PRODUCT-RATING-SUMMARY', 'ratingSummary', 'product/ratingSummary');

		$this->addBlock('PRODUCT-PURCHASE-VARIATIONS', 'purchaseVariations', 'product/block/purchaseVariations');
	}

	public function indexAction()
	{
		$this->loadLanguageFile('Category');

		$product = Product::getInstanceByID($this->request->gget('id'), Product::LOAD_DATA, array('ProductImage', 'Manufacturer', 'Category'));
		$this->product = $product;

		if (!$product->isEnabled->get() || $product->parent->get())
		{
			throw new ARNotFoundException('Product', $product->getID());
		}

		$product->loadPricing();
		$productArray = $product->toArray();

		if ($this->request->gget('category'))
		{
			$this->category = Category::getInstanceByID($this->request->gget('category'), true);
		}
		else
		{
			$this->category = $product->getCategory();
		}

		$this->categoryID = $this->category->getID();

		$this->getAppliedFilters();

		$this->setupBreadcrumb($productArray);

		$this->redirect301($this->request->gget('producthandle'), createHandleString($productArray['name_lang']));
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
		$response->set('currency', $this->request->gget('currency', $this->application->getDefaultCurrencyCode()));
		$response->set('catRoute', $catRoute);
		$response->set('context', $this->getContext());

		// ratings
		if ($this->config->get('ENABLE_RATINGS'))
		{
			if ($product->ratingCount->get() > 0)
			{
				// rating summaries
								$response->set('rating', ProductRatingSummary::getProductRatingsArray($product));
			}

						$ratingTypes = ProductRatingType::getProductRatingTypeArray($product);
			$response->set('ratingTypes', $ratingTypes);
			$response->set('ratingForm', $this->buildRatingForm($ratingTypes, $product));
			$response->set('isRated', $this->isRated($product));
			$response->set('isLoginRequiredToRate', $this->isLoginRequiredToRate());
			$response->set('isPurchaseRequiredToRate', $this->isPurchaseRequiredToRate($product));

			$response->set('sharingForm', $this->buildSharingForm($product));
		}

		$response->set('sharingForm', $this->buildSharingForm($product));

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

		unset($prod);

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
			$bundleData = ProductBundle::getBundledProductArray($product);
			$bundledProducts = array();
			foreach ($bundleData as &$bundled)
			{
				$bundledProducts[] =& $bundled['RelatedProduct'];
			}

			ProductPrice::loadPricesForRecordSetArray($bundledProducts);
			$response->set('bundleData', $bundleData);

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
		if ($theme = CategoryPresentation::getThemeByProduct($product, $this->category))
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
					if (!empty($prod['DefaultImage']['ID']))
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
		$response->set('enlargeProductThumbnailOnMouseOver',
			$this->config->get('_ENLARGE_PRODUCT_THUMBNAILS_ON') == 'P_THUMB_ENLARGE_MOUSEOVER');

		return $response;
	}

	public function quickShopAction()
	{
		$response = $this->index();
		if (!($response instanceof ActionResponse))
		{
			return $response;
		}

		$bResponse = new BlockResponse();
		foreach ($response->getData() as $key => $value)
		{
			$bResponse->set($key, $value);
		}

		$bResponse->set('context', $this->getContext());

		return $bResponse;
	}

	public function priceBlockAction()
	{
		return new BlockResponse();
	}

	public function recurringBlockAction()
	{
		$response = new BlockResponse();
		if ($this->product->type->get() == Product::TYPE_RECURRING)
		{
									$response->set('isRecurring', true);
			$response->set('periodTypesPlural', RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_PLURAL));
			$response->set('periodTypesSingle', RecurringProductPeriod::getAllPeriodTypes(RecurringProductPeriod::PERIOD_TYPE_NAME_SINGLE));
			$response->set('recurringProductPeriods', RecurringProductPeriod::getRecordSetByProduct($this->product)->toArray());
		}
		return $response;
	}

	public function addToCartBlockAction()
	{
		return new BlockResponse();
	}

	public function optionsBlockAction()
	{
		$response = new BlockResponse();
		$response->set('allOptions', $this->getOptions(true));
		$response->set('options', $this->getOptions());
		return $response;
	}

	public function variationsBlockAction()
	{
		return new BlockResponse('variations', $this->getVariations());
	}

	public function upSellBlockAction()
	{
		// upsell products
		$upsell = $this->getRelatedProducts($this->product, /*type:*/ 1);
		foreach ($upsell as $key => $group)
		{
			foreach ($upsell[$key] as $i => &$prod)
			{
				$spec[] =& $upsell[$key][$i];
			}
		}
		if (count($upsell))
		{
			ProductSpecification::loadSpecificationForRecordSetArray($spec);
		}
		$response = new BlockResponse();
		$response->set('upsell', $upsell);
		return $response;
	}

	public function overviewBlockAction()
	{
		return new BlockResponse();
	}

	public function purchaseBlockAction()
	{
		return new BlockResponse();
	}

	public function imagesBlockAction()
	{
		return new BlockResponse();
	}

	public function summaryBlockAction()
	{
		return new BlockResponse();
	}

	public function mainInfoBlockAction()
	{
		return new BlockResponse();
	}

	public function ratingSummaryBlockAction()
	{
		return new BlockResponse();
	}

	public function attributeSummaryBlockAction()
	{
		return new BlockResponse();
	}

	public function navigationBlockAction()
	{
		return new BlockResponse();
	}

	public function purchaseVariationsBlockAction()
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

	public function getVariationsAction()
	{
		// variations
		if (!isset($this->variations))
		{
			$this->variations = $this->product->getVariationData($this->application);
		}

		return $this->variations;
	}

	public function publicFilesBlockAction()
	{

	}

	public function sendToFriendAction()
	{
		$request = $this->getRequest();
		$product = Product::getInstanceByID($request->gget('id'), Product::LOAD_DATA);
		$friendemail = $request->gget('friendemail');
		$validator = $this->buildSharingValidator($product);

		if ($validator->isValid())
		{
			$productArray = $product->toArray();
			$email = new Email($this->application);
			$email->setTo($request->gget('friendemail'));
			$email->setTemplate('notify.sendProductToFriend');
			$email->set('product', $productArray);
			$user = SessionUser::getUser();
			$email->set('user', $user->toArray());

			if ($user->isAnonymous())
			{
				$friendName = $request->gget('nickname');
			}
			else
			{
				$user->load();
				$friendName = $user->firstName->get().' '.$user->lastName->get();
			}
			$email->set('friendName', trim($friendName));
			$email->set('notes', $request->gget('notes'));
			$email->send();

			$response = new JSONResponse(
				array('message'=>$this->makeText('_info_about_product_message_sent_to', array($productArray['name_lang'], $friendemail))),
				'success'
			);
		}
		else
		{
			$response = new JSONResponse(array('message'=>$this->translate('_error_cannot_send_to_friend')), 'failure');
		}

		return $response;
	}

	public function rateAction()
	{
		$product = Product::getInstanceByID($this->request->gget('id'), Product::LOAD_DATA);
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
				$rating->rating->set($this->request->gget('rating_'  . $type->getID()));
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

	public function reviewsAction()
	{
		if (!$this->config->get('ENABLE_REVIEWS'))
		{
			throw new HTTPStatusException(404);
		}

		$response = $this->index();

		$page = $this->request->gget('page', 1);
		$perPage = $this->config->get('REVIEWS_PER_PAGE');
		$offsetStart = ($this->request->gget('page', 1) - 1) * $perPage;

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

	public function sendContactFormAction()
	{
		$product = Product::getInstanceByID($this->request->gget('id'), Product::LOAD_DATA);
		$redirect = new ActionRedirectResponse('product', 'index', array('id' => $product->getID()));

		$validator = $this->buildContactValidator();
		if ($validator->isValid())
		{
			$email = new Email($this->application);
			$email->setTemplate('contactForm/productInquiry');
			$email->setFrom($this->request->gget('email'), $this->request->gget('name'));
			$email->setTo($this->config->get('NOTIFICATION_EMAIL'), $this->config->get('STORE_NAME'));
			$email->set('message', $this->request->gget('msg'));
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

	public function nextAction()
	{
		return $this->previous(1);
	}

	public function previousAction($diff = -1)
	{
		$product = Product::getInstanceByID($this->request->gget('id'), true, array('Category'));
		$this->category = (!$this->request->gget('category') ? $product->category->get() : Category::getInstanceByID($this->request->gget('category'), true));

		$this->getAppliedFilters();
		$this->getSelectFilter();

		if ($this->request->gget('quickShopSequence'))
		{
			$ids = json_decode($this->request->gget('quickShopSequence'));
		}
		else
		{
			$ids = array();
			foreach (ActiveRecordModel::getFieldValues('Product', $this->productFilter->getSelectFilter(), array('ID'), array('Category')) as $row)
			{
				$ids[] = $row['ID'];
			}
		}

		$index = array_search($product->getID(), $ids);

		$prevIndex = $index + $diff;
		if ($prevIndex < 0)
		{
			$prevIndex = count($ids) - 1;
		}
		else if ($prevIndex == count($ids))
		{
			$prevIndex = 0;
		}

		include_once(ClassLoader::getRealPath('application.helper.smarty') . '/function.productUrl.php');

		if ('quickShop' == $this->request->gget('originalAction'))
		{
			return new ActionRedirectResponse('product', 'quickShop', array('id' => $ids[$prevIndex], 'query' => $this->getContext()));
		}
		else
		{
			return new RedirectResponse(createProductUrl(array('product' => Product::getInstanceByID($ids[$prevIndex], true)->toArray(), 'query' => $this->getContext()), $this->application));
		}
	}

	public function buildAddToCartValidatorAction($options, $variations, $prefix = '')
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

	private function buildSharingForm(Product $product)
	{
		return new Form($this->buildSharingValidator($product));
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

	private function buildSharingValidator(Product $product)
	{
				$validator = $this->getValidator('productSharingValidator', $this->getRequest());
		if (!$this->config->get('ENABLE_PRODUCT_SHARING'))
		{
			$validator->addCheck(md5(time().mt_rand()), new IsNotEmptyCheck($this->translate('_feature_disabled')));
		}

		$validator->addCheck('friendemail', new IsNotEmptyCheck($this->translate('_err_enter_email')));
		$validator->addCheck('friendemail', new IsValidEmailCheck($this->translate('_err_invalid_email')));

		if (SessionUser::getUser()->isAnonymous())
		{
			if (!$this->config->get('ENABLE_ANONYMOUS_PRODUCT_SHARING'))
			{
				$validator->addCheck(md5(time().mt_rand()), new IsNotEmptyCheck($this->translate('_feature_disabled_for_anonymous')));
			}
			$validator->addCheck('nickname', new IsNotEmptyCheck($this->translate('_err_enter_nickname')));
		}

		return $validator;
	}

	private function setupBreadcrumb($productArray)
	{
		include_once(ClassLoader::getRealPath('application.helper.smarty') . '/function.productUrl.php');

		$nodeArray = $this->addCategoriesToBreadCrumb($this->product->category->get()->getPathNodeArray());
		$this->addFiltersToBreadCrumb($nodeArray);

		$this->addBreadCrumb($productArray['name_lang'], createProductUrl(array('product' => $productArray), $this->application));
	}

	private function isAddingReview()
	{
		return $this->config->get('ENABLE_REVIEWS') && ($this->config->get('REVIEWS_WITH_RATINGS') || ($this->request->gget('nickname') || $this->request->gget('title') || $this->request->gget('text')));
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
								$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $this->user->getID()));
				$f->mergeCondition(new EqualsCond(new ARFieldHandle('OrderedItem', 'productID'), $product->getID()));
				$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
				$f->setLimit(1);

				$this->isPurchaseRequiredToRate = ActiveRecordModel::getRecordCount('OrderedItem', $f, array('CustomerOrder')) < 1;
			}

			return $this->isPurchaseRequiredToRate;
		}
	}

	private function getRelatedProducts(Product $product, $type=0)
	{
		// get related products
		$related = $product->getRelatedProductsWithGroupsArray($type);

		$rel = array();
		foreach ($related as $r)
		{
			if (!isset($r['RelatedProduct']))
			{
				continue;
			}

			$p = $r['RelatedProduct'];
			if (!$p['isEnabled'])
			{
				continue;
			}

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

		return $this->product->getRelatedRecordSetArray('ProductFile', $f, array('ProductFileGroup'));
	}

	private function buildContactForm()
	{
		return new Form($this->buildContactValidator());
	}

	public function buildContactValidatorAction(Request $request = null)
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

	public function getProductAction()
	{
		return $this->product;
	}
}

?>
