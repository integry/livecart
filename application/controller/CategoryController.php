<?php

ClassLoader::import("application.controller.FrontendController");
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.filter.Filter');
ClassLoader::import('application.model.filter.SelectorFilter');
ClassLoader::import('application.model.filter.ManufacturerFilter');
ClassLoader::import('application.model.filter.PriceFilter');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductFilter');
ClassLoader::import('application.model.product.ProductCount');

/**
 * Index controller for frontend
 *
 * @package application.controller
 */
class CategoryController extends FrontendController
{
  	protected $filters = array();
  	
  	protected $productFilter;
  	
  	protected $category;
  	
	protected $categoryID = 1;
	  
	public function init()
  	{
	  	parent::init();
	  	$this->addBlock('FILTER_BOX', 'boxFilter', 'block/box/filter');	    
	}
  
	public function index()
	{
		$this->categoryID = $this->request->getValue('id');

		// get category instance
		$this->category = Category::getInstanceById($this->categoryID, Category::LOAD_DATA);

		// get category path for breadcrumb
		$path = $this->category->getPathNodeSet();
		include_once(ClassLoader::getRealPath('application.helper') . '/function.categoryUrl.php');
		foreach ($path as $node)
		{
			$nodeArray = $node->toArray();
			$url = smarty_function_categoryUrl(array('data' => $nodeArray), false);
			$this->addBreadCrumb($nodeArray['name_lang'], $url);
		}
	
		$this->getAppliedFilters();

		// add filters to breadcrumb
		$params = array('data' => $nodeArray, 'filters' => array());
		foreach ($this->filters as $filter)
		{
			$filter = $filter->toArray();
			$params['filters'][] = $filter;
			$url = smarty_function_categoryUrl($params, false);
			$this->addBreadCrumb($filter['name_lang'], $url);
		}
	
	    // get filter chain handle
        $filterChainHandle = array();
        foreach ($params['filters'] as $filter)
	    {
            $filterChainHandle[] = filterHandle($filter);
        }
        $filterChainHandle = implode(',', $filterChainHandle);
	
		// pagination
		$currentPage = $this->request->getValue('page') 
			or $currentPage = 1;

		$perPage = $this->config->getValue('NUM_PRODUCTS_PER_CAT');
		$offsetStart = (($currentPage - 1) * $perPage) + 1;
		$offsetEnd = $currentPage * $perPage;
		
		$selectFilter = new ARSelectFilter();
		$selectFilter->setLimit($perPage, $offsetStart - 1);

		// setup ProductFilter
		$productFilter = new ProductFilter($this->category, $selectFilter);
		foreach ($this->filters as $filter)
		{
			$productFilter->applyFilter($filter);  
		}

		$this->productFilter = $productFilter;

		$products = $this->category->getProductsArray($productFilter, array('Manufacturer', 'DefaultImage' => 'ProductImage'));

		// get product specification and price data
		ProductSpecification::loadSpecificationForRecordSetArray($products);
		ProductPrice::loadPricesForRecordSetArray($products);

		$count = new ProductCount($this->productFilter);
		$totalCount = $count->getCategoryProductCount($productFilter);
		$offsetEnd = min($totalCount, $offsetEnd);
		
		$urlParams = array('controller' => 'category', 'action' => 'index', 
						   'id' => $this->request->getValue('id'),
						   'cathandle' => $this->request->getValue('cathandle'),
						   'page' => '_000_',
						   );
						   
		if ($this->request->getValue('filters'))
		{
			$urlParams['filters'] = $this->request->getValue('filters');
		}
		$url = Router::getInstance()->createURL($urlParams);
		$url = str_replace('_000_', '_page_', $url);
				
		$response = new ActionResponse();
		$response->setValue('id', $this->categoryID);
		$response->setValue('url', $url);
		$response->setValue('products', $products);
		$response->setValue('count', $totalCount);
		$response->setValue('offsetStart', $offsetStart);
		$response->setValue('offsetEnd', $offsetEnd);
		$response->setValue('perPage', $perPage);
		$response->setValue('currentPage', $currentPage);
		$response->setValue('category', $this->category->toArray());
		$response->setValue('subCategories', $this->category->getSubCategoryArray(Category::LOAD_REFERENCES));
		$response->setValue('filterChainHandle', $filterChainHandle);
		$response->setValue('currency', $this->request->getValue('currency', $this->store->getDefaultCurrencyCode()));
		return $response;
	}
	
