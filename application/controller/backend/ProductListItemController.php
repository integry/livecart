<?php


/**
 * Manage category product list items
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class ProductListItemController extends StoreManagementController
{
	/**
	 * Creates new relationship
	 *
	 * @role update
	 */
	public function addAction()
	{
		$productID = $this->request->get('relatedownerID');
		$ownerID = $this->request->get('id');

		$list = ActiveRecordModel::getInstanceById('ProductList', $ownerID, ActiveRecordModel::LOAD_DATA);
		$product = Product::getInstanceByID($productID, Product::LOAD_DATA, array('ProductImage'));

		if(!$list->contains($product))
		{
			$list->addProduct($product);

			$response = new ActionResponse();
			$response->set('product', $product->toArray());
			$response->set('added', true);
			return $response;
		}
		else
		{
			return new JSONResponse(array('error' => $this->translate('_err_multiple')));
		}
	}

	/**
	 * @role update
	 */
	public function deleteAction()
	{
		$item = ActiveRecordModel::getInstanceByID('ProductListItem', $this->request->get('id'), ActiveRecordModel::LOAD_DATA);
		$item->delete();

		return new JSONResponse(false, 'success');
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		$target = $this->request->get('target');
		preg_match('/_(\d+)$/', $target, $match); // Get group.

		foreach($this->request->get($this->request->get('target'), array()) as $position => $id)
		{
			$item = ActiveRecordModel::getInstanceByID('ProductListItem', $id);
			$item->position->set($position);

			if (isset($match[1]))
			{
				$item->productList->set(ActiveRecordModel::getInstanceById('ProductList', $match[1]));
			}

			$item->save();
		}

		return new JSONResponse(false, 'success');
	}
}

?>