<?php


/**
 * Manufacturer image controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 * @role product
 */
class ManufacturerImageController extends ObjectImageController
{
	public function initialize()
	{
		parent::initialize();
		$this->loadLanguageFile('backend/ProductImage');
	}

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
		return 'ManufacturerImage';
	}

	protected function getOwnerClass()
	{
		return 'Manufacturer';
	}

	protected function getForeignKeyName()
	{
		return 'manufacturerID';
	}

}
?>