<?php

use category\Category;

/**
 * Index controller for frontend
 *
 * @author Integry Systems
 * @package application/controller
 */
class CategoryController extends CatalogController
{
  	protected $filters = array();

  	protected $productFilter;

  	protected $category;

	protected $categoryID = 1;

	protected $hasProducts = false;

	/*
	public function initialize()
  	{
	  	parent::initialize();
	  	$this->addBlock('FILTER_BOX', 'boxFilter', 'block/box/filter');
	  	$this->addBlock('FILTER_TOP', 'boxFilterTop', 'category/boxFilterTopBlock');
	  	$this->addBlock('PRODUCT_LISTS', 'productList', 'block/productList');
	  	$this->addBlock('RELATED_CATEGORIES', 'relatedCategories', 'category/block/relatedCategories');
	  	$this->addBlock('QUICK-SHOP', 'quickShopMenu', 'category/block/quickShopMenu');
	}
	*/

	public function indexAction()
	{
		$category = $this->getCategory();
		$query = new product\ProductFilter();
		
		if ($this->request->getJson('cats'))
		{
			$query->setCategory(Category::getRootNode(), true);
		}
		else
		{
			$query->setCategory($category, true);
		}
		
		$query->setCategory($category, true);
		$query->setEnabledOnly();
		$filters = $this->applyFilters($query);
		
		$query->columns('product\Product.*, product\ProductImage.*, category\Category.*')
			->join('product\ProductImage', 'product\Product.defaultImageID=product\ProductImage.ID', '', 'LEFT')
			->join('eav\EavObject', 'product\Product.eavObjectID=eav\EavObject.ID', '', 'LEFT');

		$set = $query->getQuery()->execute();
		
		$perPage = 10;
		$page = $this->request->getParam('page', 1);
		$paginator = new LivePaginator(
			array(
				"data" => $set,
				"limit"=> $perPage,
				"page" => $page
			)
		);
		
		//var_dump($paginator->getPaginate());exit;
		
		$products = array();
		foreach ($paginator->getPaginate()->items as $record)
		{
			$product = $record['product\Product'];
			$image = $record['product\ProductImage'];
			$product->set_DefaultImage($image);
			$product->set_Category($record['category\Category']);
			$product->getSpecification();
			$products[] = $product;
		}
		
		//\eav\EavSpecificationManager::loadSpecificationForRecordSet($products);

		$this->set('category', $category);
		$this->set('products', $products);
		$this->set('filters', $filters);
		$this->set('count', $set->count());
		$this->set('paginator', $paginator);
		$this->set('currency', $this->application->getDefaultCurrencyCode());
		
		if ($this->request->getJsonRawBody())
		{
			$this->view->pick('category/ajax');
		}
	}
	
	public function applyFilters($query)
	{
		$filters = $this->request->getJson('filters');
		if (!$filters)
		{
			$filters = $this->request->get('filters');
			if ($filters)
			{
				$filters = json_decode($filters, true);
			}
		}
		
		if ($filters)
		{
			$instances = filter\FilterGroup::query()->inWhere('ID', array_keys($filters))->execute();
			
			foreach ($instances as $filter)
			{
				$query->applyFilter($filter, $filters[$filter->getID()]);
			}
		}
		
		$cats = $this->request->getParam('cats');
		
		if ($cats)
		{
			$cats = array_filter($cats, 'is_numeric');
			$query->andWhere('SUBQUERY("SELECT COUNT(*) FROM ProductCategory WHERE ProductCategory.productID=Product.ID AND ProductCategory.categoryID IN (' . implode(', ', $cats) . ')") > 0');
		}
		else if (is_array($cats))
		{
			$query->andWhere('0 = 1');
		}
		
		if ($starts = $this->request->getParam('starts'))
		{
			$starts = substr($starts, 0, 10);
			$query->andWhere('SUBQUERY("SELECT dateValue FROM EavObjectValue WHERE EavObjectValue.objectID=EavObject.ID AND EavObjectValue.fieldID=11 LIMIT 1") >= :startdate:', array('startdate' => $starts));
		}
		
		if ($ends = $this->request->getParam('ends'))
		{
			$ends = substr($ends, 0, 10);
			$query->andWhere('SUBQUERY("SELECT dateValue FROM EavObjectValue WHERE EavObjectValue.objectID=EavObject.ID AND EavObjectValue.fieldID=12 LIMIT 1") <= :enddate:', array('enddate' => $ends));
		}
		
		return json_encode($filters);
	}