 	/* @todo some defuctoring... */
	protected function boxFilterBlock()
	{
		if ($this->categoryID < 1)
		{
		  	$this->categoryID = 1;
		}
		
		// get current category instance
		$currentCategory = Category::getInstanceByID($this->categoryID, true);	
		
		// get category filter groups
		$filterGroups = $currentCategory->getFilterGroupArray();
		if (!$filterGroups)
		{
		  	return new RawResponse();
		}		
	
		// get counts by filters, categories, etc
		$count = new ProductCount($this->productFilter);
		$filtercount = $count->getCountByFilters();

		// get group filters
		$ids = array();
		foreach ($filterGroups as $group)
		{
		  	$ids[] = $group['ID'];
		}		

		if ($ids)
		{
			$filters = $currentCategory->getFilterSet();

			// sort filters by group
			$sorted = array();
			$filterArray = array();
			foreach ($filters as $filter)
			{
				$array = $filter->toArray();
				$array['count'] = isset($filtercount[$filter->getID()]) ? $filtercount[$filter->getID()] : 0;
				if (!$array['count'])
				{
					continue;
				}

				$specFieldID = $filter instanceof SelectorFilter ? $filter->getSpecField()->getID() : $filter->filterGroup->get()->specField->get()->getID();
				$sorted[$specFieldID][] = $array;
				$filterArray[] = $array;
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

	 	$response = new BlockResponse();
	 	
		if ($this->filters)
	 	{
			$filterArray = array();
			foreach ($this->filters as $filter)
			{
				$filterArray[] = $filter->toArray();
			}		
			
			$response->setValue('filters', $filterArray);	

			// remove already applied value filter groups
			foreach ($filterArray as $key => $filter)
			{
				// selector values
				if (isset($filter['SpecField']))
				{
					foreach ($filterGroups as $groupkey => $group)
					{
						if (isset($group['filters']))
						{
							foreach ($group['filters'] as $k => $flt)
							{
								if ($flt['ID'] == $filter['ID'])
								{
									unset($filterGroups[$groupkey]['filters'][$k]);
								}
							}								
						}
					}	
				}
			 	
				// simple value filter
				elseif (isset($filter['FilterGroup']))
			 	{
					$id = $filter['FilterGroup']['ID'];
	
					foreach ($filterGroups as $k => $group)
					{
						if ($group['ID'] == $id)
					  	{						
						    unset($filterGroups[$k]);
						}
					} 						
				}				
			}
		}

		// remove empty filter groups
		$maxCriteria = $this->config->getValue('MAX_FILTER_CRITERIA_COUNT'); 
		$showAll = $this->request->getValue('showAll');
		
		$router = Router::getInstance();
		$url = $router->createUrlFromRoute($router->getRequestedRoute());
		foreach ($filterGroups as $key => $grp)
		{
			if (empty($grp['filters']) || count($grp['filters']) == 1)
			{
				unset($filterGroups[$key]);
			}
			
			// hide excess criterias (by default only 5 per filter are displayed)
			else if (($showAll != $grp['ID']) && (count($grp['filters']) > $maxCriteria) && ($maxCriteria > 0))
			{
				$chunks = array_chunk($grp['filters'], $maxCriteria);
				$filterGroups[$key]['filters'] = $chunks[0];
				$filterGroups[$key]['more'] = Router::setUrlQueryParam($url, 'showAll', $grp['ID']);
			}
		}			
    
        // filter by manufacturers
        $manFilters = array();
        foreach ($count->getCountByManufacturers() as $filterData)
        {
            $mFilter = new ManufacturerFilter($filterData['ID'], $filterData['name']);
            $manFilter = $mFilter->toArray();
            $manFilter['count'] = $filterData['cnt'];
            $manFilters[] = $manFilter;
        }
        
        if (count($manFilters) > $maxCriteria && $showAll != 'brand' && $maxCriteria > 0)
        {
			$chunks = array_chunk($manFilters, $maxCriteria);
			$manFilters = $chunks[0];
			$response->setValue('allManufacturers', Router::setUrlQueryParam($url, 'showAll', 'brand'));		  	
		}
        
        if (count($manFilters) > 1)
        {
    	 	$response->setValue('manGroup', array('filters' => $manFilters));
        }
        
        // filter by prices
        $priceFilters = array();
        foreach ($count->getCountByPrices() as $filterId => $count)
        {
            $pFilter = new PriceFilter($filterId);    
            $priceFilter = $pFilter->toArray();
            $priceFilter['count'] = $count;
            $priceFilters[] = $priceFilter;
        }
        
        if (count($priceFilters) > 1)
        {
    	 	$response->setValue('priceGroup', array('filters' => $priceFilters));
        }

	 	$response->setValue('category', $currentCategory->toArray());
	 	$response->setValue('groups', $filterGroups);
	 	
		return $response;	 	
	}	
	
	public function getAppliedFilters()
	{
		if ($this->request->getValue('filters'))
		{
			$valueFilterIds = array();
			$selectorFilterIds = array();
			$manufacturerFilterIds = array();
			$priceFilterIds = array();
			
			$filters = explode(',', $this->request->getValue('filters'));
		  	foreach ($filters as $filter)
			{
			  	$pair = explode('-', $filter);
			  	if (count($pair) != 2)
			  	{
				    continue;
				}
				
				if (substr($pair[1], 0, 1) == 'v')
				{
					$selectorFilterIds[] = substr($pair[1], 1);
				}
				else if (substr($pair[1], 0, 1) == 'm')
				{
					$manufacturerFilterIds[] = substr($pair[1], 1);
				}
				else if (substr($pair[1], 0, 1) == 'p')
				{
					$priceFilterIds[] = substr($pair[1], 1);
				}
				else
				{
					$valueFilterIds[] = $pair[1];	
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
					$this->filters[] = new PriceFilter($filterId);
				}                
            }		
		}		
	}
}

?>