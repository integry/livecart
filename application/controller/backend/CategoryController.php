<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Category");

/**
 * Product Category controller
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 *
 * @role product
 */
class CategoryController extends StoreManagementController
{
	public function index()
	{
		$response = new ActionResponse();
	    
		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());
		
		$languages = array();
		foreach($this->store->getLanguageList()->toArray() as $language) $languages[$language['ID']] = $language;
		$response->setValue('languages', $languages);
		
		$response->setValue('categoryList', $categoryList->toArray($this->store->getDefaultLanguageCode()));
        $response->setValue('curLanguageCode', $this->locale->getLocaleCode());
        
		return $response;
	}

	/**
	 * Displays category form (for creating a new category or modifying an existing one)
	 *
	 * @role !category
	 * 
	 * @return ActionResponse
	 */
	public function form()
	{
		ClassLoader::import("framework.request.validator.Form");

		$response = new ActionResponse();
		$form = $this->buildForm();
		$response->setValue("catalogForm", $form);

		$category = Category::getInstanceByID($this->request->getValue("id"), Category::LOAD_DATA);
		$categoryArr = $category->toArray();
		$form->setData($categoryArr);

		$languages = array();
		foreach ($this->store->getLanguageArray() as $lang)
		{
			$languages[$lang] = $this->locale->info()->getOriginalLanguageName($lang);
		}

		$response->setValue("categoryId", $categoryArr['ID']);
		$response->setValue("languageList", $languages);

		return $response;
	}

	/**
	 * Creates a new category record
	 *
	 * @role !category.create
	 * 
	 * @return ActionRedirectResponse
	 */
	public function create()
	{
		$parent = Category::getInstanceByID((int)$this->request->getValue("id"));
		
		$categoryNode = Category::getNewInstance($parent);
		$categoryNode->setValueByLang("name", $this->store->getDefaultLanguageCode(), 'dump' );
		$categoryNode->save();
		
		$categoryNode->setValueByLang("name", $this->store->getDefaultLanguageCode(), $this->translate("_new_category") . " " . $categoryNode->getID() );
		$categoryNode->setFieldValue("handle", "new.category." . $categoryNode->getID() );
        $categoryNode->save();

		try 
		{
			return new JSONResponse($categoryNode->toArray());
		}
		catch(Exception $e)
		{
		    return new JSONResponse(false);
		}
	}

	/**
	 * Updates a category record
	 * 
	 * @role !category.update
	 *
	 * @return ActionRedirectResponse
	 */
	public function update()
	{
	    $validator = $this->buildValidator();
		if($validator->isValid())
		{
			$categoryNode = Category::getInstanceByID($this->request->getValue("id"), Category::LOAD_DATA);
			$categoryNode->setFieldValue('isEnabled', $this->request->getValue('isEnabled', 0));
			$categoryNode->setFieldValue('handle', $this->request->getValue('handle', ''));
			
			$multilingualFields = array("name", "description", "keywords");
			$categoryNode->setValueArrayByLang($multilingualFields, $this->store->getDefaultLanguageCode(), $this->store->getLanguageArray(true), $this->request);
			$categoryNode->save();
			
			return new JSONResponse(array_merge($categoryNode->toFlatArray(), array('infoMessage' => $this->translate('_succsessfully_saved'))));
		}
	}

	/**
	 * Debug method: outputs category tree structure
	 *
	 */
	public function viewTree()
	{
		$rootNode = ActiveTreeNode::getRootNode("Category");

		$recordSet = $rootNode->getChildNodes(false, true);
		echo "<pre>"; print_r($recordSet->toArray()); echo "</pre>";
	}

	/**
	 * Removes node from a category
	 *
	 * @role !category.remove
	 */
	public function remove()
	{
		$status = false;
		
		Category::getInstanceByID((int)$this->request->getValue("id"))->delete();
		
		return new JSONResponse($status);
	}

	/**
	 * Reorder category node
	 *
	 * @role !category.sort
	 */
	public function reorder()
	{
	    $targetNode = Category::getInstanceByID((int)$this->request->getValue("id"));
		$parentNode = Category::getInstanceByID((int)$this->request->getValue("parentId"));
		
		$status = true;
		try
		{
			if($direction = $this->request->getValue("direction", false))
			{
			    if(ActiveTreeNode::DIRECTION_LEFT == $direction) $targetNode->moveLeft(false);
			    if(ActiveTreeNode::DIRECTION_RIGHT == $direction) $targetNode->moveRight(false);
			}
			else
			{
			    $targetNode->moveTo($parentNode);
			}
		}
		catch(Exception $e)
	    {
		    $status = false;
		}
		
		return new JSONResponse($status);
	}

	public function countTabsItems() {
	  	ClassLoader::import('application.model.category.*');
	  	ClassLoader::import('application.model.filter.*');
	  	ClassLoader::import('application.model.product.*');
	    
	    $category = Category::getInstanceByID((int)$this->request->getValue('id'), Category::LOAD_DATA);
	    return new JSONResponse(array(
	        'tabProducts' => $category->totalProductCount->get(),
	        'tabFilters' => FilterGroup::countItems($category),
	        'tabFields' => SpecField::countItems($category),
	        'tabImages' => CategoryImage::countItems($category),
	    ));
	}
	
	public function xmlBranch() 
	{
	    $xmlResponse = new XMLResponse();
	    $rootID = (int)$this->request->getValue("id");

	    if(!in_array($rootID, array(Category::ROOT_ID, 0))) 
	    {
	       $category = Category::getInstanceByID($rootID);
		   $xmlResponse->setValue("rootID", $rootID);
           $xmlResponse->setValue("categoryList", $category->getChildNodes(false, true)->toArray($this->store->getDefaultLanguageCode()));
	    }
	    
	    return $xmlResponse;
	}

	public function xmlRecursivePath() 
	{
	    $xmlResponse = new XMLResponse();
	    $targetID = (int)$this->request->getValue("id");
	    
	    try 
	    {
    	    $categoriesList = Category::getInstanceByID($targetID)->getPathBranchesArray();
    	    if(count($categoriesList) > 0 && isset($categoriesList['children'][0]['parent'])) 
    	    {
        	    $xmlResponse->setValue("rootID", $categoriesList['children'][0]['parent']);
        	    $xmlResponse->setValue("categoryList", $categoriesList);
    	    }
	    }
	    catch(Exception $e) 
	    {
	    }
	    
	    $xmlResponse->setValue("targetID", $targetID);
	    
	    return $xmlResponse;
	}
	
	public function debug()
	{
		ActiveTreeNode::reindex("Category");
	}
	
	/**
	 * Builds a category form validator
	 *
	 * @return RequestValidator
	 */
	private function buildValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		$validator = new RequestValidator("category", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("Catgory name should not be empty")));
		return $validator;
	}

	/**
	 * Builds a category form instance
	 *
	 * @return Form
	 */
	private function buildForm()
	{
		$form = new Form($this->buildValidator());
		return $form;
	}
}

?>