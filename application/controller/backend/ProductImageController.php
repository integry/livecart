<?php


/**
 * Product Image controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 * @role product
 */
class ProductImageController extends ObjectImageController
{
	public function index()
	{
		return parent::index();
	}

	/**
	 * @role update
	 */
	public function upload()
	{
		return parent::upload();
	}

	/**
	 * @role update
	 */
	public function save()
	{
		return parent::save();
	}

	public function resizeImages()
	{
		return parent::resizeImages();
	}

	/**
	 * @role update
	 */
	public function delete()
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
	public function saveOrder()
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