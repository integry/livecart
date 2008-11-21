<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.delivery.ShippingService");
ClassLoader::import("application.model.delivery.ShippingRate");

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
		if($id = (int)$this->request->get('id'))
		{
			ShippingRate::getInstanceByID($id)->delete();
		}

		return new JSONResponse(false, 'success');
	}
}
?>