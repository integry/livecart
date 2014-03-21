<?php

use category\Category;

/**
 * Base class for all front-end related controllers
 *
 * @author Integry Systems
 * @package application/controller
 */
class FrontendController extends ControllerBase
{
	protected $breadCrumb = array();

	/**
	 *	Session order instance
	 */
	protected $order;

	public function ___constructAction(LiveCart $application)
	{
		parent::__construct($application);

		$this->request->sanitizeAllData();

		unset($this->order);

		// variables to append automatically to all URLs
		foreach (array('currency', 'sort', 'layout') as $key)
		{
			if ($this->request->has($key))
			{
				$this->router->addAutoAppendQueryVariable($key, $this->request->get($key));
			}
		}

		// disallow creating new EAV select field values from frontend
		if (is_array($this->request->get('other')))
		{
			$this->request->remove('other');
		}

		if ($this->application->isCustomizationMode())
		{
			$theme = $this->session->get('customizationTheme');
			if ($theme)
			{
				$this->application->setTheme($theme);
			}
			$this->loadLanguageFile('Customize');
		}
	}

	public function initialize()
	{
		$initRes = parent::initialize();

/*
		$this->addBlock('FOOTER_TOOLBAR', 'toolbar', 'block/toolbar');
		$this->addBlock('CATEGORY_BOX', 'boxCategory', 'block/box/category');
		$this->addBlock('ROOT_CATEGORIES', 'boxRootCategory', 'block/box/rootCategory');
		$this->addBlock('DYNAMIC_CATEGORIES', 'dynamicCategoryMenu', 'block/box/dynamicCategory');
		$this->addBlock('SALE_ITEMS', 'saleItems', 'block/box/saleItems');
		$this->addBlock('NEWEST_PRODUCTS', 'newestProducts', 'block/box/newestProducts');
		$this->addBlock('BESTSELLING_PRODUCTS', 'bestsellingProducts', 'block/box/bestsellingProducts');
		$this->addBlock('BREADCRUMB', 'boxBreadCrumb', 'block/box/breadcrumb');
		$this->addBlock('BREADCRUMB_TITLE', 'boxBreadCrumbTitle', 'block/box/breadcrumbTitle');
		$this->addBlock('LANGUAGE', 'boxLanguageSelect', 'block/box/language');
		$this->addBlock('CURRENCY', 'boxSwitchCurrency', 'block/box/currency');
		$this->addBlock('CURRENCY_MENU', 'boxSwitchCurrencyMenu', 'block/box/currencyMenu');
		$this->addBlock('CART', 'boxShoppingCart', 'block/box/shoppingCart');
		$this->addBlock('SEARCH', 'boxSearch', 'block/box/search');
		$this->addBlock('INFORMATION', 'boxInformationMenu', 'block/box/informationMenu');
		$this->addBlock('NEWSLETTER', 'boxNewsletterSubscribe', 'block/box/newsletterSubscribe');
		$this->addBlock('TRACKING', 'tracking', 'block/tracking');
		$this->addBlock('NEWS', 'latestNews', 'block/box/latestNews');
		$this->addBlock('QUICKNAV', 'blockQuickNav', 'block/box/quickNav');
		$this->addBlock('COMPARE', array('compare', 'compareMenu'));
		$this->addBlock('MINI_CART', array('order', 'miniCart'), 'order/miniCartBlock');
		$this->addBlock('QUICK_LOGIN', 'quickLogin', 'user/block/quickLoginBlock');
		$this->addBlock('INVOICES_MENU', array('user', 'invoicesMenu'), 'user/block/invoicesMenu');
*/
		$this->application->logStat('Init FrontendController');

		return $initRes;
	}

	public function getRequestCurrencyAction()
	{
		$currencyCode = $this->request->get('currency', $this->application->getDefaultCurrencyCode());

		// currency variable is sometimes POST'ed from external services, like payment gateway notifications
		if (!empty($_POST['currency']) && !empty($_GET['currency']))
		{
			$currencyCode = $_GET['currency'];
		}

		$currency = Currency::getValidInstanceById($currencyCode);
		if ($currency)
		{
			return $currency->getID();
		}
	}

