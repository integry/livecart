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

		$customerOrder = CustomerOrder::getInstanceById($this->request->get('id'), true, array('User', 'Currency'));

		$logs = array();
		foreach(OrderLog::getRecordSetByorderBy($customerOrder, null, array('User'))->toArray() as $entry)
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

		$this->set('defaultCurrencyCode', $this->application->getDefaultCurrencyCode());
		$this->set('logs', $logs);
	}
}

?>