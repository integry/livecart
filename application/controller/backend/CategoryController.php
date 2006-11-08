<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

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
	
	public function form()
	{		
		ClassLoader::import("framework.request.validator.Form");

		$response = new ActionResponse();
		$response->setValue("catalogForm", $this->createCatalogForm());
		$response->setValue("ID", $this->request->getValue("id"));
		return $response;
	}
	
	/**
	 * Add catalog form
	 */
	public function add()
	{		
		$response = new ActionResponse();
		$response->setValue("catalogForm", $this->createCatalogForm());
		
		return $response;
	}
	
	public function update()
	{
		if($id = $this->request->getValue('id', false))
		{			
			$response = new ActionResponse();
			$response->setValue("catalogForm", $this->createCatalogForm());
			$response->setValue('id', $id);
			
			return $response;
		}
		else 
		{
			return new ActionRedirectResponse($this->request->getControllerName(), "index");
		}
	}

	
	/**
	 * Creates form object and defines validation rules
	 * 
	 * @return Form
	 */
	private function createCatalogForm()
	{
		ClassLoader::import("framework.request.validator.*");
		
		$validator = new RequestValidator("catalogForm", $this->request);
		$validator->addCheck("name", new MinLengthCheck($this->translate("Name must be at least two chars length"), 2));
		
		$form = new Form($validator);
		
		if ($this->request->isValueSet("id"))
		{
			ClassLoader::import("application.model.category.Category");
			ClassLoader::import("application.model.category.CategoryLangData");
			$category = ActiveRecord::getInstanceById('CategoryLangData', array('categoryID' => $this->request->getValue("id"), 'languageID' => 'en'), true);
			$data = $category->toArray();
				
			$form->setData(array(
				'name' => $data['name'],
				'description' => $data['description']
			));
		}
		else if($this->request->isValueSet('parent')) 
		{
			$form->setValue('parent', $this->request->getValue('parent'));
		}
	
		return $form; 
	}

	public function fields()
	{
		$response = new ActionResponse();
		$response->setValue("action", "fields");
		return $response;
	}

	public function filters()
	{
		$response = new ActionResponse();
		$response->setValue("action", "filters");
		return $response;
	}
}

?>