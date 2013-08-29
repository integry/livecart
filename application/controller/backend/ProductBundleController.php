<?php


/**
 * Product bundles
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class ProductBundleController extends ProductListControllerCommon
{
	public function indexAction()
	{
		$productID = (int)$this->request->gget('id');
		$product = Product::getInstanceByID($productID, ActiveRecord::LOAD_DATA);

		$response = new ActionResponse();
		$response->set('ownerID', $productID);
		$response->set('categoryID', $product->category->get()->getID());
		$response->set('items', ProductBundle::getBundledProductArray($product));

		$currency = $this->application->getDefaultCurrency();
		$response->set('total', $currency->getFormattedPrice(ProductBundle::getTotalBundlePrice($product, $currency)));

		return $response;
	}

	protected function getOwnerClassName()
	{
		return 'Product';
	}

	protected function getGroupClassName()
	{
		return null;
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