	public function xindexAction()
	{
		ClassLoader::import('application/model/presentation/CategoryPresentation');

		$this->getAppliedFilters();

		// presentation
		if ($theme = CategoryPresentation::getThemeByCategory($this->getCategory()))
		{
			if ($theme->getTheme())
			{
				$this->application->setTheme($theme->getTheme());
			}

			if ($layout = $theme->listStyle)
			{
				//$this->request = 'layout', strtolower($layout));
				//$this->config = 'LIST_LAYOUT', $layout);
				//$this->config = 'ALLOW_SWITCH_LAYOUT', false);
			}
		}

		// pagination
		$currentPage = $this->request->get('page', 1);
		$listLayout = $this->getListLayout();
		$perPage = $this->getProductLimitCount($listLayout);

		$offsetStart = (($currentPage - 1) * $perPage) + 1;
		$offsetEnd = $currentPage * $perPage;

		// create new search filter
		$query = $this->request->get('q');
		if ($query)
		{
			$searchFilter = new SearchFilter($query);
			$this->filters[] = $searchFilter;

			// search by category names
			$f = new ARSelectFilter();
			foreach (array($this->locale->getLocaleCode(), $this->application->getDefaultLanguageCode()) as $handle)
			{
				$langHandle = MultiLingualObject::getLangSearchHandle('Category.name', $handle);
				$f->andWhere(new LikeCond($langHandle, '%' . $query . '%'));
			}

			$foundCategories = ActiveRecordModel::getRecordSet('Category', $f, Category::LOAD_REFERENCES);
			foreach ($foundCategories as $category)
			{
				$category->getPathNodeSet();
			}

			$cleanedQuery = $searchFilter->getCleanedQuery($query);
			$this->logSearchQuery($cleanedQuery);
		}

		$productFilter = $this->getSelectFilter();

		if ($currentPage)
		{
			$productFilter->getBaseFilter()->limit($perPage, $offsetStart - 1);
		}

		// root category?
		if ($this->getCategory()->isRoot() && !$this->filters && !($this instanceof IndexController) && !$this->request->get('includeSub') && ($currentPage > 1))
		{
			return $this->response->redirect('index/index');
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

		foreach ($this->getCategory()->getSpecificationFieldArray() as $field)
		{
			if ($field['isSortable'])
			{
				$sortName = $field['dataType'] == SpecField::DATATYPE_NUMBERS ? '_sort_num' : '_sort_text';
				$sort[$field['ID'] . '-' . $field['handle'] . '_asc'] = $this->maketext($sortName . '_asc', $field['name_lang']);
				$sort[$field['ID'] . '-' . $field['handle'] . '_desc'] = $this->maketext($sortName . '_desc', $field['name_lang']);
			}
		}

		$order = $this->request->get('sort');

		$products = $this->getProductsArray($productFilter);
		$this->hasProducts = count($products) > 0;

		// pagination
		$count = new ProductCount($this->productFilter, $this->application);
		$totalCount = $count->getCategoryProductCount($productFilter);
		$offsetEnd = min($totalCount, $offsetEnd);
		$this->totalCount = $totalCount;

		// narrow by subcategories
		$subCategories = $this->getCategory()->getSubCategoryArray(Category::LOAD_REFERENCES);

		$categoryNarrow = array();
		if ((!empty($searchQuery) || $this->getCategory()->isRoot() || $this->filters) && $products)
		{
			$categoryNarrow = $this->getSubCategoriesBySearchQuery($productFilter->getSelectFilter(), $subCategories);
		}

		$categoryArray = $this->getCategory()->toArray();

		if (!$this->getCategory()->isRoot())
		{
			$this->redirect301($this->request->get('cathandle'), createHandleString($categoryArray['name_lang']));
		}

		// if all the results come from one category, redirect to this category
		if ((count($categoryNarrow) == 1) && (count($this->filters) == 1) && empty($foundCategories))
		{
			$canNarrow = true;

			foreach ($products as $product)
			{
				if ($product['Category']['ID'] == $this->getCategoryId())
				{
					$canNarrow = false;
				}
			}

			if ($canNarrow)
			{
				while (count($categoryNarrow) == 1)
				{
					if ($categoryNarrow[0]['searchCount'] != $totalCount)
					{
						break;
					}

					$this->category = Category::getInstanceByID($categoryNarrow[0]['ID'], Category::LOAD_DATA);
					$subCategories = $this->getCategory()->getSubCategoryArray(Category::LOAD_REFERENCES);
					if ($subCategories)
					{
						$subCategories[] = $categoryArray;
					}
					$categoryNarrow = $this->getSubCategoriesBySearchQuery($productFilter->getBaseFilter(), $subCategories);
				}

				include_once($this->config->getPath('application/helper/smarty') . '/function.categoryUrl.php');

				if (!$this->getCategory()->isRoot())
				{
					return new RedirectResponse(createCategoryUrl(array('data' => $this->getCategory()->toArray(), 'filters' => $this->filters), $this->application));
				}
			}
		}

		// get subcategory-subcategories
		if ($subCategories && $this->config->get('CAT_MENU_SUBS'))
		{
			$this->getSubSubCategories($subCategories);
		}

		// get subcategory featured products
		$subCatFeatured = array();
		if (($subCategories && !$products) || ($this instanceof IndexController))
		{
			$subCatFeatured = $this->getSubCatFeaturedProducts();
		}

		// if there were no products found, include subcategories in filter counts... except home page
		if (!$products || $this->getCategory()->isRoot())
		{
			$productFilter->getBaseFilter()->removeCondition('Product.categoryID = :Product.categoryID:', array('Product.categoryID' => $this->getCategory()->getID()));
			$productFilter->includeSubcategories();
		}

		// search redirects
		// no products found, but found one category name - redirect to this category
		if (isset($foundCategories) && (1 == $foundCategories->count()) && !$products)
		{
			include_once($this->config->getPath('application/helper/smarty') . '/function.categoryUrl.php');
			return new RedirectResponse(createCategoryUrl(array('data' => $foundCategories->shift()->toArray()), $this->application));
		}

		$filterArray = array();
		foreach ($this->filters as $filter)
		{
			$filterArray[] = $filter->toArray();
		}

		if ($this->config->get('DISPLAY_CATEGORY_FEATURED'))
		{
			$this->getFeaturedMainCategoryProducts($subCategories);
			$this->getFeaturedMainCategoryProducts($categoryNarrow);
		}


		/*
		$this->set('id', $this->getCategoryId());

		$this->set('context', $this->getContext());
		$this->set('products', $products);
		$this->set('count', $totalCount);
		$this->set('offsetStart', $offsetStart);
		$this->set('offsetEnd', $offsetEnd);
		$this->set('perPage', $perPage);
		$this->set('currentPage', $currentPage);
		$this->set('category', $categoryArray);
		$this->set('subCategories', $subCategories);

		$this->set('currency', $this->getRequestCurrency());
		$this->set('sortOptions', $sort);
		$this->set('sortForm', $this->buildSortForm($order));
		$this->set('sortField', $order);
		$this->set('categoryNarrow', $categoryNarrow);
		$this->set('subCatFeatured', $subCatFeatured);
		//$this->set('allFilters', $filters);
		//$this->set('showAll', $showAll);
		$this->set('appliedFilters', $filterArray);
		$this->set('layout', $listLayout);
		$this->set('listAttributes', $this->getListAttributes());

		$filterChainHandle = $this->setUpBreadCrumbAndReturnFilterChainHandle($currentPage);
		$this->set('url', $this->getCategoryPageUrl(array('page' => '_000_', 'filters' => $filterChainHandle)));
		$this->set('layoutUrl', $this->getCategoryPageUrl(array('filters' => $filterChainHandle, 'query' => array('layout' => ''))));
		$this->set('filterChainHandle', $filterChainHandle);
		*/

		if (isset($searchQuery))
		{
			$this->set('searchQuery', $searchQuery);
		}

		if (isset($foundCategories))
		{
			$this->set('foundCategories', $foundCategories->toArray());
		}

		// look for manufacturer filter
		foreach ($this->filters as $filter)
		{
			if ($filter instanceof ManufacturerFilter)
			{
				$this->set('manufacturerFilter', $filter->toArray());
			}
		}

		if ((1 == $currentPage) && $query)
		{
			$searchCon = new SearchController($this->application);
			$this->set('modelSearch', $searchCon->searchAll($cleanedQuery));
		}

	}

	/**
	 *	Display a list of all categories
	 */
	public function allAction()
	{
		$root = Category::getRootNode();
		$f = new ARSelectFilter(new MoreThanCond(new ARFieldHandle('Category', $root->getProductCountField()), 0));
		$f->andWhere(new NotEqualsCond('Category.ID', $root->getID()));
		$f->orderBy(MultiLingualObject::getLangOrderHandle('Category.name'));

		$allCategories = ActiveRecordModel::getRecordSetArray('Category', $f, array('CategoryImage'));

		$func = function_exists('mb_substr') ? 'mb_substr' : 'substr';
		$sorted = array();
		foreach ($allCategories as $category)
		{
			$letter = $func($category['name_lang'], 0, 1, 'UTF-8');
			$sorted[$letter][] = $category;
		}

		$this->set('sorted', $sorted);
		$this->set('totalCount', count($allCategories));
		$this->set('categories', $allCategories);
	}

	/**
	 *	Display a list of all products
	 */
	public function allProductsAction()
	{
		/*
		$this->request = 'page', $this->request->get('id', 1));
		$this->request = 'id', 1);
		$this->request = 'includeSub', true);
		*/
		$this->removeBlock('PRODUCT_LISTS');

		$response = $this->index();

		$this->set('subCategories', array());
		$this->set('categoryNarrow', array());
		//$this->set('url', $this->url->get('category/allProducts', 'id' => 0)));

		$category = $response->get('category');
		$category['name_lang'] = $this->translate('_all_products');
		$this->set('category', $category);

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
		$products = $this->getCategory()->getProductArray($filter, array('Manufacturer', 'DefaultImage' => 'ProductImage', 'Category'));

		// get product specification and price data
		ProductSpecification::loadSpecificationForRecordSetArray($products);
		ProductPrice::loadPricesForRecordSetArray($products);

		return $products;
	}

	/**
	 *  Create breadcrumb
	 */
	private function setUpBreadCrumbAndReturnFilterChainHandle($page)
	{
		$last = $this->addCategoriesToBreadCrumb($this->getCategory()->getPathNodeArray(), true);
		$params = $this->addFiltersToBreadCrumb($last, $page);

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

				$cond = new EqualsOrMoreCond('Category.lft', $cat['lft']);
				$cond->andWhere(new EqualsOrLessCond('Category.rgt', $cat['rgt']));
				$case->addCondition($cond, new ARExpressionHandle($cat['ID']));
				$index[$cat['ID']] = $key;
			}

			$query = new ARSelectQueryBuilder();
			$query->includeTable('Product');

			$filter = clone $selectFilter;
			$filter->limit(0);
			$filter->reorderBy();
			$filter->orderBy(new ARExpressionHandle('cnt'), 'DESC');
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

			$statement = $query->getPreparedStatement(ActiveRecord::getDBConnection());
			$statement->execute();
			$count = $statement->fetchAll(PDO::FETCH_ASSOC);

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

		$f = new ARSelectFilter(new INCond('Category.parentNodeID', $ids));
		$f->andWhere('Category.isEnabled = :Category.isEnabled:', array('Category.isEnabled' => true));
		$f->orderBy('Category.parentNodeID');
		$f->orderBy('Category.lft');

		$a = ActiveRecordModel::getRecordSetArray('Category', $f, Category::LOAD_REFERENCES);
		foreach ($a as $cat)
		{
			$subCategories[$index[$cat['parentNodeID']]]['subCategories'][] = $cat;
		}
	}

