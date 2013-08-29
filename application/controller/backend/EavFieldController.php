<?php


/**
 * Custom fields controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 */
class EavFieldController extends EavFieldControllerCommon
{
	public function initialize()
	{
		$this->loadLanguageFile('backend/SpecField');
		return parent::initialize();
	}

	protected function getParent($id)
	{
		return new EavFieldManager($id);
	}

	protected function getFieldClass()
	{
		return 'EavField';
	}

	public function indexAction()
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
	public function itemAction()
	{
		$specFieldList = parent::item()->getValue();

		$specFieldList['categoryID'] = $specFieldList['classID'];

		return new JSONResponse($specFieldList);
	}

	public function updateAction()
	{
		return parent::update();
	}

	public function createAction()
	{
		return parent::create();
	}

	protected function save(EavField $specField)
	{
		if (!is_numeric($this->request->gget('categoryID')))
		{
			$specField->stringIdentifier->set($this->request->gget('categoryID'));
		}
		return parent::save($specField);
	}

	/**
	 * Delete specification field from database
	 *
	 * @return JSONResponse
	 */
	public function deleteAction()
	{
		return parent::delete();
	}

	/**
	 * Sort specification fields
	 *
	 * @return JSONResponse
	 */
	public function sortAction()
	{
		return parent::sort();
	}
}

?>