<?php


/**
 * Product Image controller
 *
 * @package application/controller/backend
 * @author Integry Systems
 *
 * @role product
 */
class ProductImageController extends ObjectImageController
{
	public function indexAction()
	{
		return parent::index();
	}

	/**
	 * @role update
	 */
	public function uploadAction()
	{
		return parent::upload();
	}

	/**
	 * @role update
	 */
	public function saveAction()
	{
		return parent::save();
	}

	public function resizeImagesAction()
	{
		return parent::resizeImages();
	}

	/**
	 * @role update
	 */
	public function deleteAction()
	{
		if(parent::delete())
		{
			return new JSONResponse(false, 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure');
		}
	}

	/**
	 * @role update
	 */
	public function saveOrderAction()
	{
		return parent::saveOrder();
	}

	protected function getModelClass()
	{
		return 'ProductImage';
	}

	protected function getOwnerClass()
	{
		return 'Product';
	}

	protected function getForeignKeyName()
	{
		return 'productID';
	}

}
?>