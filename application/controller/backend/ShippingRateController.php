<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.delivery.ShippingService");
ClassLoader::import("application.model.delivery.ShippingRate");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");
		
		
/**
 * Application settings management
 *
 * @package application.controller.backend
 *
 * @role product
 */
class ShippingRateController extends StoreManagementController
{
    public function delete()
    {
        if($id = (int)$this->request->getValue('id'))
        {
            ShippingRate::getInstanceByID($id)->delete();
        }
        
        return new JSONResponse(array('status' => 'success'));
    }
    
    public function edit()
    {
        return new RawResponse('edit');
    }
    
    public function save()
    {
        return new RawResponse('save');
    }
}
?>