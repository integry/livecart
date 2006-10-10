<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 *
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 * @role admin.store.catalog
 */
class FilterGroupController extends StoreManagementController
{

	public function index()
  {
	}

	public function add()
	{
		$activeLang = "en"; // TODO: read value from config

		$specFieldID = $this->request->getValue("specFieldID");
		$name = $this->request->getValue("name");
		$isEnabled = $this->request->getValue("isEnabled");

		$specField = SpecField::getInstanceByID($specFieldID);

		$filterGroup = FilterGroup::getNewInstance();
		$filterGroup->specField->set($specField);
		$filterGroup->isEnabled->set($isEnabled);
		$filterGroup->lang($activeLang)->name->set($name);
		$filterGroup->save();
	}

	public function remove()
	{

		$recordID = $this->request->getValue("id");
		if (!empty($recordID) && is_numeric($recordID))
		{
			ActiveRecord::deleteByID("FilterGroup", $recordID);
		}
	}

	public function reorder()
  {
	}

	public function form()
  {
	}
}

?>
