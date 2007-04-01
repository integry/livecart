<?php

ClassLoader::import("application.controller.BaseController");

/**
 * Base class for all front-end related controllers
 *
 * @package application.controller
 */
abstract class FrontendController extends BaseController 
{	
	protected $breadCrumb = array();
	
	public function __construct($request)
	{
        parent::__construct($request);
        if ($request->isValueSet('currency'))
        {
            Router::addAutoAppendQueryVariable('currency', $request->getValue('currency'));
        }    
        if ($request->isValueSet('showAll'))
        {
            Router::addAutoAppendQueryVariable('showAll', $request->getValue('showAll'));
        }    
    }
    
    public function init()
	{
	  	$this->setLayout('frontend');
	  	$this->addBlock('CATEGORY_BOX', 'boxCategory', 'block/box/category');
	  	$this->addBlock('BREADCRUMB', 'boxBreadCrumb', 'block/box/breadcrumb');
	  	$this->addBlock('LANGUAGE', 'boxLanguageSelect', 'block/box/language');
	  	$this->addBlock('CURRENCY', 'boxSwitchCurrency', 'block/box/currency');
	}
	
	protected function addBreadCrumb($title, $url)
	{
		$this->breadCrumb[] = array('title' => $title, 'url' => $url);
	}	

	protected function boxLoginBlock()
	{
		/* Returning Users: View your order history & information */
	}

	protected function boxViewBasketBlock()
	{
	  	
	}

	protected function boxSwitchCurrencyBlock()
	{
        $router = Router::getInstance();
        $returnRoute = $router->getRequestedRoute();
		$returnRoute = $router->createUrlFromRoute($returnRoute);		
		$returnRoute = Router::setUrlQueryParam($returnRoute, 'currency', '_curr_');

        $currencies = Store::getInstance()->getCurrencySet();        
        $currencyArray = array();
        foreach ($currencies as $currency)
        {
            $currencyArray[$currency->getID()] = $currency->toArray();
            $currencyArray[$currency->getID()]['url'] = str_replace('_curr_', $currency->getID(), $returnRoute);            
        }
        
        $response = new BlockResponse();			  	        
        $response->setValue('currencies', $currencyArray);        
        return $response;	  	
	}

	protected function boxLanguageSelectBlock()
	{
        $response = new BlockResponse();			  	
        $languages = Store::getInstance()->getLanguageList()->toArray();
        $current = Store::getInstance()->getLocaleCode();
        
        $router = Router::getInstance();
        $returnRoute = $router->getRequestedRoute();
        
    	if ('/' == substr($returnRoute, 2, 1))
    	{
    	  	$returnRoute = substr($returnRoute, 3);
    	}
        
        foreach ($languages as $key => $lang)
        {
            if ($lang['ID'] == $current)
            {
                unset($languages[$key]);
            }   
            else
            {
                $languages[$key]['url'] = $router->createUrlFromRoute($lang['ID'] . '/' . $returnRoute);
            }
        }

        $response->setValue('languages', $languages);
        return $response;
	}
	
	protected function boxBreadCrumbBlock()
	{
		array_unshift($this->breadCrumb, array('title' => $this->config->getValue('STORE_NAME'), 
											   'url' => Router::getInstance()->createUrl(array('controller' => 'index', 'action' => 'index'))));
		$response = new BlockResponse();
		$response->setValue('breadCrumb', $this->breadCrumb);
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
		$topCategories = $rootCategory->getSubcategoryArray();
		$currentCategory = Category::getInstanceByID($this->categoryID, Category::LOAD_DATA);	
		
		// get path of the current category (except for top categories)
		if (!(1 == $currentCategory->getID()) && (1 < $currentCategory->parentNode->get()->getID()))
		{
			$path = $currentCategory->getPathNodeSet(false)->toArray();

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
		if (!(1 == $currentCategory->getID()) && (1 < $currentCategory->parentNode->get()->getID()))
		{
			$siblings = $currentCategory->getSiblingArray();
		
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
						
		// apply current filters to suitable categories
		if ($this->filters)
		{
			//$filterArray = $this->filters->toArray();

			$rootFilters = array();
			foreach ($this->filters as $filter)
			{
				if (!($filter instanceof SpecificationFilterInterface) || 
                    Category::ROOT_ID == $filter->getSpecField()->category->get()->getID())
			  	{
					$rootFilters[$filter->getID()] = true;
				}
			}
			$this->applyFilters($topCategories, $rootFilters);		  
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
	private function applyFilters(&$categories, $parentFilterIds)
	{
		foreach ($categories as &$category)
		{
		  	$categoryFilters = $parentFilterIds;
			foreach ($this->filters as $filter)
		  	{
			    if (!($filter instanceof SpecificationFilterInterface) || 
                    $filter->getSpecField()->category->get()->getID() == $category['ID'])
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

}

?>