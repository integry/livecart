<?php

ClassLoader::import("application.controller.BaseController");

/**
 * Base class for all front-end related controllers
 *
 * @package application.controller
 */
abstract class FrontendController extends BaseController 
{	
	public function init()
	{
	  	$this->setLayout('frontend');
	  	$this->addBlock('CATEGORY_BOX', 'boxCategory', 'block/box/category');
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
		
		print_r($currentCategory->category);
		
		// get path of the current category (except for top categories)
		if (1 < $currentCategory->category->get()->getID())
		{
			$path = $currentCategory->getPathNodeSet(false)->toArray();
//			$path[] = $currentCategory->toArray();

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
		if ($currentCategory->category->get()->getID() > 1)
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
						

		/* @todo get rid of this line (needed to preload all related records) */
		$filterGroupSet = $currentCategory->getFilterGroupSet();
		
		// apply current filters to suitable categories
		if ($this->filters)
		{
			$filterArray = $this->filters->toArray(true, true);
			$rootFilters = array();
			foreach ($filterArray as $filter)
			{
			  	if (Category::ROOT_ID == $filter['FilterGroup']['SpecField']['Category']['ID'])
			  	{
					$rootFilters[$filter['ID']] = true;
				}
			}
			$this->applyFilters($topCategories, $filterArray, $rootFilters);		  
		}

		$response = new BlockResponse();
		$response->setValue('categories', $topCategories);
		$response->setValue('currentId', $this->categoryID);
		$response->setValue('lang', 'en');
		return $response;
	}
	
	/**
	 *  Recursively applies all selected filters to applicable categories
	 */
	private function applyFilters(&$categories, $filters, $parentFilterIds)
	{
		foreach ($categories as &$category)
		{
		  	$categoryFilters = $parentFilterIds;
			foreach ($filters as $filter)
		  	{
			    if ($filter['FilterGroup']['SpecField']['Category']['ID'] == $category['ID'])
			    {
					$categoryFilters[$filter['ID']] = true;
				}

				if (isset($categoryFilters[$filter['ID']]))
				{
					$category['filters'][] = $filter;
				}
			}
			
			if (isset($category['subCategories']))
			{
			  	$this->applyFilters($category['subCategories'], $filters, $categoryFilters);
			}
		}  	
	}

}

?>