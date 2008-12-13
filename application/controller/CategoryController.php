<?php

ClassLoader::import("application.controller.FrontendController");
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.filter.*');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.Manufacturer');
ClassLoader::import('application.model.product.ProductFilter');
ClassLoader::import('application.model.product.ProductCount');

/**
 * Index controller for frontend
 *
 * @author Integry Systems
 * @package application.controller
 */
class CategoryController extends FrontendController
{
  	protected $filters = array();

  	protected $productFilter;

  	protected $category;

	protected $categoryID = 1;

	protected $hasProducts = false;

	public function init()
  	{
	  	parent::init();
	  	$this->addBlock('FILTER_BOX', 'boxFilter', 'block/box/filter');
	  	$this->addBlock('PRODUCT_LISTS', 'productList', 'block/productList');
	}

	public function index()
	{
		ClassLoader::import('application.model.presentation.CategoryPresentation');

		// get category instance
		$this->categoryID = $this->request->get('id');
		$this->category = Category::getInstanceById($this->categoryID, Category::LOAD_DATA);
		$categoryArray = $this->category->toArray();

		$this->getAppliedFilters();

		// pagination
		$currentPage = $this->request->get('page', 1);
		$listLayout = $this->request->get('layout') && $this->config->get('ALLOW_SWITCH_LAYOUT') ?
						('grid' == $this->request->get('layout') ? 'GRID' : 'LIST') :
						$this->config->get('LIST_LAYOUT');

		$perPage = ('GRID' == $listLayout) ?
						$this->config->get('LAYOUT_GRID_COLUMNS') * $this->config->get('LAYOUT_GRID_ROWS') :
						$this->config->get('NUM_PRODUCTS_PER_CAT');

		$offsetStart = (($currentPage - 1) * $perPage) + 1;
		$offsetEnd = $currentPage * $perPage;

		$selectFilter = new ARSelectFilter();
		$selectFilter->setLimit($perPage, $offsetStart - 1);

	  	// create new search filter
		$query = $this->request->get('q');
		if ($query)
	  	{
			$this->filters[] = new SearchFilter($query);

			// search by category names
			$f = new ARSelectFilter();
			foreach (array($this->locale->getLocaleCode(), $this->application->getDefaultLanguageCode()) as $handle)
			{
				$langHandle = MultiLingualObject::getLangSearchHandle(new ARFieldHandle('Category', 'name'), $handle);
				$f->mergeCondition(new LikeCond($langHandle, '%' . $query . '%'));
			}

			$foundCategories = ActiveRecordModel::getRecordSet('Category', $f, Category::LOAD_REFERENCES);
			foreach ($foundCategories as $category)
			{
				$category->getPathNodeSet();
			}
		}

		// sorting
		$sort = array();
		$opts = $this->config->get('ALLOWED_SORT_ORDER');
		if ($opts)
		{
			foreach ($opts as $opt => $status)
			{
				$sort[strtolower($opt)] = $this->translate($opt);
			}
		}

		$order = $this->request->get('sort');
		$defOrder = strtolower($this->config->get('SORT_ORDER'));
		if (!$order || !isset($sort[$order]))
		{
			$order = $defOrder;
		}

		$this->applySortOrder($selectFilter, $order);

		// setup ProductFilter
		$productFilter = new ProductFilter($this->category, $selectFilter);
		$this->productFilter = $productFilter;
		foreach ($this->filters as $filter)
		{
			$productFilter->applyFilter($filter);

			if ($filter instanceof SearchFilter)
			{
				$productFilter->includeSubcategories();
				$searchQuery = $filter->getKeywords();
			}
		}

		if (($this->category->isRoot() && $this->filters) || $this->filters || $this->request->get('includeSub'))
		{
			$productFilter->includeSubcategories();
		}

		$products = $this->getProductsArray($productFilter);
		$this->hasProducts = count($products) > 0;

		// pagination
		$count = new ProductCount($this->productFilter, $this->application);
		$totalCount = $count->getCategoryProductCount($productFilter);
		$offsetEnd = min($totalCount, $offsetEnd);
		$this->totalCount = $totalCount;

		// narrow by subcategories
		$subCategories = $this->category->getSubCategoryArray(Category::LOAD_REFERENCES);

		$categoryNarrow = array();
		if ((!empty($searchQuery) || $this->category->isRoot() || $this->filters) && $products)
		{
			$categoryNarrow = $this->getSubCategoriesBySearchQuery($selectFilter, $subCategories);
		}

		// if all the results come from one category, redirect to this category
		if ((count($categoryNarrow) == 1) && (count($this->filters) == 1))
		{
			$canNarrow = true;

			foreach ($products as $product)
			{
				if ($product['Category']['ID'] == $this->categoryID)
				{
					$canNarrow = false;
				}
			}

			if ($canNarrow)
			{
				while (count($categoryNarrow) == 1)
				{
					$this->category = Category::getInstanceByID($categoryNarrow[0]['ID'], Category::LOAD_DATA);
					$subCategories = $this->category->getSubCategoryArray(Category::LOAD_REFERENCES);
					if ($subCategories)
					{
						$subCategories[] = $categoryArray;
					}
					$categoryNarrow = $this->getSubCategoriesBySearchQuery($selectFilter, $subCategories);
				}

				include_once(ClassLoader::getRealPath('application.helper.smarty') . '/function.categoryUrl.php');
				return new RedirectResponse(createCategoryUrl(array('data' => $this->category->toArray(), 'filters' => $this->filters), $this->application));
			}
		}

		// get subcategory-subcategories
		if ($subCategories)
		{
			$this->getSubSubCategories($subCategories);
		}

		// get subcategory featured products
		$subCatFeatured = array();
		if (($subCategories && !$products) || ($this instanceof IndexController))
		{
			$subCatFeatured = $this->getSubCatFeaturedProducts();
		}

		// if there were no products found, include subcategories in filter counts
		if (!$products)
		{
			$selectFilter->removeCondition(new EqualsCond(new ARFieldHandle('Product', 'categoryID'), $this->category->getID()));
			$this->productFilter->includeSubcategories();
		}

		// load filter data
		$this->getFilterCounts();

		$filters = array();
		if ($showAll = $this->request->get('showAll'))
		{
			if ('brand' == $showAll)
			{
				$filters = array('filters' => $this->manFilters);
			}
			else
			{
				foreach ($this->filterGroups as $filterGroup)
				{
					if ($filterGroup['ID'] == $showAll)
					{
						$filters = $filterGroup;
					}
				}
			}
		}

		// search redirects
		// no products found, but found one category name - redirect to this category
		if (isset($foundCategories) && (1 == $foundCategories->size()) && !$products)
		{
			include_once(ClassLoader::getRealPath('application.helper.smarty') . '/function.categoryUrl.php');
			return new RedirectResponse(createCategoryUrl(array('data' => $foundCategories->get(0)->toArray()), $this->application));
		}

		$filterArray = array();
		foreach ($this->filters as $filter)
		{
			$filterArray[] = $filter->toArray();
		}

		// display theme
		if ($theme = CategoryPresentation::getThemeByCategory($this->category))
		{
			$this->application->setTheme($theme->getTheme());
		}

		// load EAV data
		foreach ($products as $key => $product)
		{
			if (isset($product['Manufacturer']))
			{
				ActiveRecordModel::addToEavQueue('Manufacturer', $products[$key]['Manufacturer']);
			}
		}

//		ActiveRecordModel::addToEavQueue('Category', $categoryArray);

		ActiveRecordModel::loadEav();

		$response = new ActionResponse();
		$response->set('id', $this->categoryID);

		$response->set('products', $products);
		$response->set('count', $totalCount);
		$response->set('offsetStart', $offsetStart);
		$response->set('offsetEnd', $offsetEnd);
		$response->set('perPage', $perPage);
		$response->set('currentPage', $currentPage);
		$response->set('category', $categoryArray);
		$response->set('subCategories', $subCategories);

		$response->set('currency', $this->getRequestCurrency());
		$response->set('sortOptions', $sort);
		$response->set('sortForm', $this->buildSortForm($order));
		$response->set('categoryNarrow', $categoryNarrow);
		$response->set('subCatFeatured', $subCatFeatured);
		$response->set('allFilters', $filters);
		$response->set('showAll', $showAll);
		$response->set('appliedFilters', $filterArray);
		$response->set('layout', $listLayout);

		$filterChainHandle = $this->setUpBreadCrumbAndReturnFilterChainHandle($currentPage);
		$response->set('url', $this->getCategoryPageUrl(array('page' => '_000_', 'filters' => $filterChainHandle)));
		$response->set('layoutUrl', $this->getCategoryPageUrl(array('filters' => $filterChainHandle, 'query' => array('layout' => ('GRID' == $listLayout) ? 'list' : 'grid'))));
		$response->set('filterChainHandle', $filterChainHandle);

		if (isset($searchQuery))
		{
			$response->set('searchQuery', $searchQuery);
		}

		if (isset($foundCategories))
		{
			$response->set('foundCategories', $foundCategories->toArray());
		}

		// look for manufacturer filter
		foreach ($this->filters as $filter)
		{
			if ($filter instanceof ManufacturerFilter)
			{
				$response->set('manufacturerFilter', $filter->toArray());
			}
		}

		return $response;
	}

