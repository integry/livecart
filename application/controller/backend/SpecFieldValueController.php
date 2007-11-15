<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("library.*");

/**
 * Category specification field value controller
 *
 * @package application.controller.backend
 * @author	Integry Systems
 * @role category
 */
class SpecFieldValueController extends StoreManagementController
{  
	/**
	 * Delete specification field value from database
	 * 
	 * @role update
	 * @return JSONResponse Indicates status
	 */
	public function delete()
	{
		if($id = $this->request->get("id", false))
		{
			SpecFieldValue::deleteById($id);
			return new JSONResponse(false, 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure');
		}
	}
 
	/**
	 * Sort specification field values
	 * 
	 * @role update
	 * return JSONResponse Indicates status
	 */
	public function sort()
	{
		foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
		{
			// Except new fields, because they are not yet in database
			if(!empty($key) && !preg_match('/^new/', $key))
			{
				$specField = SpecFieldValue::getInstanceByID((int)$key);
				$specField->setFieldValue('position', (int)$position);
				$specField->save();
			}
		}

		return new JSONResponse(false, 'success');
	}
	
	/**
	 * @role update
	 */
	public function mergeValues()
	{
		$mergedIntoValue = SpecFieldValue::getInstanceByID((int)$this->request->get('mergeIntoValue'), true);
		
		foreach($this->request->get('mergedValues') as $mergedValueId)
		{
			$mergedValue = SpecFieldValue::getInstanceByID((int)$mergedValueId, true);
			$mergedIntoValue->mergeWith($mergedValue);
		}

		$mergedIntoValue->save();
		return new JSONResponse(array('status' => 'success'));
	}
}
