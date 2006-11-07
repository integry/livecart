<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.SpecField");

/**
 * Category specification field ("extra field") controller
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 * @role admin.store.category
 */
class SpecFieldController extends StoreManagementController
{
	public function index()
	{
//		$category = Category::getInstanceByID(1);
//		$recordSet = $category->getSpecFieldList();

//		$response = new ActionResponse();
//
//		$configuration = array(
//    		'languages' => array (
//        		'en' => 'English',
//        		'lt' => 'Lithuanian',
//        		'de' => 'German'
//    		),
//
//    		'types' => array(
//        		'numbers' => array(
//    		          array('Selector', 'selector'),
//    		          array('Numbers', 'numbers')
//
//
//		);
//
//
//
//	LiveCart.SpecFieldManager.prototype.types =
//	{
//		numbers:
//		[
//			new Option('Selector', 'selector'),
//			new Option('Numbers', 'numbers')
//		],
//		text:
//		[
//			new Option('Text', 'text'),
//			new Option('Word processer', 'wordProcesser'),
//			new Option('{t _selector}', '_selector'),
//			new Option('Date', 'date')
//		]
//	}
//
//	LiveCart.SpecFieldManager.prototype.messages = { deleteField: 'delete field'	}
//	LiveCart.SpecFieldManager.prototype.selectorValueTypes = ['_selector', 'selector'];
//	LiveCart.SpecFieldManager.prototype.doNotTranslateTheseValueTypes = ['numbers'];
//	LiveCart.SpecFieldManager.prototype.countNewValues = 0;

//		$response->setValue("fieldList", $recordSet);

//Json
		return $response;
	}

	public function add()
	{
//		$this->setLayout("categoryManager");
		$this->removeLayout();
//		$specField = array("name" => $this->request->getValue("name"), "description" => $this->request->getValue("description"));

		$response = new ActionResponse();
//		$response->setValue("specField", $specField);
		return $response;
	}
	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 * @return ActionResponse
	 */
	public function form()
	{
		ClassLoader::import("framework.request.validator.Form");
		$systemLangList = array("lt" => "Lietuvių", "de" => "Deutch");
		$specFieldTypeList = array("1" => "Text Field", "2" => "Checkbox", "3" => "Select field");
		$form = new Form($this->buildValidator());

		if ($this->request->isValueSet("id"))
		{
			ClassLoader::import("application.model.product.SpecField");
			$specField = SpecField::getInstanceByID($this->request->getValue("id"), SpecField::LOAD_DATA);
			$form->setData($specField->toArray());
		}

		$specFieldList = array(array("name" => "test", "description" => "test"), array("name" => "another item", "description" => "one more..."));

		$response = new ActionResponse();

		$response->setValue("specFieldList", $specFieldList);

		$response->setValue("specFieldForm", $form);
		$response->setValue("systemLangList", $systemLangList);
		$response->setValue("typeList", $specFieldTypeList);
		return $response;
	}

	/**
	 * Creates a new or modifies an exisitng specification field (according to a passed parameters)
	 *
	 * @return ActionRedirectResponse Redirects back to a form if validation fails or to a field list
	 */
	public function save()
	{
		$validator = $this->buildValidator();
		$validator->execute();
		if ($validator->hasFailed())
		{
			$validator->saveState();
			return new ActionRedirectResponse("backend.specField", "form");
		}
		else
		{
			if ($this->request->isValueSet("id"))
			{
				$specField = SpecField::getInstanceByID($this->request->getValue("id"));
			}
			else
			{
				$specField = SpecField::getNewInstance();
			}

			$langCode = $this->user->getActiveLang()->getID();
			$category = Category::getInstanceByID($this->request->getValue("categoryID"));

			$specField->lang($langCode)->name->set($form->getFieldValue('name'));
			$specField->lang($langCode)->description->set($form->getFieldValue('description'));
			$specField->category->set($category);
			$specField->type->set($this->request->getValue("type"));
			$specField->dataType->set($this->request->getValue("dataType"));
			$specField->handle->set($this->request->getValue("handle"));
			return new ActionRedirectResponse("backend.specField", "form", array("id" => $this->request->getValue('id')));
		}
	}


	/**
	 * Removes a specification field and returns back to a field list
	 *
	 * @return ActionRedirectResponse
	 */
	public function remove()
	{
		if ($this->request->isValueSet("id"))
		{
			SpecField::deleteByID($this->request->getValue("id"));
		}
		return new ActionRedirectResponse("specField", "index");
	}

	private function buildValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("specField", $this->request);

		$validator->addCheck("name", new IsNotEmptyCheck("You must enter your name"));
		$validator->addCheck("name", new MaxLengthCheck("Field name must not exceed 40 chars", 40));
		$validator->addCheck("type", new IsNotEmptyCheck("You must set a field type"));

		return $validator;
	}
}

?>