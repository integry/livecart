<?php


/**
 * Manage category product list items
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class ProductBundleItemController extends StoreManagementController
{
	/**
	 * Creates new relationship
	 *
	 * @role update
	 */
	public function addAction()
	{
		$productID = $this->request->gget('relatedownerID');
		$ownerID = $this->request->gget('id');

		$owner = Product::getInstanceByID($ownerID, Product::LOAD_DATA);
		$product = Product::getInstanceByID($productID, Product::LOAD_DATA, array('ProductImage'));

		if ($product->isBundle())
		{
			return new JSONResponse(array('error' => $this->translate('_err_bundle_with_bundle')));
		}

		if(!ProductBundle::hasRelationship($owner, $product))
		{
			$instance = ProductBundle::getNewInstance($owner, $product);
			if (!$instance)
			{
				return new JSONResponse(array('error' => $this->translate('_err_add_to_itself')));
			}

			$instance->save();

			$response = new ActionResponse();
			$response->set('product', $product->toArray());
			$response->set('added', true);
			$response->set('total', $this->getTotal($owner));
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
		$relatedProductID = (int)$this->request->gget('id');
		$productID = (int)$this->request->gget('relatedProductID');

		$item = ActiveRecordModel::getInstanceByID('ProductBundle', array('productID' => $productID, 'relatedProductID' => $relatedProductID), ActiveRecordModel::LOAD_DATA);
		$item->delete();

		return new JSONResponse(array('total' => $this->getTotal($item->getProduct())), 'success');
	}

	/**
	 * @role update
	 */
	public function setCountAction()
	{
		$productID = (int)$this->request->gget('id');
		$relatedProductID = (int)$this->request->gget('relatedownerID');

		$item = ActiveRecordModel::getInstanceByID('ProductBundle', array('productID' => $productID, 'relatedProductID' => $relatedProductID), ActiveRecordModel::LOAD_DATA);

		$count = $this->request->gget('count');
		if ($count < 0 || !$count)
		{
			$count = 1;
		}
		$item->count->set($count);
		$item->save();

		return new JSONResponse(array('count' => $count, 'total' => $this->getTotal($item->getProduct())), 'success');
	}

	/**
	 * @role update
	 */
	public function sortAction()
	{
		$target = $this->request->gget('target');
		preg_match('/_(\d+)$/', $target, $match); // Get group.
		$productID = $match[1];

		foreach($this->request->gget($this->request->gget('target'), array()) as $position => $id)
		{
			$item = ActiveRecordModel::getInstanceByID('ProductBundle', array('productID' => $productID, 'relatedProductID' => $id), ActiveRecord::LOAD_DATA);
			$item->position->set($position);
			$item->save();
		}

		return new JSONResponse(false, 'success');
	}

	private function getTotal(Product $product)
	{
		$currency = $this->application->getDefaultCurrency();
		return $currency->getFormattedPrice(ProductBundle::getTotalBundlePrice($product, $currency));
	}
}

?>