	private function getSubCatFeaturedProducts()
	{
		$cache = $this->application->getCache();
		$namespace = 'subcategory_featured_' . $this->application->getLocaleCode();
		$id = $this->getCategory()->getID();
		$key = array($namespace, $id);

		if ($products = $cache->get($key))
		{
			return $products;
		}

		$count = $this->config->get('FEATURED_COUNT');
		if ('GRID' == $this->getListLayout())
		{
			$row = $this->config->get('LAYOUT_GRID_COLUMNS');
			$count = ceil($count / $row) * $row;
		}

		$selFilter = new ARSelectFilter();
		if (!$this->config->get('FEATURED_RANDOM'))
		{
			$selFilter->andWhere('Product.isFeatured = :Product.isFeatured:', array('Product.isFeatured' => true));
		}
		else
		{
			$selFilter->orderBy(new ARExpressionHandle('Product.isFeatured=1'), 'DESC');
		}

		$featuredFilter = new ProductFilter($this->getCategory(), $selFilter);
		$featuredFilter->includeSubcategories();

		$selFilter->orderBy(new ARExpressionHandle('RAND()'));
		$selFilter->limit($count);

		$ids = ActiveRecord::getRecordSetFields('Product', $featuredFilter->getSelectFilter(), array('Product.ID'), array('Category', 'Manufacturer'));
		$rand = array();
		foreach ($ids as $id)
		{
			$rand[] = $id['ID'];
		}

		$featuredFilter = new ProductFilter(Category::getRootNode(), select(in('Product.ID', $rand)));
		$featuredFilter->includeSubcategories();

//		$cache = $key, $this->getProductsArray($featuredFilter), time() + 1800);

		return $cache->get($key);
	}