	/**
	 *	Display a list of all categories
	 */
	public function all()
	{
		$root = Category::getRootNode();
		$f = new ARSelectFilter(new MoreThanCond(new ARFieldHandle('Category', $root->getProductCountField()), 0));
		$f->mergeCondition(new NotEqualsCond(new ARFieldHandle('Category', 'ID'), $root->getID()));
		$f->setOrder(MultiLingualObject::getLangOrderHandle(new ARFieldHandle('Category', 'name')));

		return new ActionResponse('categories', ActiveRecordModel::getRecordSetArray('Category', $f, array('CategoryImage')));
	}

	/**
	 *	Display a list of all products
	 */
	public function allProducts()
	{
		$this->request->set('page', $this->request->get('id', 1));
		$this->request->set('id', 1);
		$this->request->set('includeSub', true);
		$this->removeBlock('PRODUCT_LISTS');

		$response = $this->index();
		$response->set('subCategories', array());
		$response->set('categoryNarrow', array());
		$response->set('url', $this->router->createUrl(array('controller' => 'category', 'action' => 'allProducts', 'id' => 0)));

		$category = $response->get('category');
		$category['name_lang'] = $this->translate('_all_products');
		$response->set('category', $category);

		return $response;
	}

	private function getCategoryPageUrl($params = array())
	{
		if (empty($params['filters']))
		{
			unset($params['filters']);
		}

		$urlParams = array('controller' => 'category', 'action' => 'index',
				   'id' => $this->request->get('id'),
				   'cathandle' => $this->request->get('cathandle'),
				   );

		$urlParams = array_merge($urlParams, $params);

		return $this->router->createURL($urlParams, true);
	}