	public function setOrderAction(CustomerOrder $order)
	{
		$this->order = $order;
	}

	public function addBreadCrumb($title, $url)
	{
		$this->breadCrumb[] = array('title' => $title, 'url' => $url);
	}

	protected function getCountryList()
	{
		$primaryCountries = str_replace(' ', '', strtoupper($this->config->get('PRIMARY_COUNTRIES')));

		if ($primaryCountries)
		{
			$defCountries = explode(',', $primaryCountries);
		}
		else
		{
			$defCountries = array($this->config->get('DEF_COUNTRY'));
		}

		$countries = $this->application->getEnabledCountries();
		asort($countries);

		// set default countries first
		$defCountries = array_reverse($defCountries);
		foreach ($defCountries as $country)
		{
			if (isset($countries[$country]))
			{
				$name = $countries[$country];
				unset($countries[$country]);
				$countries = array_merge(array($country => $name), $countries);
			}
		}

		return $countries;
	}

	protected function getStateList($country)
	{
		$states = State::getStatesByCountry($country);

		if ($states)
		{
			$states = array('' => '') + $states;
		}

		return $states;
	}

	protected function saveAddress(UserAddress $address, $prefix = '')
	{
		$address->loadRequestData($this->request, $prefix);
		$address->countryID->set($this->request->get($prefix . 'country'));
		$address->stateName->set($this->request->get($prefix . 'state_text'));
		if ($this->request->get($prefix . 'state_select'))
		{
			$address->state->set(State::getStateByIDAndCountry($this->request->get($prefix . 'state_select'), $this->request->get($prefix . 'country')));
		}
		else
		{
			$address->state->set(null);
		}
		$address->save();
	}

	public function quickLoginBlockAction()
	{
		if (!$this->user->isAnonymous())
		{
			return;
		}

		$this->loadLanguageFile('User');
		return new BlockResponse('return', $_SERVER['REQUEST_URI']);
	}

	protected function boxInformationMenuBlock()
	{
				$f = new ARSelectFilter(StaticPage::getIsInformationMenuCondition());
		$f->orderBy('StaticPage.position');
		$response = new BlockResponse();
		$this->set('pages', StaticPage::createTree(ActiveRecordModel::getRecordSetArray('StaticPage', $f)));
		unset($f);
	}

	protected function boxNewsletterSubscribeBlock()
	{
		if (!$this->user->isAnonymous())
		{
			//return false;
		}

				return new BlockResponse('form', new Form(NewsletterController::getSubscribeValidator()));
	}

	protected function boxShoppingCartBlock()
	{
				$response = new BlockResponse();

		$orderData = $this->session->get('orderData');
		if (!$orderData && $this->session->get('CustomerOrder'))
		{
			$orderData = SessionOrder::getOrderData();
		}

		$this->set('order', $orderData);
		$this->set('currency', $this->request->get('currency', $this->application->getDefaultCurrencyCode()));

	}

	protected function boxSwitchCurrencyBlock()
	{
		$returnRoute = $this->router->getRequestedRoute();
		$returnRoute = $this->router->createUrlFromRoute($returnRoute, true);
		$returnRoute = $this->router->addQueryParams($returnRoute);
		$returnRoute = $this->router->setUrlQueryParam($returnRoute, 'currency', '_curr_');

		$current = $this->getRequestCurrency();
		$currencies = $this->application->getCurrencySet();
		$currencyArray = array();
		foreach ($currencies as $currency)
		{
			$currencyArray[$currency->getID()] = $currency->toArray();
			$currencyArray[$currency->getID()]['url'] = str_replace('_curr_', $currency->getID(), $returnRoute);

			if ($currency->getID() == $current)
			{
				$currentCurrency = $currency->toArray();
			}
		}

		$response = new BlockResponse();
		$this->set('allCurrencies', $currencyArray);
		$this->set('currencies', array_diff_key($currencyArray, array($current => '')));
		$this->set('current', $current);
		$this->set('currentCurrency', $currentCurrency);
	}

	protected function boxSwitchCurrencyMenuBlock()
	{
		return $this->boxSwitchCurrencyBlock();
	}

