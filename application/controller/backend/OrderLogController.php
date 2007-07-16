<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.CustomerOrder");
ClassLoader::import("application.model.order.OrderNote");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");

/**
 * Manage order notes (communication with customer)
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 *
 * @role order
 */
class OrderLogController extends StoreManagementController
{
    public function index()
    {
        $response = new ActionResponse();
        $response->set('logs', array('1', '2', '3'));
        return $response;
    }
}

?>