	private function getProductsArray(ProductFilter $filter)
	{
		$products = $this->category->getProductArray($filter, array('Manufacturer', 'DefaultImage' => 'ProductImage', 'Category'));

		// get product specification and price data
		ProductSpecification::loadSpecificationForRecordSetArray($products);
		ProductPrice::loadPricesForRecordSetArray($products);

		return $products;
	}

	/**
	 *  Apply selected product sort order to ARSelectFilter instance
	 */
	private function applySortOrder(ARSelectFilter $selectFilter, $order)
	{
		if (substr($order, 0, 12) == 'product_name')
		{
			$dir = array_pop(explode('_', $order)) == 'asc' ? 'ASC' : 'DESC';
			$selectFilter->setOrder(Product::getLangOrderHandle(new ARFieldHandle('Product', 'name')), $dir);
		}
		else if (substr($order, 0, 5) == 'price')
		{
			$dir = array_pop(explode('_', $order)) == 'asc' ? 'ASC' : 'DESC';
			$selectFilter->setOrder(new ARFieldHandle('ProductPrice', 'price'), $dir);
			$selectFilter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');
		}
		else if ('newest_arrivals' == $order)
		{
			$selectFilter->setOrder(new ARFieldHandle('Product', 'dateCreated'), 'DESC');
		}
		else if ('rating' == $order)
		{
			$selectFilter->setOrder(new ARFieldHandle('Product', 'rating'), 'DESC');
		}
		else if ('sales_rank' == $order)
		{
			$selectFilter->setOrder(new ARFieldHandle('Product', 'salesRank'), 'DESC');
		}
		else
		{
			$selectFilter->setOrder(new ARFieldHandle('Product', 'isFeatured'), 'DESC');
			$selectFilter->setOrder(new ARFieldHandle('Product', 'salesRank'), 'DESC');
			$selectFilter->setOrder(new ARFieldHandle('Product', 'position'), 'DESC');
		}
	}

