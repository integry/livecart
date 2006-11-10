<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Filter");
/**
 * ...
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 * 
 * @role admin.store.catalog
 */
class FilterController extends StoreManagementController
{
	public function index()
	{
		$filterList = ActiveRecordModel::getRecordSetArray("Filter", new ARSelectFilter());
		echo "<pre>"; print_r($filterList); echo "</pre>";
	}
	
	public function create()
	{
		$filter = ActiveRecordModel::getNewInstance("Filter");
		srand();
		$filter->setValueByLang("name", "en", "This is my test filter " . rand());
		$filter->rangeStart->set(rand());
		$filter->rangeEnd->set(rand());
		$filter->save();
	}
}

?>