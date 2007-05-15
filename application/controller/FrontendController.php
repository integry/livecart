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
        
        // variables to append automatically to all URLs
        $autoAppend = array('currency', 'sort');
        foreach ($autoAppend as $key)
        {
            if ($request->isValueSet($key))
            {
                Router::addAutoAppendQueryVariable($key, $request->getValue($key));
            }    
        }
    }
    
    public function init()
	{
	  	$this->setLayout('frontend');
	  	$this->addBlock('CATEGORY_BOX', 'boxCategory', 'block/box/category');
	  	$this->addBlock('BREADCRUMB', 'boxBreadCrumb', 'block/box/breadcrumb');
	  	$this->addBlock('LANGUAGE', 'boxLanguageSelect', 'block/box/language');
	  	$this->addBlock('CURRENCY', 'boxSwitchCurrency', 'block/box/currency');
	  	$this->addBlock('CART', 'boxShoppingCart', 'block/box/shoppingCart');
	  	$this->addBlock('SEARCH', 'boxSearch', 'block/box/search');
	  	$this->addBlock('INFORMATION', 'boxInformationMenu', 'block/box/informationMenu');
	}
	
	protected function getRequestCurrency()
    {
        $instance = Currency::getValidInstanceById($this->request->getValue('currency', $this->store->getDefaultCurrencyCode()));
        return $instance->getID();
    }
	
	protected function addBreadCrumb($title, $url)
	{		
		$this->breadCrumb[] = array('title' => $title, 'url' => $url);
	}	

	protected function boxInformationMenuBlock()
	{	 	
		ClassLoader::import('application.model.staticpage.StaticPage');
        $f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('StaticPage', 'isInformationBox'), true));
		$f->setOrder(new ARFieldHandle('StaticPage', 'position'));
		
        $response = new BlockResponse();        
		$response->setValue('pages', ActiveRecordModel::getRecordSet('StaticPage', $f)->toArray()); 
		return $response; 	
	}

	protected function boxShoppingCartBlock()
	{	 	
		ClassLoader::import('application.model.order.CustomerOrder');
		$response = new BlockResponse();
		$response->setValue('order', CustomerOrder::getInstance()->toArray()); 
		$response->setValue('currency', $this->request->getValue('currency', $this->store->getDefaultCurrencyCode()));
		return $response; 	
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
	
	protected function boxSearchBlock()
	{
		if ($this->categoryID < 1)
		{
		  	$this->categoryID = Category::ROOT_ID;
		}

		$category = Category::getInstanceById($this->categoryID, Category::LOAD_DATA);
		$subCategories = $category->getSubCategorySet();
		
		$search = array();
				
		do
		{
			if (isset($parent))
			{
				$search[] = $parent->toArray();
			}
			else
			{
				$parent = $category;
			}
		
			$parent = $parent->parentNode->get();
		}
		while ($parent && ($parent->getID() > Category::ROOT_ID));
		
		if ($subCategories)
		{
			if ($category->getID() != Category::ROOT_ID)
			{
				$search[] = $category->toArray();				
			}
			
			foreach ($subCategories as $category)
			{
				$search[] = $category->toArray();
			}					
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
			$options[$cat['ID']] = $cat['name_lang'];
		}
		
		ClassLoader::import("framework.request.validator.Form");        
        $form = new Form(new RequestValidator("productSearch", $this->request));
        $form->enableClientSideValidation(false);
        $form->setValue('id', $this->categoryID);
        $form->setValue('q', $this->request->getValue('q'));
        
        $response = new BlockResponse();		
		$response->setValue('categories', $options);
		$response->setValue('form', $form);
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