	/**
	 *  Create breadcrumb
	 */
	private function setUpBreadCrumbAndReturnFilterChainHandle($page)
	{
		// get category path for breadcrumb
		$path = $this->category->getPathNodeSet()->toArray();

		include_once(ClassLoader::getRealPath('application.helper.smarty') . '/function.categoryUrl.php');
		foreach ($path as $nodeArray)
		{
			$url = createCategoryUrl(array('data' => $nodeArray), $this->application);
			$this->addBreadCrumb($nodeArray['name_lang'], $url);
		}

		// add filters to breadcrumb
		if (!isset($nodeArray))
		{
			$nodeArray = $this->category->toArray();
		}

		$params = array('data' => $nodeArray, 'filters' => array());
		foreach ($this->filters as $filter)
		{
			$filter = $filter->toArray();
			$params['filters'][] = $filter;

			// add current page number to the last item URL
			if (count($params['filters']) == count($this->filters))
			{
				$params['page'] = $page;
			}

			$url = createCategoryUrl($params, $this->application);
			$this->addBreadCrumb($filter['name_lang'], $url);
		}

		// set return path
		if (isset($url))
		{
			$this->router->setReturnPath($this->router->getRouteFromUrl($url));
		}

		// get filter chain handle
		$filterChainHandle = array();
		if (!empty($params['filters']))
		{
			foreach ($params['filters'] as $filter)
			{
				$filterChainHandle[] = filterHandle($filter);
			}
		}

		return implode(',', $filterChainHandle);
	}

	/**
	 *  Narrow search results by categories
	 */
	private function getSubcategoriesBySearchQuery(ARSelectFilter $selectFilter, $subCategories)
	{
		if (count($subCategories) > 0)
		{
			$case = new ARCaseHandle();
			$index = array();
			foreach ($subCategories as $key => $cat)
			{
				if (Category::ROOT_ID == $cat['ID'])
				{
					continue;
				}

				$cond = new EqualsOrMoreCond(new ARFieldHandle('Category', 'lft'), $cat['lft']);
				$cond->addAND(new EqualsOrLessCond(new ARFieldHandle('Category', 'rgt'), $cat['rgt']));
				$case->addCondition($cond, new ARExpressionHandle($cat['ID']));
				$index[$cat['ID']] = $key;
			}

			$query = new ARSelectQueryBuilder();
			$query->includeTable('Product');

			$filter = clone $selectFilter;
			$filter->setLimit(0);
			$filter->resetOrder();
			$filter->setOrder(new ARExpressionHandle('cnt'), 'DESC');
			$filter->setGrouping(new ARExpressionHandle('ID'));

			foreach ($this->filters as $f)
			{
				$f->defineJoin($filter);
			}

			$query->setFilter($filter);
			$query->addField($case->toString(), null, 'ID');
			$query->addField('COUNT(*)', null, 'cnt');
			$query->joinTable('Category', 'Product', 'ID', 'categoryID');
			$query->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');

			$count = $query->getPreparedStatement(ActiveRecord::getDBConnection())->executeQuery();

			$categoryNarrow = array();

			foreach ($count as $cat)
			{
				if (!isset($index[$cat['ID']]))
				{
					continue;
				}

				$data = $subCategories[$index[$cat['ID']]];
				$data['searchCount'] = $cat['cnt'];
				$categoryNarrow[] = $data;
			}

			return $categoryNarrow;
		}
	}

