<?php


/**
 * Product lists
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class ProductListController extends ProductListControllerCommon
{
	public function indexAction()
	{
		$categoryID = (int)$this->request->get('id');
		$category = Category::getInstanceByID($categoryID, ActiveRecord::LOAD_DATA);

		// get lists
		$f = new ARSelectFilter();
		$f->orderBy(new ARFieldHandle('ProductList', 'position'));
		$lists = $category->getRelatedRecordSetArray('ProductList', $f);

		$ids = array();
		foreach ($lists as $list)
		{
			$ids[] = $list['ID'];
		}

		// get list items
		$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductListItem', 'productListID'), $ids));
		$f->orderBy(new ARFieldHandle('ProductList', 'position'));
		$f->orderBy(new ARFieldHandle('ProductListItem', 'productListID'));
		$f->orderBy(new ARFieldHandle('ProductListItem', 'position'));
		$items = ActiveRecordModel::getRecordSetArray('ProductListItem', $f, array('ProductList', 'Product', 'ProductImage'));

		$items = ActiveRecordGroup::mergeGroupsWithFields('ProductList', $lists, $items);


		$this->set('ownerID', $categoryID);
		$this->set('items', $items);
	}

	protected function getOwnerClassName()
	{
		return 'Category';
	}

	protected function getGroupClassName()
	{
		return 'ProductList';
	}

	/**
	 * @role update
	 */
	public function createAction()
	{
		return parent::create();
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