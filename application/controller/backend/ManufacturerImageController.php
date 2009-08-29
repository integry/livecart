<?php

ClassLoader::import('application.controller.backend.abstract.ObjectImageController');
ClassLoader::import('application.model.product.Manufacturer');
ClassLoader::import('application.model.product.ManufacturerImage');

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
	public function init()
	{
		parent::init();
		$this->loadLanguageFile('backend/ProductImage');
	}

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