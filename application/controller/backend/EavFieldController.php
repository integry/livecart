<?php

ClassLoader::import('application.controller.backend.abstract.eav.EavFieldControllerCommon');
ClassLoader::import('application.model.eav.EavField');
ClassLoader::import('application.model.eav.EavFieldManager');

/**
 * Custom fields controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 */
class EavFieldController extends EavFieldControllerCommon
{
	public function init()
	{
		$this->loadLanguageFile('backend/SpecField');
		return parent::init();
	}

	protected function getParent($id)
	{
		return new EavFieldManager($id);
	}

	protected function getFieldClass()
	{
		return 'EavField';
	}

	public function index()
	{
		$response = parent::index();

		$fields = $response->get('specFieldsWithGroups');
		foreach ($fields as &$field)
		{
			if (isset($field['EavFieldGroup']))
			{
				$field['SpecFieldGroup'] = $field['EavFieldGroup'];
				$field['SpecFieldGroup']['Category']['ID'] = $response->get('categoryID');
			}
		}
		$response->set('specFieldsWithGroups', $fields);

		return $response;
	}

	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 * @return ActionResponse
	 */
	public function item()
	{
		$specFieldList = parent::item()->getValue();

		$specFieldList['categoryID'] = $specFieldList['classID'];

		return new JSONResponse($specFieldList);
	}

	/**
	 * @role update
	 */
	public function update()
	{
		return parent::update();
	}

	/**
	 * @role update
	 */
	public function create()
	{
		return parent::create();
	}

	protected function save(EavField $specField)
	{
		if (!is_numeric($this->request->get('categoryID')))
		{
			$specField->stringIdentifier->set($this->request->get('categoryID'));
		}
		return parent::save($specField);
	}

	/**
	 * Delete specification field from database
	 *
	 * @role update
	 * @return JSONResponse
	 */
	public function delete()
	{
		return parent::delete();
	}

	/**
	 * Sort specification fields
	 *
	 * @role update
	 * @return JSONResponse
	 */
	public function sort()
	{
		return parent::sort();
	}
}

?>