	protected function boxLanguageSelectBlock()
	{
		$languages = $this->application->getLanguageSetArray(true, false);
		$current = $this->application->getLocaleCode();

		$returnRoute = $this->router->getRequestedRoute();

		if ('/' == substr($returnRoute, 2, 1))
		{
		  	$returnRoute = substr($returnRoute, 3);
		}

		$response = new BlockResponse();

		foreach ($languages as $key => $lang)
		{
			if ($lang['ID'] == $current)
			{
				$defKey = $key;
				$this->set('current', $lang);
			}
			else
			{
				// changing language from the index page
				if (strlen($returnRoute) == 2)
				{
					$returnRoute = '';
				}

				$langRoute = ($lang['isDefault'] ? '' : $lang['ID'] . '/') . $returnRoute;
				$languages[$key]['url'] = $this->router->addQueryParams($this->router->createUrlFromRoute($langRoute, true));
			}
		}

		$this->set('allLanguages', $languages);

		unset($languages[$defKey]);
		$this->set('languages', $languages);

	}

	protected function boxBreadCrumbBlock()
	{
		$home = array('controller' => 'index', 'action' => 'index');
		if ($this->locale->getLocaleCode() != $this->application->getDefaultLanguageCode())
		{
			$home['requestLanguage'] = $this->locale->getLocaleCode();
		}

		$title = $this->config->get('BREADCRUMB_TITLE') ? $this->config->get('BREADCRUMB_TITLE') : $this->config->get('STORE_NAME');
		$root = array('title' => $title,
					  'url' => $this->router->createUrl($home, true));

		if (reset($this->breadCrumb) != $root)
		{
			array_unshift($this->breadCrumb, $root);
		}

		$response = new BlockResponse();
		$this->set('breadCrumb', $this->breadCrumb);
	}

	protected function boxBreadCrumbTitleBlock()
	{
		$breadCrumbCopy = $this->breadCrumb;
		$last = array_pop($breadCrumbCopy);
		return new BlockResponse('breadCrumb', $last);
	}

	protected function boxSearchBlock()
	{

		$category = $this->getCategory();
		$search = $this->getCategory()->getPathNodeArray();

		$subCategories = $category->getSubCategoryArray();
		if ($subCategories)
		{
			if ($category->getID() != Category::ROOT_ID)
			{
				$search[] = $category->toArray();
			}

			$search = array_merge($search, $subCategories);
		}

		if (!$search)
		{
			$category = Category::getInstanceById(Category::ROOT_ID, Category::LOAD_DATA);
			$subCategories = $category->getSubCategorySet();

			foreach ($subCategories as $category)
			{
				$search[] = $category->toArray();
			}
		}

		$options = array(1 => $this->translate('_all_products'));

		foreach ($search as $cat)
		{
			if ($cat['isEnabled'])
			{
				$options[$cat['ID']] = $cat['name_lang'];
			}
		}

		$form = new Form($this->getValidator("productSearch", $this->request));
		$form->enableClientSideValidation(false);
		$form->set('id', $this->getCategory()->getID());
		$form->set('q', $this->request->get('q'));

		if ($this->filters && is_array($this->filters))
		{
			foreach ($this->filters as $filter)
			{
				if ($filter instanceof SearchFilter)
				{
					$form->set('q', $filter->getKeywords());
				}
			}
		}

		$response = new BlockResponse();
		$this->set('categories', $options);
		$this->set('form', $form);
	}

	private function getTopCategories()
	{

		if (!isset($this->topCategories))
		{
			$this->topCategories = Category::getInstanceByID(1)->getSubcategoryArray();
		}

		return $this->topCategories;
	}

	private function getTopCategoryId()
	{
		$this->getCurrentCategoryPath();
		return $this->topCategoryId;
	}

	private function getCurrentCategoryPath()
	{
		if (!isset($this->currentCategoryPath))
		{
			$currentCategory = $this->getCategory();

			$parent = $currentCategory->getParent();
			
			// get path of the current category (except for top categories)
			if (!(1 == $currentCategory->getID()) && ($parent && (1 < $parent->getID())))
			{
				$path = $currentCategory->getPathNodes();

				$topCategoryId = $path[0]->getID();
				unset($path[0]);
			}
			else
			{
				$topCategoryId = $this->getCategory()->getID();
			}

			$this->topCategoryId = $topCategoryId;
			$this->currentCategoryPath = isset($path) ? $path : array();
		}

		return $this->currentCategoryPath;
	}

