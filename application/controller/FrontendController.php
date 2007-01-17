<?php

ClassLoader::import("application.controller.BaseController");

/**
 * Base class for all front-end related controllers
 *
 * @package application.controller
 */
abstract class FrontendController extends BaseController 
{
	protected $categoryID = 1;
	
	protected $filters = array();
	
	public function init()
	{
	  	$this->setLayout('frontend');
	  	$this->addBlock('CATEGORY_BOX', 'boxCategory', 'block/box/category');
	  	$this->addBlock('FILTER_BOX', 'boxFilter', 'block/box/filter');
	}
	
	protected function boxLoginBlock()
	{
		/* Returning Users: View your order history & information */
	}

	protected function boxViewBasketBlock()
	{
	  	
	}

	protected function boxLanguageSelectBlock()
	{
	  	
	}
	
	protected function boxAppliedFiltersBlock()
	{
	  	
	}

	protected function boxFilterBlock()
	{
		ClassLoader::import('application.model.category.Category');	 
		ClassLoader::import('application.model.category.Filter');
			 		
		if ($this->categoryID < 1)
		{
		  	$this->categoryID = 1;
		}
		
		// get current category instance
		$currentCategory = Category::getInstanceByID($this->categoryID, true);	
		
		// get category filter groups
		$filterGroupSet = $currentCategory->getFilterGroupSet();
		if (!$filterGroupSet || (0 == $filterGroupSet->getTotalRecordCount()))
		{
		  	return new RawResponse();
		}		
		$filterGroups = $filterGroupSet->toArray(true);

		// get group filters
		$ids = array();
		foreach ($filterGroups as $group)
		{
		  	$ids[] = $group['ID'];
		}		

		if ($ids)
		{
			$filterCond = new INCond(new ARFieldHandle('Filter', 'filterGroupID'), $ids);
			$filterFilter = new ARSelectFilter();
			$filterFilter->setCondition($filterCond);
			$filterFilter->setOrder(new ARFieldHandle('Filter', 'filterGroupID'));
			$filterFilter->setOrder(new ARFieldHandle('Filter', 'position'));
			
			$filters = ActiveRecord::getRecordSet('Filter', $filterFilter, true)->toArray(true);
								
			// sort filters by group
			$sorted = array();
			foreach ($filters as $filter)
			{
				$sorted[$filter['FilterGroup']['ID']][] = $filter;  	
			}
			
			// assign sorted filters to group arrays
			foreach ($filterGroups as &$group)
			{
			  	if (isset($sorted[$group['ID']]))
			  	{
				    $group['filters'] = $sorted[$group['ID']];
				}
			}
		}

	 	$response = new BlockResponse();
	 	if ($this->filters)
	 	{
			$filters = $this->filters->toArray(true);
			$response->setValue('filters', $filters);	

			// remove already applied filter groups
			$activeFilterGroups = $filterGroups;
			foreach ($filters as $key => $filter)
			{
			 	$id = $filter['FilterGroup']['ID'];
			 	
				foreach ($filterGroups as $k => &$group)
				{
					if ($group['ID'] == $id)
				  	{						
					    unset($activeFilterGroups[$k]);
					}
				} 	
			}
			$filterGroups = $activeFilterGroups;	
		}

	 	$response->setValue('category', $currentCategory->toArray());		 
	 	$response->setValue('groups', $filterGroups);		 
	 	
		return $response;	 	
	}
	
	protected function boxCategoryBlock()
	{
		ClassLoader::import('application.model.category.Category');
		
		if ($this->categoryID < 1)
		{
		  	$this->categoryID = 1;
		}
		
		// get top categories
		$rootCategory = Category::getInstanceByID(1);
		$topCategories = $rootCategory->getSubcategorySet()->toArray();
		$currentCategory = Category::getInstanceByID($this->categoryID, true);		
		
		// get path of the current category (except for top categories)
		if (1 < $currentCategory->category->get()->getID())
		{
			$path = $currentCategory->getPathNodeSet()->toArray();
			$path[] = $currentCategory->toArray();
	
			$topCategoryId = $path[0]['ID'];
			unset($path[0]);
		} 
		else
		{
		  	$topCategoryId = $this->categoryID;
		}
					
		foreach ($topCategories as &$cat)
		{
		  	if ($topCategoryId == $cat['ID'])
		  	{
			    $current =& $cat;
			}
		}		  

		// get sibling (same-level) categories (except for top categories)
		if (1 < $currentCategory->category->get()->getID())
		{
			$siblings = $currentCategory->getSiblingSet()->toArray();
	
			foreach ($path as &$node)
			{
			  	if ($node['ID'] != $this->categoryID)
			  	{
					$current['subCategories'] = array(0 => &$node);			    
				  	$current =& $node;
				}
				else
				{
					$current['subCategories'] =& $siblings;
					foreach ($current['subCategories'] as &$sib)
					{
					  	if ($sib['ID'] == $this->categoryID)
					  	{
						    $current =& $sib;
						}
					}
				}
			}		  
		}
	
		// get subcategories of the current category (except for the root category)
		if ($this->categoryID > 1)
		{
			$subcategories = $currentCategory->getSubcategorySet()->toArray();
	
			if ($subcategories)
			{
				$current['subCategories'] = $subcategories;
			}									  
		}
										
		$response = new BlockResponse();
		$response->setValue('categories', $topCategories);
		$response->setValue('currentId', $this->categoryID);
		$response->setValue('lang', 'en');
		return $response;
	}
}

?>