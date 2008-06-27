<?php

ClassLoader::import('application.controller.backend.abstract.eav.EavFieldValueControllerCommon');
ClassLoader::import('application.model.eav.EavValue');

/**
 * Category specification field value controller
 *
 * @package application.controller.backend
 * @author	Integry Systems
 * @role category
 */
class EavFieldValueController extends EavFieldValueControllerCommon
{
	protected function getClassName()
	{
		return 'EavValue';
	}

	/**
	 * Delete specification field value from database
	 *
	 * @role update
	 * @return JSONResponse Indicates status
	 */
	public function delete()
	{
		return parent::delete();
	}

	/**
	 * Sort specification field values
	 *
	 * @role update
	 * return JSONResponse Indicates status
	 */
	public function sort()
	{
		return parent::sort();
	}

	/**
	 * @role update
	 */
	public function mergeValues()
	{
		return parent::mergeValues();
	}
}