	/**
	 * @return Form
	 */
	private function buildSortForm($order)
	{
		$form = new Form($this->getValidator("productSort", $this->request));
		$form->enableClientSideValidation(false);
		//$form = 'sort', $order);
		return $form;
	}

	protected function quickShopMenuBlock()
	{
		$context = $this->getContext();
		$context['category'] = $this->getCategoryId();

		$response = new BlockResponse();
		$this->set('context', $context);
	}

	protected function relatedCategoriesBlock()
	{
		$f = select(eq('CategoryRelationship.categoryID', $this->getCategory()->getID()));
		$f->orderBy(f('CategoryRelationship.position'));
		$categories = array();
		foreach (ActiveRecordModel::getRecordSet('CategoryRelationship', $f, array('Category')) as $rel)
		{
			$category = $rel->relatedCategory;
			$category->getPathNodeSet();
			$categories[] = $category->toArray();
		}

		if ($categories)
		{
			return new BlockResponse('categories', $categories);
		}
	}

	protected function productListBlock()
	{
		// get list items
		$f = query::query()->where('ProductList.categoryID = :ProductList.categoryID:', array('ProductList.categoryID' => $this->getCategory()->getID()));
		$f->orderBy('ProductList.position');
		$f->orderBy('ProductListItem.productListID');
		$f->orderBy('ProductListItem.position');

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
		$this->set('lists', $lists);
	}

