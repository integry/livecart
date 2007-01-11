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

		if ($this->categoryID < 1)
		{
		  	$this->categoryID = 1;
		}
		
		// get current category instance
		$currentCategory = Category::getInstanceByID($this->categoryID, true);	
		
		$filterSet = $currentCategory->getFilterGroupSet();
		if (!$filterSet)
		{
		  	return new RawResponse();
		}
		
		$filters = $filterSet->toArray();
		
		print_r($filters);
		
	 	$response = new BlockResponse();
		 
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