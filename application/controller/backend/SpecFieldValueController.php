<?php


/**
 * Category specification field value controller
 *
 * @package application/controller/backend
 * @author	Integry Systems
 * @role category
 */
class SpecFieldValueController extends EavFieldValueControllerCommon
{
	protected function getClassName()
	{
		return 'SpecFieldValue';
	}

	/**
	 * Delete specification field value from database
	 *
	 * @role update
	 * @return JSONResponse Indicates status
	 */
	public function deleteAction()
	{
		return parent::delete();
	}

	/**
	 * Sort specification field values
	 *
	 * @role update
	 * return JSONResponse Indicates status
	 */
	public function sortAction()
	{
		return parent::sort();
	}

	/**
	 * @role update
	 */
	public function mergeValuesAction()
	{
		return parent::mergeValues();
	}
}