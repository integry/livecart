<?php


/**
 * Product Image controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 * @role product
 */
class ProductQuickImageController extends ObjectImageController
{
	public function indexAction()
	{
		$this->loadLanguageFile('backend/ProductImage');
		return parent::index();
	}

	/**
	 * @role update
	 */
	public function uploadAction()
	{
		$result = parent::upload();
		$request = $this->getRequest();
		if($request->gget('setAsMainImage'))
		{
			$data = json_decode($result->get('result'));
			$imageID = $data->ID;
			$filter = select(eq(f('ProductImage.productID'), $request->gget('ownerId')));
			$filter->setOrder(f('ProductImage.position'));
			$r = ActiveRecordModel::getRecordSetArray('ProductImage', $filter, true);
			$order = array($imageID);
			foreach($r as $item)
			{
				if ($imageID != $item['ID'])
				{
					$order[] = $item['ID'];
				}
			}
			parent::saveOrder($order);
		}
		return $result;
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