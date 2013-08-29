<?php


/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class ProductRelationshipGroupController extends ProductListControllerCommon
{
	protected function getOwnerClassName()
	{
		return 'Product';
	}

	protected function getGroupClassName()
	{
		return 'ProductRelationshipGroup';
	}

	/**
	 * @role update
	 */
	public function createAction()
	{
		$group = ProductRelationshipGroup::getNewInstance($this->getOwnerInstanceByID($this->request->get('ownerID')), $this->request->get('type'));
		return $this->save($group);
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
	public function deleteAction()
	{
		return parent::delete();
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		return parent::sort();
	}

	public function editAction()
	{
		return parent::edit();
	}
}

?>