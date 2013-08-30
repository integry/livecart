<?php


/**
 * Category specification field ("extra field") controller
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role category
 */
class SpecFieldController extends EavFieldControllerCommon
{
	protected function getParent($id)
	{
		return Category::getInstanceByID($id);
	}

	protected function getFieldClass()
	{
		return 'SpecField';
	}

	public function indexAction()
	{
		return parent::index();
	}

	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 */
	public function itemAction()
	{
		$specFieldList = parent::item()->getValue();

		$specFieldList['categoryID'] = $specFieldList['Category']['ID'];
		unset($specFieldList['Category']);
		unset($specFieldList['SpecFieldGroup']['Category']);

		return new JSONResponse($specFieldList);
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		return parent::update();
	}

	/**
	 * @role update
	 */
	public function createAction()
	{
		return parent::create();
	}

	protected function save(SpecField $specField)
	{
		return parent::save($specField);
	}

	/**
	 * Delete specification field from database
	 *
	 * @role update
	 * @return JSONResponse
	 */
	public function deleteAction()
	{
		return parent::delete();
	}

	/**
	 * Sort specification fields
	 *
	 * @role update
	 * @return JSONResponse
	 */
	public function sortAction()
	{
		return parent::sort();
	}
}

?>