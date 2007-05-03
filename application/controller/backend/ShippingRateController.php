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
 */
class ShippingRateController extends StoreManagementController
{
    public function delete()
    {
        return new RawResponse('delete');
    }
    
    public function edit()
    {
        return new RawResponse('edit');
    }
    
    public function save()
    {
        return new RawResponse('save');
    }
    
    public function sort()
    {
        return new RawResponse('sort');
    }
}
?>