	protected function boxFilterBlock($includeAppliedFilters = true)
	{
		$filterStyle = $this->config->get('FILTER_STYLE');
		if('FILTER_STYLE_CHECKBOXES' == $filterStyle)
		{
			$includeAppliedFilters = false;
		}
		$count = $this->getFilterCounts($includeAppliedFilters);

		$filterGroups = $count['groups'];

		// remove empty filter groups
		$maxCriteria = $this->config->get('MAX_FILTER_CRITERIA_COUNT');
		$showAll = $this->request->get('showAll');

		$url = $this->router->createUrlFromRoute($this->router->getRequestedRoute(), true);
		$url = $this->router->addQueryParams($url);
		foreach ((array)$filterGroups as $key => $grp)
		{
			foreach ((array)$grp['filters'] as $k => $f)
			{
				if (!$f['count'])
				{
					unset($filterGroups[$key]['filters'][$k]);
				}
			}

			if (empty($grp['filters']))
			{
				unset($filterGroups[$key]);
			}
			// hide excess criterias (by default only 5 per filter are displayed)
			else if (($showAll != $grp['ID']) && (count($grp['filters']) > $maxCriteria) && ($maxCriteria > 0))
			{
				$filterGroups[$key]['more'] = $this->router->setUrlQueryParam($url, 'showAll', $grp['ID']);
			}
		}

	 	$response = new BlockResponse();

		// filter by manufacturers
		$manFilters = $count['manufacturers'];

		if (count($manFilters) > $maxCriteria && $showAll != 'brand' && $maxCriteria > 0)
		{
			$this->set('allManufacturers', $this->router->setUrlQueryParam($url, 'showAll', 'brand'));
		}

		if (!$this->getCategory())
		{
			$this->category = Category::getRootNode();
			$this->category->load();
		}

		$priceFilters = $count['prices'];

		// hide price filters from side menu if a price filter is applied
		if ($includeAppliedFilters)
		{
			foreach ($this->filters as $filter)
			{
				if ($filter instanceof PriceFilter)
				{
					$priceFilters = array();
				}
			}
		}

		// index page filters
		if ($this->getCategory()->isRoot())
		{
			if (!$this->config->get('INDEX_MAN_FILTERS'))
			{
				$manFilters = array();
			}

			if (!$this->config->get('INDEX_PRICE_FILTERS'))
			{
				$priceFilters = array();
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
				$priceFilters = array();
			}
		}

		if ($this->config->get('ENABLE_MAN_FILTERS') && count($manFilters) > 1)
		{
		 	$this->set('manGroup', array('filters' => $manFilters));
		}

		if ($this->config->get('ENABLE_PRICE_FILTERS') && (count($count['prices']) > 1))
		{
		 	foreach ($priceFilters as $key => $filter)
		 	{
		 		if (!$filter['count'])
		 		{
		 			unset($priceFilters[$key]);
				}
			}

		 	$this->set('priceGroup', array('filters' => $priceFilters));
		}

		$appliedFilterArray = $this->getAppliedFilterArray();
		$this->set('filters', $appliedFilterArray);
		if('FILTER_STYLE_CHECKBOXES' == $filterStyle)
		{
			$IDs = array();
			foreach($appliedFilterArray as $item)
			{
				$IDs[] = $item['ID'];
			}
			$this->set('filtersIDs', $IDs);
		}
	 	$this->set('category', $this->getCategory()->toArray());
	 	$this->set('groups', $filterGroups);
	}

