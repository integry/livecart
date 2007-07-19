<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.*");

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
        
        $customerOrder = CustomerOrder::getInstanceById($this->request->get('id'), ActiveRecord::LOAD_DATA);
        $response->set('defaultCurrencyCode', $this->application->getDefaultCurrencyCode());
        $response->set('logs', OrderLog::getRecordSetByOrder($customerOrder, null, array('User'))->toArray());
        return $response;
    }
}

?>