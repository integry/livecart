<?php


/**
 * Category specification field value controller
 *
 * @package application.controller.abstract.eav
 * @author	Integry Systems
 */
abstract class EavFieldValueControllerCommon extends StoreManagementController
{
	protected abstract function getClassName();

	/**
	 * Delete field value from database
	 *
	 * @return JSONResponse Indicates status
	 */
	public function deleteAction()
	{
		if($id = $this->request->gget('id', false))
		{
			ActiveRecordModel::deleteById($this->getClassName(), $id);
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
	 * return JSONResponse Indicates status
	 */
	public function sortAction()
	{
		foreach($this->request->gget($this->request->gget('target'), array()) as $position => $key)
		{
			// Except new fields, because they are not yet in database
			if(!empty($key) && !preg_match('/^new/', $key))
			{
				$specField = $this->getInstanceByID($key);
				$specField->setFieldValue('position', $position);
				$specField->save();
			}
		}

		return new JSONResponse(false, 'success');
	}

	public function mergeValuesAction()
	{
		$mergedIntoValue = $this->getInstanceByID($this->request->gget('mergeIntoValue'), true);

		foreach($this->request->gget('mergedValues') as $mergedValueId)
		{
			$mergedValue = $this->getInstanceByID($mergedValueId, true);
			$mergedIntoValue->mergeWith($mergedValue);
		}

		$mergedIntoValue->save();
		return new JSONResponse(array('status' => 'success'));
	}

	private function getInstanceByID($id)
	{
		return call_user_func_array(array($this->getClassName(), 'getInstanceById'), array($id, ActiveRecordModel::LOAD_DATA));
	}
}

?>