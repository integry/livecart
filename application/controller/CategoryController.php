<?php

ClassLoader::import("application.controller.FrontendController");
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.filter.Filter');
ClassLoader::import('application.model.filter.SelectorFilter');
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

		$this->getAppliedFilters();

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
	
		// add filters to breadcrumb
		$params = array('data' => $nodeArray, 'filters' => array());
		foreach ($this->filters as $filter)
		{
			$filter = $filter->toArray();
			$params['filters'][] = $filter;
			$url = smarty_function_categoryUrl($params, false);
			$this->addBreadCrumb($filter['name_lang'], $url);
		}
	
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

		$products = $this->category->getProductsArray($productFilter, array('Manufacturer'));

		// get product specification data
		ProductSpecification::loadSpecificationForRecordSetArray($products);
	
		$count = new ProductCount($this->productFilter);
		$totalCount = $count->getCategoryProductCount($productFilter);
		$offsetEnd = min($totalCount, $offsetEnd);
		
		$urlParams = array('controller' => 'category', 'action' => 'index', 
						   'id' => $this->request->getValue('id'),
						   'cathandle' => $this->request->getValue('cathandle')
						   );
		if ($this->request->getValue('filters'))
		{
			$urlParams['filters'] = $this->request->getValue('filters');
		}
		$url = Router::getInstance()->createURL($urlParams) . '/';
		
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
		foreach ($filterGroups as $key => $grp)
		{
			if (empty($grp['filters']))
			{
				unset($filterGroups[$key]);
			}
		}			

	 	$response->setValue('category', $currentCategory->toArray());		 
	 	$response->setValue('groups', $filterGroups);		 

		return $response;	 	
	}	
	
	private function getAppliedFilters()
	{
		if ($this->request->getValue('filters'))
		{
			$valueFilterIds = array();
			$selectorFilterIds = array();
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
		}		
	}
}

?>