<?php

/**
 * Application settings management
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role delivery
 */
class ShippingRateController extends StoreManagementController
{
	/**
	 * @role update
	 */
	public function delete()
	{
		if($id = (int)$this->request->gget('id'))
		{
			ShippingRate::getInstanceByID($id)->delete();
		}

		return new JSONResponse(false, 'success');
	}
}
?>