	private function getAppliedFilterArray()
	{
		$filterArray = array();
		foreach ($this->filters as $filter)
		{
			$id = $filter->getID();
			if (strpos($id, '_'))
			{
				$id = 'v' . array_pop(explode('_', $id));
			}

			$filterArray[$id] = $filter->toArray();
		}

		return $filterArray;
	}

	public function boxFilterTopBlockAction()
	{
		if (!$this->productFilter)
		{
			$this->index();
		}

		if ($this->config->get('TOP_FILTER_CONTINUOS'))
		{
			$groups = $this->getCategory()->getFilterGroupSet()->filter('displayLocation', FilterGroup::LOC_TOP);

			// find filters that will be included for selection automatically
			$appliedFilters = array();
			foreach($this->filters as $filter)
			{
				if (($filter instanceof PriceFilter && !$this->config->get('TOP_FILTER_PRICE')) ||
					($filter instanceof ManufacturerFilter && !$this->config->get('TOP_FILTER_MANUFACTURER')) ||
					($filter instanceof SearchFilter) ||
					($filter instanceof SpecificationFilterInterface && ($filter->getFilterGroup()->displayLocation != FilterGroup::LOC_TOP))
					)
				{
					$appliedFilters[] = $filter;
				}
			}

			$productFilter = clone $this->productFilter;
			$productCount = new ProductCount($productFilter, $this->application);

			if ($this->config->get('TOP_FILTER_PRICE'))
			{
				$productFilter->setFilters($appliedFilters);
				$priceFilters = array(
									'filters' => $this->removeEmptyFilters($this->createPriceFilterSet($productCount->getCountByPrices(true))),
									'appliedFilters' => $this->transformToArray($appliedFilters)
								);

				foreach ($this->filters as $index => $filter)
				{
					if ($filter instanceof PriceFilter)
					{
						$appliedFilters[] = $filter;
					}
				}
			}

			if ($this->config->get('TOP_FILTER_MANUFACTURER'))
			{
				$productFilter->setFilters($appliedFilters);
				$manFilters = array(
								'filters' => $this->removeEmptyFilters($this->createManufacturerFilterSet($productCount->getCountByManufacturers(true))),
								'appliedFilters' => $this->transformToArray($appliedFilters)
								);

				foreach ($this->filters as $index => $filter)
				{
					if ($filter instanceof ManufacturerFilter)
					{
						$appliedFilters[] = $filter;
					}
				}
			}

			$categoryFilters = $this->getCategory()->getFilterSet();

			$filterGroups = $this->getCategory()->getFilterGroupSet()->filter('displayLocation', FilterGroup::LOC_TOP);
			$groups = array();
			foreach ($filterGroups as $group)
			{
				$appliedGroupFilter = $this->getFiltersByGroup($this->filters, $group);

				$groups[$group->getID()] = $group->toArray();

				if (isset($stop))
				{
					continue;
				}

				$productFilter->setFilters($appliedFilters);
				$c = $productCount->getCountByFilterSet($this->getFiltersByGroup($categoryFilters, $group), true);
				$groups[$group->getID()] = array_shift($this->createFilterGroupSet(array($groups[$group->getID()]), $c));
				$groups[$group->getID()]['filters'] = $this->removeEmptyFilters($groups[$group->getID()]['filters']);
				$groups[$group->getID()]['appliedFilters'] = $this->transformToArray($appliedFilters);

				if (!$appliedGroupFilter)
				{
					$stop = true;
				}
				else
				{
					$appliedFilters[] = array_shift($appliedGroupFilter);
				}
			}

			$response = new BlockResponse();

			if (isset($manFilters))
			{
				$this->set('manGroup', $manFilters);
			}

			if (isset($priceFilters))
			{
				$this->set('priceGroup', $priceFilters);
			}

			$this->set('filters', $this->getAppliedFilterArray());
			$this->set('category', $this->getCategory()->toArray());
			$this->set('groups', $groups);

		}
		else
		{
			return $this->boxFilterBlock(false);
		}
	}

