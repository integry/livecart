<?php

include_once dirname(__file__) . '/MassActionProcessor.php';

/**
 * @package application.helper.massAction
 * @author Integry Systems
 */
class OrderMassActionProcessor extends MassActionProcessor
{
	protected function processRecord(CustomerOrder $order)
	{
		$order->processMass_history = new OrderHistory($order, SessionUser::getUser());

		switch($this->getAction())
		{
			case 'setNew':
				$order->status->set(CustomerOrder::STATUS_NEW);
				break;
			case 'setProcessing':
				$order->status->set(CustomerOrder::STATUS_PROCESSING);
				break;
			case 'setAwaitingShipment':
				$order->status->set(CustomerOrder::STATUS_AWAITING);
				break;
			case 'setShipped':
				$order->status->set(CustomerOrder::STATUS_SHIPPED);
				break;
			case 'setReturned':
				$order->status->set(CustomerOrder::STATUS_RETURNED);
				break;
			case 'setFinalized':
				$order->isFinalized->set(1);
				break;
			case 'setUnfinalized':
				$order->isFinalized->set(0);
				break;
			case 'setCancel':
				$order->isCancelled->set(true);
				break;
		}
	}

	protected function saveRecord(CustomerOrder $order)
	{
		parent::saveRecord($order);
		if($this->getAction() != 'delete')
		{
			$order->processMass_history->saveLog();
			unset($order->processMass_history);
		}
	}
}

?>