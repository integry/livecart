<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.product.Manufacturer");

/**
 * 
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class ManufacturerController extends StoreManagementController
{
	public function autoComplete()
	{	  	
	  	$f = new ARSelectFilter();
	  	$c = new LikeCond(new ARFieldHandle('Manufacturer', 'name'), $this->request->get('manufacturer') . '%');
	  	$f->setCondition($c);
	  	
	  	$results = ActiveRecordModel::getRecordSetArray('Manufacturer', $f);
	  	
		$resp = array();
	  	foreach ($results as $value)
	  	{
			$resp[$value['ID']] = $value['name'];
		}	  	
	  	
		return new AutoCompleteResponse($resp);
	}
}

?>