	public function boxCategoryBlockAction()
	{
		//$this->invalidateCacheOnUpdate('Category');
		//$this->setRequestVar('id');
		//$this->allowCache();

		// get top categories
		$topCategories = persist($this->getTopCategories());
		$path = $this->getCurrentCategoryPath();
		foreach ($topCategories as &$cat)
		{
		  	if ($this->topCategoryId == $cat->getID())
		  	{
				$current = &$cat;
			}
		}

		$currentCategory = $this->getCategory();
		$parent = $currentCategory->getParent();
		$subCategories = array();

		// get sibling (same-level) categories (except for top categories)
		if (!(1 == $currentCategory->getID()) && ($parent && (1 < $parent->getID())))
		{
			$siblings = persist($currentCategory->getSiblings());

			foreach ($path as &$node)
			{
			  	if ($node->getID() != $this->getCategory()->getID())
			  	{
					$subCategories[$current->getID()] = array(0 => &$node);
				  	$current =& $node;
				}
				else
				{
					$subCategories[$current->getID()] =& $siblings;
					foreach ($subCategories[$current->getID()] as &$sib)
					{
					  	if ($sib->getID() == $this->getCategory()->getID())
					  	{
							$current =& $sib;
						}
					}
				}
			}
		}

		// get subcategories of the current category (except for the root category)
		if ($this->getCategory()->getID() > 1)
		{
			$subCategories[$current->getID()] = $currentCategory->getSubcategorySet();
		}
		
		$this->set('subCategories', $subCategories);
		$this->set('categories', $topCategories);
		$this->set('currentId', $this->getCategory()->getID());
		$this->set('lang', 'en');
	}

	protected function dynamicCategoryMenuBlock()
	{
		$f = query::query()->where('Category.isEnabled = :Category.isEnabled:', array('Category.isEnabled' => true));
		$categories = ActiveRecordModel::getRecordSetArray('Category', Category::getRootNode()->getBranchFilter($f));

		$tree = array(1 => array('subCategories' => array()));
		foreach ($categories as $key => &$category)
		{
			$tree[$category['ID']] =& $category;
		}

		foreach ($categories as &$category)
		{
			$tree[$category['parentNodeID']]['subCategories'][] =& $category;
		}

		$tree = $tree[1]['subCategories'];

		$response = new BlockResponse('categories', $tree);

		$path = $this->getCategory()->getPathNodeArray();
		if ($path)
		{
			$this->set('topCategoryId', $path[0]['ID']);
		}

		$this->set('currentId', $this->getCategory()->getID());
		$this->set('currentCategory', $this->getCategory()->toArray());

	}

	protected function boxRootCategoryBlock()
	{

		if ($this->config->get('TOP_MENU_HIDE'))
		{
			return;
		}

		if (!$this->config->get('TOP_MENU_HIDE_CATS'))
		{
			$topCategories = $this->getTopCategories();
			$ids = array();
			foreach ($topCategories as $cat)
			{
				if ($cat['isEnabled'])
				{
					$ids[] = $cat['ID'];
				}
			}
			$f = new ARSelectFilter(new INCond('Category.parentNodeID', $ids));
			$f->orderBy('Category.parentNodeID');
			$f->orderBy('Category.lft');
			$subCategories = array();
			foreach (ActiveRecordModel::getRecordSetArray('Category', $f) as $cat)
			{
				if ($cat['isEnabled'])
				{
					$subCategories[$cat['parentNodeID']][] = $cat;
				}
			}
		}

		$f = new ARSelectFilter(new IsNullCond('StaticPage.parentID'));
		$f->andWhere(StaticPage::getIsRootCategoriesMenuCondition());
		$f->orderBy('StaticPage.position');
		$pages = ActiveRecordModel::getRecordSetArray('StaticPage', $f);
		$ids = array();
		$subPages = array();
		foreach($pages as $page)
		{
			$ids[] = $page['ID'];
		}
		$f = new ARSelectFilter(new INCond('StaticPage.parentID', $ids));
		$f->orderBy('StaticPage.position');
		foreach (ActiveRecordModel::getRecordSetArray('StaticPage', $f) as $page)
		{
			$subPages[$page['parentID']][] = $page;
		}

		if (empty($topCategories) && empty($pages))
		{
			return;
		}

		$response = new BlockResponse();
		$this->set('categories', $topCategories);
		$this->set('subCategories', $subCategories);
		$this->set('pages', $pages);
		$this->set('subPages', $subPages);
		$this->set('currentId', $this->getTopCategoryId());
	}

