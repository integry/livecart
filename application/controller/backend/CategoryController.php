<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Category");

/**
 * Product Category controller
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 *
 */
class CategoryController extends StoreManagementController
{
	public function init()
	{
		parent::init();
		$this->removeLayout();
	}

	public function index()
	{
		$response = new ActionResponse();

		$filter = new ARSelectFilter();
		// Removing tree ROOT node from results
		$filter->setCondition(new OperatorCond(new ARFieldHandle("Category", "ID"), Category::ROOT_ID, "<>"));
		$filter->setOrder(new ARFieldHandle("Category", "lft", 'ASC'));
		$categoryList = Category::getRecordSet($filter);
		$response->setValue("categoryList", $categoryList->toArray($this->store->getDefaultLanguageCode()));

        $response->setValue('curLanguageCode', $this->locale->getLocaleCode());

		return $response;
	}

	/**
	 * Displays category form (for creating a new category or modifying an existing one)
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
	 * @return ActionRedirectResponse
	 */
	public function create()
	{
		$parent = Category::getInstanceByID((int)$this->request->getValue("id"));
		
		$categoryNode = Category::getNewInstance($parent);
		
		$categoryNode->setValueByLang("name", $this->store->getDefaultLanguageCode(), $this->translate("_new_category"));
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
	 * @return ActionRedirectResponse
	 */
	public function update()
	{
		$validator = $this->buildValidator();
		if($validator->isValid())
		{
			$categoryNode = ActiveTreeNode::getInstanceByID("Category", $this->request->getValue("id"));
			
			$categoryNode->setFieldValue('handle', $this->request->getValue('handle', ''));
			
			$multilingualFields = array("name", "description", "keywords");
			$categoryNode->setValueArrayByLang($multilingualFields, $this->store->getDefaultLanguageCode(), $this->store->getLanguageArray(true), $this->request);
			$categoryNode->save();
			
			return new JSONResponse(array_merge($categoryNode->toArray(), array('infoMessage' => $this->translate('_succsessfully_saved'))));
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
	 */
	public function remove()
	{
		$targetNode = Category::getInstanceByID((int)$this->request->getValue("id"));
	    
		$status = false;
		try 
		{
		    $status = $targetNode->delete();
		} 
		catch(Exception $e){}
		
		return new JSONResponse($status);
	}

	/**
	 * Reorder category node
	 *
	 */
	public function reorder()
	{
		$targetNode = Category::getInstanceByID((int)$this->request->getValue("id"));
		$parentNode = Category::getInstanceByID((int)$this->request->getValue("parentId"));
		
		$status = false;
		try 
		{
		    $status = $targetNode->moveTo($parentNode);
		} 
		catch(Exception $e){}
		
		return new JSONResponse($status);
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

	public function debug()
	{
		ActiveTreeNode::reindex("Category");
	}
	
	public function countTabsItems() {
	  	ClassLoader::import('application.model.category.*');
	    
	    $category = Category::getInstanceByID((int)$this->request->getValue('id'));
	    return new JSONResponse(array(
	        'tabFilters' => FilterGroup::countItems($category),
	        'tabFields' => SpecField::countItems($category),
	        'tabImages' => CategoryImage::countItems($category),
	    ));
	}
}

?>