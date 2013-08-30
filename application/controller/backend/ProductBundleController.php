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
		$productID = (int)$this->request->get('id');
		$product = Product::getInstanceByID($productID, ActiveRecord::LOAD_DATA);


		$this->set('ownerID', $productID);
		$this->set('categoryID', $product->category->get()->getID());
		$this->set('items', ProductBundle::getBundledProductArray($product));

		$currency = $this->application->getDefaultCurrency();
		$this->set('total', $currency->getFormattedPrice(ProductBundle::getTotalBundlePrice($product, $currency)));

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