	protected function saleItemsBlock($useRoot = false)
	{

		$category = $useRoot ? Category::getRootNode() : $this->getCategory();
		$filter = new ProductFilter($category, query::query()->where('Product.isFeatured = :Product.isFeatured:', array('Product.isFeatured' => true)));
		$filter->includeSubcategories();
		$filter->setEnabledOnly();

		$selectFilter = $filter->getSelectFilter();
		$selectFilter->limit($this->config->get('SALE_ITEMS_COUNT'));
		$selectFilter->orderBy(new ARExpressionHandle('RAND()'));

		$products = ActiveRecord::getRecordSetArray('Product', $selectFilter, array('Category', 'DefaultImage' => 'ProductImage'));

		ProductPrice::loadPricesForRecordSetArray($products);

		if ($products)
		{
			return new BlockResponse('products', $products);
		}
		else if (!$category->isRoot())
		{
			return $this->saleItemsBlock(true);
		}
	}

	protected function newestProductsBlock($useRoot = false)
	{

		$category = $useRoot ? Category::getRootNode() : $this->getCategory();
		$filter = new ProductFilter($category, new ARSelectFilter());
		$filter->includeSubcategories();
		$filter->setEnabledOnly();

		$selectFilter = $filter->getSelectFilter();
		$selectFilter->limit($this->config->get('NEWEST_ITEMS_COUNT'));
		$selectFilter->orderBy('Product.dateCreated', 'DESC');

		$products = ActiveRecord::getRecordSetArray('Product', $selectFilter, array('Category', 'DefaultImage' => 'ProductImage'));

		ProductPrice::loadPricesForRecordSetArray($products);

		if ($products)
		{
			return new BlockResponse('products', $products);
		}
		else if (!$category->isRoot())
		{
			return $this->newestProductsBlock(true);
		}
	}

	public function bestsellingProductsBlockAction()
	{

		$cache = $this->application->getCache();
		$key = array('bestsellers', $this->getCategory()->getID() . '_'/* . $days*/);

		if (!$cache->get($key))
		{
			$category = $this->getCategory();
			$filter = new ProductFilter($category, new ARSelectFilter());
			$filter->includeSubcategories();
			$filter->setEnabledOnly();

			$selectFilter = $filter->getSelectFilter();
			$selectFilter->limit($this->config->get('BESTSELLING_ITEMS_COUNT'));
			$selectFilter->orderBy(new ARExpressionHandle('cnt'), 'DESC');

			$q = new ARSelectQueryBuilder();
			$q->includeTable('Product');
			$q->joinTable('Category', 'Product', 'ID', 'categoryID');
			$q->addField('Product.ID');
			$q->addField(new ARExpressionHandle('(SELECT SUM(count) FROM OrderedItem LEFT JOIN CustomerOrder ON OrderedItem.customerOrderID=CustomerOrder.ID WHERE productID=Product.ID AND CustomerOrder.isPaid=1 AND CustomerOrder.dateCompleted > "' . ARSerializableDateTime::createFromTimeStamp(strtotime('-' . $this->config->get('BESTSELLING_ITEMS_DAYS') . ' days')) . '")'), null, 'cnt');
			$q->setFilter($selectFilter);

			$cache->set($key, ActiveRecord::getDataByQuery($q));
		}

		$products = $cache->get($key);

		if (!$products)
		{
			return;
		}

		$ids = array();
		foreach ($products as $id)
		{
			$ids[] = $id['ID'];
		}

		$products = ActiveRecord::getRecordSetArray('Product', select(IN('Product.ID', $ids)) , array('DefaultImage' => 'ProductImage'));
		ProductPrice::loadPricesForRecordSetArray($products);

		if ($products)
		{
			return new BlockResponse('products', $products);
		}
	}

