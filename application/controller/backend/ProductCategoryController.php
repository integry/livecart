<?php


/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class ProductCategoryController extends StoreManagementController
{
	public function indexAction()
	{
		$product = Product::getInstanceById($this->request->gget('id'), ActiveRecord::LOAD_DATA, array('Category'));
		$product->category->get()->getPathNodeSet();

		$additional = $product->getAdditionalCategories();
		foreach ($additional as $category)
		{
			$category->getPathNodeSet();
		}

		$response = new ActionResponse('product', $product->toArray());

		if ($additional)
		{
			$response->set('categories', ARSet::buildFromArray($additional)->toArray());
		}

		return $response;
	}

	public function saveMainCategoryAction()
	{
		$product = Product::getInstanceByID($this->request->gget('id'), ActiveRecord::LOAD_DATA, array('Category'));
		$category = Category::getInstanceByID($this->request->gget('categoryId'), ActiveRecord::LOAD_DATA);
		$product->category->set($category);
		$product->save();

		Category::recalculateProductsCount();

		return new RawResponse($category->getID());
	}

	public function addCategoryAction()
	{
		$product = Product::getInstanceByID($this->request->gget('id'), ActiveRecord::LOAD_DATA, array('Category'));
		$category = Category::getInstanceByID($this->request->gget('categoryId'), ActiveRecord::LOAD_DATA);

		// check if the product is not assigned to this category already
		$relation = ActiveRecordModel::getInstanceByIdIfExists('ProductCategory', array('productID' => $product->getID(), 'categoryID' => $category->getID()));
		if ($relation->isExistingRecord() || ($product->category->get() === $category))
		{
			return new JSONResponse(false, 'failure', $this->translate('_err_already_assigned'));
		}

		$relation->save();

		return new JSONResponse(array('data' => $relation->toFlatArray()));
	}

	public function deleteAction()
	{
		$product = Product::getInstanceByID($this->request->gget('id'), ActiveRecord::LOAD_DATA, array('Category'));
		$category = Category::getInstanceByID($this->request->gget('categoryId'), ActiveRecord::LOAD_DATA);

		$relation = ActiveRecordModel::getInstanceById('ProductCategory', array('productID' => $product->getID(), 'categoryID' => $category->getID()));
		$relation->delete();

		return new JSONResponse(array('data' => $relation->toFlatArray()));
	}
}