<?php

ClassLoader::import("application.controller.backend.abstract.ProductListControllerCommon");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.category.ProductList");
ClassLoader::import("application.model.category.ProductListItem");

/**
 * Product lists
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class ProductListController extends ProductListControllerCommon
{
	public function index()
	{
		$categoryID = (int)$this->request->get('id');
		$category = Category::getInstanceByID($categoryID, ActiveRecord::LOAD_DATA);

		// get lists
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('ProductList', 'position'));
		$lists = $category->getRelatedRecordSetArray('ProductList', $f);

		$ids = array();
		foreach ($lists as $list)
		{
			$ids[] = $list['ID'];
		}

		// get list items
		$f = new ARSelectFilter(new INCond(new ARFieldHandle('ProductListItem', 'productListID'), $ids));
		$f->setOrder(new ARFieldHandle('ProductList', 'position'));
		$f->setOrder(new ARFieldHandle('ProductListItem', 'productListID'));
		$f->setOrder(new ARFieldHandle('ProductListItem', 'position'));
		$items = ActiveRecordModel::getRecordSetArray('ProductListItem', $f, array('ProductList', 'Product', 'ProductImage'));

		$items = ActiveRecordGroup::mergeGroupsWithFields('ProductList', $lists, $items);

		$response = new ActionResponse();
		$response->set('ownerID', $categoryID);
		$response->set('items', $items);
		return $response;
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
	public function create()
	{
		return parent::create();
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
	public function delete()
	{
		return parent::delete();
	}

	/**
	 * @role update
	 */
	public function sort()
	{
		return parent::sort();
	}

	public function edit()
	{
		return parent::edit();
	}
}

?>