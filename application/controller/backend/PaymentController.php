<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.currency");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");

/**
 * @package application.controller.backend
 * @role order
 */
class PaymentController extends StoreManagementController
{
    public function index()
    {
        $response = new ActionResponse();
        
        return $response;
    }
}

?>