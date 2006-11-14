<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Category");

/**
 * Controller for catalog (product category) related actions
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 */
class CategoryController extends StoreManagementController
{
	
	public function index()
	{
		$response = new ActionResponse();
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
		
		if ($this->request->getValue("mode") != "create" && $this->request->isValueSet("id"))
		{
			$category = Category::getInstanceByID($this->request->getValue("id"), Category::LOAD_DATA);
			$form->setData($category->toArray());
		}
		$response->setValue("mode", $this->request->getValue("mode"));

		return $response;
	}
	
	/**
	 * Create a new category
	 * @return ActionRedirectResponse
	 */
	public function create()
	{
		$parentId = $this->request->getValue("id", 0);
		$defaultLang = "en";
		$validator = $this->buildValidator();
		
		if ($validator->isValid())
		{
			$parent = ActiveTreeNode::getInstanceByID("Category", $parentId);
			$categoryNode = ActiveTreeNode::getNewInstance("Category", $parent);
			
			$multilingualFields = array("name", "description", "keywords");
			$langArray = array("en", "lt", "lv");
			$categoryNode->setValueArrayByLang($multilingualFields, $defaultLang, $langArray, $this->request);
			//$categoryNode->setValueByLang("name", $defaultLang, $this->request->getValue("name"));
			//$categoryNode->setValueByLang("description", $defaultLang, $this->request->getValue("description"));
			//$categoryNode->setValueByLang("keywords", $defaultLang, $this->request->getValue("keywords"));
			$categoryNode->isActive->set($this->request->getValue("isActive"));
			
			$categoryNode->save();
		}
		else
		{
			return new ActionRedirectResponse($this->request->getControllerName(), "form");
		}
	}
	
	public function update()
	{
		$validator = $this->buildValidator();
		
		if($validator->isValid())
		{			
			$response = new ActionResponse();
			$categoryNode = ActiveTreeNode::getInstanceByID("Category", $this->request->getValue("id"));
			
			return new ActionRedirectResponse("backend.category", "index");
		}
		else 
		{
			return new ActionRedirectResponse($this->request->getControllerName(), "form");
		}
	}
	
	public function viewTree()
	{
		$rootNode = ActiveTreeNode::getRootNode("Category", true);
		$rootNode->load();
		
		echo "<pre>"; print_r($rootNode->toArray()); echo "</pre>";
	}

	private function buildValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");
		
		$validator = new RequestValidator("category", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("Catgory name should not be empty")));
		return $validator;
	}
	
	private function buildForm()
	{
		$form = new Form($this->buildValidator());
		return $form;
	}
}

?>