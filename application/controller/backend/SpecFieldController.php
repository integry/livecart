<?php


/**
 * Category specification field ("extra field") controller
 *
 * @package application.controller.backend
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

	public function index()
	{
		return parent::index();
	}

	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 * @return ActionResponse
	 */
	public function item()
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