	protected function trackingBlock()
	{
		$code = array();

		if (!$this->config->get('TRACKING_SERVICES'))
		{
			return false;
		}

		foreach ($this->config->get('TRACKING_SERVICES') as $class => $enabled)
		{

			$data = array();

			foreach ($this->config->getSection('tracking/' . $class) as $key => $value)
			{
				$value = $this->config->get($key);
				$key = substr($key, strlen($class) + 1);
				$data[$key] = $value;
			}

			$tracker = new $class($data, $this);
			$code[$class] = $tracker->getHtml();
		}

		return new BlockResponse('code', $code);
	}

	public function latestNewsBlockAction()
	{
		$this->application->logStat('Starting latestNewsBlock');
				$f = query::query()->where('NewsPost.isEnabled = :NewsPost.isEnabled:', array('NewsPost.isEnabled' => true));
		$f->orderBy('NewsPost.position', 'DESC');
		$f->limit($this->config->get('NUM_NEWS_INDEX') + 1);

		$this->application->logStat('Before fetching news from DB');
		$news = array();
		$news = ActiveRecordModel::getRecordSetArray('NewsPost', $f);

		$this->application->logStat('Fetched news from DB');

		$response = new BlockResponse('news', $news);
		$this->set('isNewsArchive', count($news) > $this->config->get('NUM_NEWS_INDEX'));

		$this->application->logStat('Finished latestNewsBlock');
	}

	public function blockQuickNavBlockAction()
	{
		$response = new BlockResponse();

		// manufacturer list
				$controller = new ManufacturersController($this->application);
		$man = $controller->index();
		$this->set('manufacturers', $man->get('manufacturers'));
		$this->set('rootCat', $man->get('rootCat'));

		// category tree
		$cat = $this->dynamicCategoryMenuBlock();
		$this->set('categories', $cat->get('categories'));

	}

	/**
	 *  Recursively applies all selected filters to applicable categories
	 */
	private function applyFilters(&$categories, $parentFilterIds)
	{
		foreach ($categories as &$category)
		{
		  	$categoryFilters = $parentFilterIds;
			foreach ($this->filters as $filter)
		  	{
				if (!($filter instanceof SpecificationFilterInterface) ||
					$filter->getSpecField()->category->getID() == $category['ID'])
				{
					$categoryFilters[$filter->getID()] = true;
				}

				if (isset($categoryFilters[$filter->getID()]))
				{
					$category['filters'][] = $filter;
				}
			}

			if (isset($category['subCategories']))
			{
			  	$this->applyFilters($category['subCategories'], $categoryFilters);
			}
		}
	}

	protected function ajaxResponse(CompositeJSONResponse $response)
	{
		$this->set('orderSummary', SessionOrder::getOrderData());

		if ($msg = $this->getMessage())
		{
			$this->set('successMessage', $msg);
		}

		if ($error = $this->getErrorMessage())
		{
			$this->set('errorMessage', $error);
		}

	}

	protected function getCategory()
	{
		$curr = $this->request->get('__current_cat');
		if (!empty($curr))
		{
			$this->category = Category::getInstanceById($curr);
		}
		else
		{
			$this->category = Category::getRootNode();
		}
		
		return $this->category;
	}

	/*
	public function __getAction($name)
	{
		if ($inst = parent::__get($name))
		{
			return $inst;
		}

		switch ($name)
	  	{
			case 'order':
								$this->order = SessionOrder::getorderBy();

				// check if order currency matches the request currency
				if (!$this->order->currency || ($this->order->currency->getID() != $this->getRequestCurrency()))
				{
					$this->order->changeCurrency(Currency::getInstanceByID($this->getRequestCurrency()));
				}

				return $this->order;
			break;

			default:
			break;
		}
	}
	*/
}

?>
