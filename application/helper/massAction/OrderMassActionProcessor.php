<?php

include_once dirname(__file__) . '/MassActionProcessor.php';

/**
 * @package application/helper/massAction
 * @author Integry Systems
 */
class OrderMassActionProcessor extends MassActionProcessor
{
	protected function processRecord(CustomerOrder $order)
	{
		$order->processMass_history = new OrderHistory($order, $this->sessionUser->getUser());

		switch($this->getAction())
		{
			case 'setNew':
				$status = CustomerOrder::STATUS_NEW;
				break;
			case 'setProcessing':
				$status = CustomerOrder::STATUS_PROCESSING;
				break;
			case 'setAwaitingShipment':
				$status = CustomerOrder::STATUS_AWAITING;
				break;
			case 'setShipped':
				$status = CustomerOrder::STATUS_SHIPPED;
				break;
			case 'setReturned':
				$status = CustomerOrder::STATUS_RETURNED;
				break;
			case 'setUnfinalized':
				$order->isFinalized->set(0);
				break;
			case 'setCancel':
				$order->cancel();
				break;
			case 'setFinalized':
				if (!$order->isFinalized && $order->user)
				{
					$order->finalize();
				}
				break;
		}

		if (isset($status) && ($status != $order->status))
		{
			$order->setStatus($status);
			$this->params['controller']->sendStatusNotifyEmail($order);
		}
	}

	protected function saveRecord(CustomerOrder $order)
	{
		parent::saveRecord($order);
		if (!in_array($this->getAction(), array('setFinalized', 'delete')))
		{
			$order->processMass_history->saveLog();
			unset($order->processMass_history);
		}
	}
}

?>