	private function transformToArray($filters)
	{
		$res = array();

		foreach ($filters as $filter)
		{
			$arr = $filter->toArray();
			$res[$arr['ID']] = $arr;
		}

		return $res;
	}

	private function removeEmptyFilters($filterArray)
	{
		$res = array();
		foreach ($filterArray as $filter)
		{
			if ($filter['count'])
			{
				$res[] = $filter;
			}
		}

		return $res;
	}

	private function getFiltersByGroup($array, FilterGroup $group)
	{
		$res = array();
		foreach ($array as $index => $filter)
		{
			if ($filter instanceof SpecificationFilterInterface)
			{
				if ($filter->getFilterGroup()->getID() === $group->getID())
				{
					$res[$index] = $filter;
				}
			}
		}

		return $res;
	}

	private function getFilterCounts($includeAppliedFilters)
	{
		$productFilter = $this->getProductFilter();
		if (!$productFilter)
		{
			$productFilter = new ProductFilter($this->getCategory(), new ARSelectFilter());
		}

		$count = new ProductCount($productFilter, $this->application);

		// get category filter groups
		$filterGroups = $this->getCategory()->getFilterGroupArray();

		$filterGroups = $this->createFilterGroupSet($filterGroups, $count->getCountByFilters($includeAppliedFilters));

		$manFilters = $this->createManufacturerFilterSet($count->getCountByManufacturers($includeAppliedFilters));
		$priceFilters = $this->createPriceFilterSet($count->getCountByPrices($includeAppliedFilters));

		return array('groups' => $filterGroups, 'prices' => $priceFilters, 'manufacturers' => $manFilters);
	}

	private function createFilterGroupSet($filterGroups, $filterArray)
	{
		// get group filters
		if ($filterGroups)
		{
			$filterStyle = $this->config->get('FILTER_STYLE');
			$filters = $this->getCategory()->getFilterSet();

			// sort filters by group
			$sorted = array();
			foreach ($filters as $filter)
			{
				$cnt = isset($filterArray[$filter->getID()]) ? $filterArray[$filter->getID()] : 0;
				if ((!$cnt || $cnt == $this->totalCount) && $filter->getFilterGroup()->displayLocation == FilterGroup::LOC_SIDE)
				{
					// when filter style is set to checkboxes and filtering by only one selector filter
					// this continue removes (ignores here) selected filter from side menu.
					if ('FILTER_STYLE_CHECKBOXES' !=  $filterStyle)
					{
						continue;
					}
				}

				$array = $filter->toArray();
				$array['count'] = $cnt;

				$specFieldID = $filter instanceof SelectorFilter ? $filter->getSpecField()->getID() : $filter->filterGroup->specField->getID();
				$sorted[$specFieldID][] = $array;
			}

			// assign sorted filters to group arrays
			foreach ($filterGroups as $key => $group)
			{
				if (isset($sorted[$group['SpecField']['ID']]))
				{
					$group['specFieldID'] = $group['SpecField']['ID'];
				}

				if (isset($sorted[$group['specFieldID']]))
				{
					$filterGroups[$key]['filters'] = $sorted[$group['specFieldID']];
				}
			}
		}

		return $filterGroups;
	}