	private function getSubSubCategories(&$subCategories)
	{
		$ids = array();
		$index = array();
		foreach ($subCategories as $key => $cat)
		{
			$ids[] = $cat['ID'];
			$index[$cat['ID']] = $key;
		}

		$f = new ARSelectFilter(new INCond(new ARFieldHandle('Category', 'parentNodeID'), $ids));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('Category', 'isEnabled'), true));
		$f->setOrder(new ARFieldHandle('Category', 'parentNodeID'));
		$f->setOrder(new ARFieldHandle('Category', 'lft'));

		$a = ActiveRecordModel::getRecordSetArray('Category', $f, Category::LOAD_REFERENCES);
		foreach ($a as $cat)
		{
			$subCategories[$index[$cat['parentNodeID']]]['subCategories'][] = $cat;
		}
	}

	private function getSubCatFeaturedProducts()
	{
		$selFilter = new ARSelectFilter(new EqualsCond(new ARFieldHandle('Product', 'isFeatured'), true));
		$selFilter->setOrder(new ARExpressionHandle('RAND()'));
		$selFilter->setLimit($this->config->get('NUM_PRODUCTS_PER_CAT'));

		$featuredFilter = new ProductFilter($this->category, $selFilter);
		$featuredFilter->includeSubcategories();

		return $this->getProductsArray($featuredFilter);
	}

	/**
	 * @return Form
	 */
	private function buildSortForm($order)
	{
		$form = new Form(new RequestValidator("productSort", $this->request));
		$form->enableClientSideValidation(false);
		$form->set('sort', $order);
		return $form;
	}

	protected function productListBlock()
	{
		// get list items
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('ProductList', 'categoryID'), $this->category->getID()));
		$f->setOrder(new ARFieldHandle('ProductList', 'position'));
		$f->setOrder(new ARFieldHandle('ProductListItem', 'productListID'));
		$f->setOrder(new ARFieldHandle('ProductListItem', 'position'));

		$items = array();
		foreach (ActiveRecordModel::getRecordSetArray('ProductListItem', $f, array('ProductList', 'Product', 'ProductImage')) as $item)
		{
			$entry =& $item['Product'];
			$entry['ProductList'] =& $item['ProductList'];
			$items[] =& $entry;
		}

		ProductSpecification::loadSpecificationForRecordSetArray($items);
		ProductPrice::loadPricesForRecordSetArray($items);

		// sort by lists
		$lists = array();
		foreach ($items as &$item)
		{
			$lists[$item['ProductList']['ID']][] =& $item;
		}

		$response = new BlockResponse();
		$response->set('lists', $lists);
		return $response;
	}

	protected function boxFilterBlock()
	{
		$filterGroups = $this->filterGroups;

	 	$response = new BlockResponse();

		// remove empty filter groups
		$maxCriteria = $this->config->get('MAX_FILTER_CRITERIA_COUNT');
		$showAll = $this->request->get('showAll');

		$url = $this->router->createUrlFromRoute($this->router->getRequestedRoute(), true);
		$url = $this->router->addQueryParams($url);
		foreach ((array)$filterGroups as $key => $grp)
		{
			if (empty($grp['filters']))
			{
				unset($filterGroups[$key]);
			}

			// hide excess criterias (by default only 5 per filter are displayed)
			else if (($showAll != $grp['ID']) && (count($grp['filters']) > $maxCriteria) && ($maxCriteria > 0))
			{
				$chunks = array_chunk($grp['filters'], $maxCriteria);
				$filterGroups[$key]['filters'] = $chunks[0];
				$filterGroups[$key]['more'] = $this->router->setUrlQueryParam($url, 'showAll', $grp['ID']);
			}
		}

		// filter by manufacturers
		$manFilters = $this->manFilters;

		if (count($manFilters) > $maxCriteria && $showAll != 'brand' && $maxCriteria > 0)
		{
			$chunks = array_chunk($manFilters, $maxCriteria);
			$manFilters = $chunks[0];
			$response->set('allManufacturers', $this->router->setUrlQueryParam($url, 'showAll', 'brand'));
		}

		if (!$this->category)
		{
			$this->category = Category::getRootNode();
			$this->category->load();
		}

		// index page filters
		if ($this->category->isRoot())
		{
			if (!$this->config->get('INDEX_MAN_FILTERS'))
			{
				$manFilters = array();
			}

			if (!$this->config->get('INDEX_PRICE_FILTERS'))
			{
				$this->priceFilters = array();
			}
		}
		// categories without own products
		else if (!$this->hasProducts)
		{
			if (!$this->config->get('DISPLAY_CAT_MAN_FILTERS'))
			{
				$manFilters = array();
			}

			if (!$this->config->get('DISPLAY_CAT_PRICE_FILTERS'))
			{
				$this->priceFilters = array();
			}
		}

		if ($this->config->get('ENABLE_MAN_FILTERS') && (count($manFilters) > 1))
		{
		 	$response->set('manGroup', array('filters' => $manFilters));
		}

		if ($this->config->get('ENABLE_PRICE_FILTERS') && (count($this->priceFilters) > 1))
		{
		 	$response->set('priceGroup', array('filters' => $this->priceFilters));
		}

		$filterArray = array();
		foreach ($this->filters as $filter)
		{
			$filterArray[] = $filter->toArray();
		}

		$response->set('filters', $filterArray);
	 	$response->set('category', $this->category->toArray());
	 	$response->set('groups', $filterGroups);

		return $response;
	}

	private function getFilterCounts()
	{
		// get category filter groups
		$filterGroups = $this->category->getFilterGroupArray();

		// get counts by filters, categories, etc
		$count = new ProductCount($this->productFilter, $this->application);
		$filtercount = $count->getCountByFilters();

		// get group filters
		if ($filterGroups)
		{
			$filters = $this->category->getFilterSet();

			// sort filters by group
			$sorted = array();
			foreach ($filters as $filter)
			{
				$cnt = isset($filtercount[$filter->getID()]) ? $filtercount[$filter->getID()] : 0;
				if (!$cnt || $cnt == $this->totalCount)
				{
					continue;
				}

				$array = $filter->toArray();
				$array['count'] = $cnt;

				$specFieldID = $filter instanceof SelectorFilter ? $filter->getSpecField()->getID() : $filter->filterGroup->get()->specField->get()->getID();
				$sorted[$specFieldID][] = $array;
			}

			// assign sorted filters to group arrays
			foreach ($filterGroups as $key => $group)
			{
			  	if (isset($sorted[$group['specFieldID']]))
			  	{
					$filterGroups[$key]['filters'] = $sorted[$group['specFieldID']];
				}
			}
		}

		$this->filterGroups = $filterGroups;

		// filter by manufacturers
		$isManufacturerFiltered = false;
		foreach ($this->filters as $filter)
		{
			if ($filter instanceof ManufacturerFilter)
			{
				$isManufacturerFiltered = true;
			}
		}

		$manFilters = array();

		// check for filter counts only if the manufacturer filter hasn't been applied already
		if (!$isManufacturerFiltered)
		{
			foreach ($count->getCountByManufacturers() as $filterData)
			{
				$mFilter = new ManufacturerFilter($filterData['ID'], $filterData['name']);
				$manFilter = $mFilter->toArray();
				$manFilter['count'] = $filterData['cnt'];
				$manFilters[] = $manFilter;
			}
		}

		$this->manFilters = $manFilters;

		// filter by prices
		$priceFilters = array();
		foreach ($count->getCountByPrices() as $filterId => $count)
		{
			$pFilter = new PriceFilter($filterId, $this->application);
			$priceFilter = $pFilter->toArray();
			$priceFilter['count'] = $count;
			if ($count && $count != $this->totalCount)
			{
				$priceFilters[] = $priceFilter;
			}
		}

		$this->priceFilters = $priceFilters;
	}

	public function getAppliedFilters(FrontendController $controller = null)
	{
		if (!$controller)
		{
			$controller = $this;
		}

		if ($this->filters)
		{
			return $this->filters;
		}

		$request = $controller->getRequest();
		$app = $controller->getApplication();

		if ($request->get('filters'))
		{
			$valueFilterIds = array();
			$selectorFilterIds = array();
			$manufacturerFilterIds = array();
			$priceFilterIds = array();
			$searchFilters = array();

			$filters = explode(',', $request->get('filters'));

			foreach ($filters as $filter)
			{
			  	$pair = explode('-', $filter);

			  	if (count($pair) < 2)
			  	{
					continue;
				}

				$id = array_pop($pair);

				if (substr($id, 0, 1) == 'v')
				{
					$selectorFilterIds[] = substr($id, 1);
				}
				else if (substr($id, 0, 1) == 'm')
				{
					$manufacturerFilterIds[] = substr($id, 1);
				}
				else if (substr($id, 0, 1) == 'p')
				{
					$priceFilterIds[] = substr($id, 1);
				}
				else if ('s' == $id)
				{
					$searchFilters[] = implode('-', $pair);
				}
				else
				{
					$valueFilterIds[] = $id;
				}
			}

			// get value filters
			if ($valueFilterIds)
			{
				$f = new ARSelectFilter();
				$c = new INCond(new ARFieldHandle('Filter', 'ID'), $valueFilterIds);
				$f->setCondition($c);
				$filters = ActiveRecordModel::getRecordSet('Filter', $f, Filter::LOAD_REFERENCES);
				foreach ($filters as $filter)
				{
					$this->filters[] = $filter;
				}
			}

			if ($selectorFilterIds)
			{
				$f = new ARSelectFilter();
				$c = new INCond(new ARFieldHandle('SpecFieldValue', 'ID'), $selectorFilterIds);
				$f->setCondition($c);
				$filters = ActiveRecordModel::getRecordSet('SpecFieldValue', $f, array('SpecField', 'Category'));
				foreach ($filters as $filter)
				{
					$this->filters[] = new SelectorFilter($filter);
				}
			}

			if ($manufacturerFilterIds)
			{
				$f = new ARSelectFilter();
				$c = new INCond(new ARFieldHandle('Manufacturer', 'ID'), $manufacturerFilterIds);
				$f->setCondition($c);
				$manufacturers = ActiveRecordModel::getRecordSetArray('Manufacturer', $f);
				foreach ($manufacturers as $manufacturer)
				{
					$this->filters[] = new ManufacturerFilter($manufacturer['ID'], $manufacturer['name']);
				}
			}

			if ($priceFilterIds)
			{
				foreach ($priceFilterIds as $filterId)
				{
					$this->filters[] = new PriceFilter($filterId, $app);
				}
			}

			if ($searchFilters)
			{
				foreach ($searchFilters as $query)
				{
					$this->filters[] = new SearchFilter($query);
				}
			}
		}
	}

	public function getProductFilter()
	{
		return $this->productFilter;
	}
}

?>
