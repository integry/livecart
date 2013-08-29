<?php

/**
 * Manage order notes (communication with customer)
 *
 * @package application/controller/backend
 * @author Integry Systems
 *
 * @role order
 */
class OrderLogController extends StoreManagementController
{
	public function indexAction()
	{
		$response = new ActionResponse();
		$customerOrder = CustomerOrder::getInstanceById($this->request->gget('id'), true, array('User', 'Currency'));

		$logs = array();
		foreach(OrderLog::getRecordSetByOrder($customerOrder, null, array('User'))->toArray() as $entry)
		{
			if (!$entry['oldValue'])
			{
				$entry['oldValue'] = array();
			}
			if (!$entry['newValue'])
			{
				$entry['newValue'] = array();
			}

			if($entry['action'] != OrderLog::ACTION_REMOVED_WITH_SHIPMENT)
			{
				$logs[] = $entry;
				$logs[count($logs) - 1]['items'] = array();
			}
			else
			{
				$logs[count($logs) - 1]['items'][] = $entry;
			}
		}

		$response->set('defaultCurrencyCode', $this->application->getDefaultCurrencyCode());
		$response->set('logs', $logs);
		return $response;
	}
}

?>