	private function createManufacturerFilterSet($filterArray)
	{
		$manFilters = array();

		foreach ((array)$filterArray as $filterData)
		{
			$mFilter = new ManufacturerFilter($filterData['ID'], $filterData['name']);
			$manFilter = $mFilter->toArray();
			$manFilter['count'] = $filterData['cnt'];
			$manFilters[] = $manFilter;
		}

		return $manFilters;
	}

	private function createPriceFilterSet($filterArray)
	{
		$priceFilters = array();

		foreach ((array)$filterArray as $filterId => $count)
		{
			$pFilter = new PriceFilter($filterId, $this->application);
			$priceFilter = $pFilter->toArray();
			if ($count && $count == $this->totalCount)
			{
				//$count = 0;
			}
			$priceFilter['count'] = $count;

			$priceFilters[] = $priceFilter;
		}

		return $priceFilters;
	}

	public function getCategoryIdAction()
	{
		return $this->getCategory()->getID();
	}

	public function getProductFilterAction()
	{
		return $this->productFilter;
	}

	private function getProductLimitCount($listLayout)
	{
		return ('GRID' == $listLayout) ?
						$this->config->get('LAYOUT_GRID_COLUMNS') * $this->config->get('LAYOUT_GRID_ROWS') :
						$this->config->get('NUM_PRODUCTS_PER_CAT');
	}

	private function getListLayout()
	{
		$layout = $this->request->get('layout');
		return $layout && $this->config->get('ALLOW_SWITCH_LAYOUT') ?
						(in_array($layout, array('grid', 'list', 'table')) ? strtoupper($layout) : 'LIST') :
						$this->config->get('LIST_LAYOUT');
	}

	private function logSearchQuery($query)
	{
		$query = strtolower($query);
		$searchLog = $this->session->get('searchLog', array());
		if (!isset($searchLog[$query]))
		{
			$log = SearchLog::getNewInstance($query);
			$log->ip = $this->request->getIPLong();
			$log->save();

			$searchLog[$query] = true;
			//$this->session = 'searchLog', $searchLog);
		}
	}

	private function getFeaturedMainCategoryProducts(&$categories)
	{
		$cache = $this->application->getCache();

		$namespace = 'category_featured';
		$products = array();
		foreach ((array)$categories as $category)
		{
			$key = array($namespace, $category['ID']);
			if ($product = $cache->get($key))
			{
				$products[] = $product;
				continue;
			}

			$cat = Category::getInstanceByID($category['ID'], Category::LOAD_DATA);
			$pf = new ProductFilter($cat, new ARSelectFilter());
			$pf->includeSubcategories();
			$f = $cat->getProductsFilter($pf);
			$f->andWhere('Product.isFeatured = :Product.isFeatured:', array('Product.isFeatured' => true));
			$f->limit(1);
			$f->orderBy(new ARExpressionHandle('RAND()'));

			$product = array_pop(ActiveRecordModel::getRecordSetArray('Product', $f, array('ProductImage', 'Category', 'Manufacturer')));
			if (!$product)
			{
				$product = array('ID' => 0);
			}

			//$cache = $key, $product, 1800);

			$products[] = $product;
		}

		ProductPrice::loadPricesForRecordSetArray($products);

		foreach ($products as $key => $product)
		{
			$categories[$key]['featuredProduct'] = $product;
		}
	}

	private function getListAttributes()
	{
		$res = array();
		foreach ($this->getCategory()->getSpecificationFieldArray() as $field)
		{
			if ($field['isDisplayedInList'])
			{
				$res[] = $field;
			}
		}

		return $res;
	}

	protected function getCategory()
	{
		if (!$this->category)
		{
			$this->category = Category::getInstanceById($this->request->getParam('id', 1));
		}
		
		$_REQUEST['__current_cat'] = $this->category->getID();

		return $this->category;
	}
}

function hasFilters($array)
{
	foreach ((array)$array as $filter)
	{
		if (!empty($filter['count']))
		{
			return true;
		}
	}
}

?>
