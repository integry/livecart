<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * ...
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 * @role admin.store.catalog
 */
class FilterController extends StoreManagementController
{
	public function form()
	{
		$formObj = $this->createForm();

		$response = new ActionResponse();
		$response->setValue("form", $formObj);
		return $response;
	}

  /**
   * @todo Get active lang code from user config
   */
	public function create()
	{
		$activeLang = "en";

		$filterGroupID = $this->request->getValue("filterGroupID");
		$filterName = $this->request->getValue("name");
		$filterType = $this->request->getValue("type");
		$rangeStart = $this->request->getValue("rangeStart");
		$rangeEnd = $this->request->getValue("rangeEnd");

		$filterGroup = FilterGroup::getInstanceByID($filterGroupID);

		$filter = Filter::getNewInstance("Filter");
		$filter->type->set($filterType);
		$filter->rangeStart->set($rangeStart);
		$filter->rangeEnd->set($rangeEnd);
		$filter->lang($activeLang)->name->set($filterName);

		$filterGroup->addFilter($filter);

		//return new ActionRedirectResponse("");
	}

	public function reorder()
  {
	}

	public function remove()
	{
		$filterID = $this->request->getValue("id");

		if (is_numeric($filterID))
		{
			ActiveRecord::deleteByID("Filter", $filterID);
		}
		return new ActionRedirectResponse("backend.filter", "index");
	}

	private function createForm()
	{
		ClassLoader::import("application.model.LangForm");
		ClassLoader::import("application.model.Store");

		$store = Store::getInstance();
		$langList = $store->getLanguageList();

		$form = new LangForm("filterFrm", null, $activeUserLang = $this->user->getActiveLang(), $this->user->getDefaultLang(), $langList);
		$form->setAction(Router::createURL(array("controller" => "backend.filter", "action" => "save")));

		$nameField = new TextlineField("name", "Filter name");
		$nameField->addCheck(new RequiredValueCheck("You must enter a filter name"));

		$rangeStart = new TextlineField("rangeStart", "Range start");
		$rangeEnd = new TextlineField("rangeEnd", "Range end");

		$idField = new HiddenField("id", "filter ID");
		$submitButton = new submitField("submit", "Save");

		$form->addLangField($nameField);
		$form->addField($rangeStart);
		$form->addField($rangeEnd);
		$form->addField($submitButton);
		$form->addField($idField);

		return $form;
	}
}

?>
