<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.product.SpecField");

/**
 * Catalog specification field ("extra field") controller
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 * @role admin.store.catalog
 */
class SpecFieldController extends StoreManagementController
{
	public function index()
	{
		$catalog = Catalog::getInstanceByID(1);
		$recordSet = $catalog->getSpecFieldList();

		$response = new ActionResponse();
		$response->setValue("fieldList", $recordSet);
		return $response;
	}

	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 * @return ActionResponse
	 */
	public function form()
	{
		/*
		$form = $this->createForm();

		if ($this->request->isValueSet("id"))
		{
			ClassLoader::import("application.model.product.SpecField");
			$specField = SpecField::getInstanceByID($this->request->getValue("id"), SpecField::LOAD_DATA);
			$form->setData($specField->toArray());
		}

		if ($form->validationFailed())
		{
			$form->restore();
		}

		$response = new ActionResponse();
		$response->setValue("form", $form);
		return $response;
		*/
		ClassLoader::import("framework.request.validator.*");
		$form = new Form($this->buildValidator());
		$systemLangList = array("lt" => "Lietuvių", "de" => "Deutch");
		
		$response = new ActionResponse();
		$response->setValue("specFieldForm", $form);
		return $response;
	}

	/**
	 * Creates a new or modifies an exisitng specification field (according to a passed parameters)
	 *
	 * @return ActionRedirectResponse Redirects back to a form if validation fails or to a field list
	 */
	public function save()
	{
		echo"<pre>";
		print_r($this->request->toArray());
		echo"</pre>";

		$form = $this->createForm();
		$form->setData($this->request->toArray());

		if ($form->isValid())
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
			$catalog = Catalog::getInstanceByID($this->request->getValue("catalogID"));

			$specField->lang($langCode)->name->set($form->getFieldValue('name'));
			$specField->lang($langCode)->description->set($form->getFieldValue('description'));
			$specField->catalog->set($catalog);
			$specField->type->set($this->request->getValue("type"));
			$specField->dataType->set($this->request->getValue("dataType"));
			$specField->handle->set($this->request->getValue("handle"));

			echo"<pre>";
			print_r($specField);
			echo"</pre>";
		}
		else
		{
			echo"invalid form";
			print_r($form->getErrorList());
			$form->saveState();
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

	/**
	 * Creates LangForm instance for specification field data
	 *
	 * @return LangForm
	 */
	/*
	private function createForm()
	{
		ClassLoader::import("application.model.LangForm");
		ClassLoader::import("application.model.Store");

		$store = Store::getInstance();
		$langList = $store->getLanguageList();

		$form = new LangForm("specFieldFrm", null, $langList);
		$router = Router::getInstance();
		$form->setAction($router->createURL(array("controller" => "backend.specField", "action" => "save")));

		$nameField = new TextlineField("name", "Field name");
		$nameField->addCheck(new RequiredValueCheck("You must enter field name"));

		$descriptionField = new TextareaField("description", "Field description");

		$typeField = new SelectField("type", "Field type");
		$typeField->addValue(1, "Text field");
		$typeField->addValue(2, "Drop down option list");
		$typeField->addValue(3, "Multiple choices");
		$typeField->setValue(1);

		$dataTypeField = new SelectField("dataType");
		$dataTypeField->addValue(1, "Any");
		$dataTypeField->addValue(2, "Numeric");

		$idField = new HiddenField("id", "spec field ID");

		$handleField = new TextlineField("handle", "Field handle");
		$submitButton = new submitField("submit", "Save");

		$form->addLangField($nameField);
		$form->addLangField($descriptionField);
		$form->addField($typeField);
		$form->addField($dataTypeField);
		$form->addField($handleField);
		$form->addField($submitButton);
		$form->addField($idField);

		return $form;
	}
	*/

	public function buildValidator()
	{
		$validator = new RequestValidator("specField", $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck("You must enter your name"));
		
		return $validator;
	}
}

?>
