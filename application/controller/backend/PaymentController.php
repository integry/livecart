<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.Currency");

/**
 * @package application.controller.backend
 * @author Integry Systems
 * @role order
 */
class PaymentController extends StoreManagementController
{
	public function index()
	{
		$order = CustomerOrder::getInstanceById($this->request->gget('id'));
		$transactions = $this->appendOfflineTransactionData($this->getTransactionArray($order));
		ActiveRecordModel::addArrayToEavQueue('Transaction', $transactions);

		foreach ($transactions as $id => $transaction)
		{
			if (isset($transaction['ParentTransaction']))
			{
				unset($transactions[$id]);
			}
		}

		$orderArray = $order->toArray(array('payments' => true));
		$captureForm = $this->buildCaptureForm();
		$captureForm->set('amount', $orderArray['amountNotCaptured']);

		$response = new ActionResponse();
		$response->set('transactions', $transactions);
		$response->set('order', $orderArray);
		$response->set('offlinePaymentForm', $this->buildOfflinePaymentForm());
		$response->set('capture', $captureForm);

		return $response;
	}

	/**
	 * @role update
	 */
	public function void()
	{
		$transaction = Transaction::getInstanceById($this->request->gget('id'));

		$voidTransaction = $transaction->void();

		if ($voidTransaction instanceof Transaction)
		{
			$voidTransaction->user->set($this->user);
			$voidTransaction->comment->set($this->request->gget('comment'));
			$voidTransaction->save();

			return $this->getTransactionUpdateResponse();
		}
		else
		{
			return new JSONResponse(false, 'failure', $voidTransaction->getMessage());
		}
	}

	/**
	 * @role update
	 */
	public function capture()
	{
		$transaction = Transaction::getInstanceById($this->request->gget('id'));

		$captureTransaction = $transaction->capture($this->request->gget('amount'), $this->request->gget('complete'));

		if ($captureTransaction instanceof Transaction)
		{
			$captureTransaction->user->set($this->user);
			$captureTransaction->comment->set($this->request->gget('comment'));
			$captureTransaction->save();

			if ($this->request->gget('complete'))
			{
				$transaction->isCompleted->set(true);
				$transaction->save();
			}

			return $this->getTransactionUpdateResponse();
		}
		else
		{
			return new JSONResponse(false, 'failure', $captureTransaction->getMessage());
		}
	}

	/**
	 * @role update
	 */
	public function deleteCcNumber()
	{
		$transaction = Transaction::getInstanceById($this->request->gget('id'));
		$transaction->truncateCcNumber();
		$transaction->save();

		return $this->getTransactionUpdateResponse();
	}

	/**
	 * @role update
	 */
	public function addOffline()
	{
		$order = CustomerOrder::getInstanceById($this->request->gget('id'));

		$transaction = Transaction::getNewOfflineTransactionInstance($order, $this->request->gget('amount'));
		$transaction->comment->set($this->request->gget('comment'));
		$transaction->user->set($this->user);
		$transaction->save();

		if ($order->totalAmount->get() <= $order->capturedAmount->get())
		{
			$order->isPaid->set(true);
			$order->save();
		}

		$this->request->set('id', $transaction->getID());

		return $this->getTransactionUpdateResponse();
	}

	public function totals()
	{
		$transaction = Transaction::getInstanceById($this->request->gget('id'));
		$response = new ActionResponse();
		$response->set('order', $transaction->order->get()->toArray(array('payments' => true)));
		return $response;
	}

	/**
	 *  Generates a page fragment with particular transaction
	 *
	 *  @return ActionResponse
	 */
	public function transaction()
	{
		$transaction = Transaction::getInstanceById($this->request->gget('id'));
		$transactions = $this->getTransactionArray($transaction->order->get());

		$orderArray = $transaction->order->get()->toArray(array('payments' => true));
		$captureForm = $this->buildCaptureForm();
		$captureForm->set('amount', $orderArray['amountNotCaptured']);

		$response = new ActionResponse();
		$response->set('transaction', $transactions[$transaction->getID()]);
		$response->set('capture', $captureForm);
		return $response;
	}

	public function ccForm()
	{
		$order = CustomerOrder::getInstanceById($this->request->gget('id'));

		$response = new ActionResponse();

		$response->set('currency', $this->request->gget('currency', $this->application->getDefaultCurrencyCode()));

		$ccHandler = $this->application->getCreditCardHandler();
		if ($ccHandler)
		{
			$response->set('ccHandler', $ccHandler->toArray());
			$response->set('ccForm', $this->buildCreditCardForm());

			$months = range(1, 12);
			$months = array_combine($months, $months);

			$years = range(date('Y'), date('Y') + 20);
			$years = array_combine($years, $years);

			$response->set('months', $months);
			$response->set('years', $years);
		}

		$orderArray = $order->toArray(array('payments' => true));
		$form = $this->buildCreditCardForm();
		$form->set('amount', $orderArray['amountDue']);
		$form->set('name', $order->user->get()->getName());

		$response->set('ccTypes', $this->application->getCardTypes($ccHandler));
		$response->set('order', $orderArray);
		$response->set('ccForm', $form);
		return $response;
	}

	/**
	 * @role update
	 */
	public function processCreditCard()
	{
		$order = CustomerOrder::getInstanceById($this->request->gget('id'));

		if (!$this->buildCreditCardValidator()->isValid())
		{
			return new ActionRedirectResponse('backend.payment', 'ccForm', array('id' => $order->getID()));
		}

		// set up transaction details
		$transaction = new LiveCartTransaction($order, $order->currency->get());
		$transaction->amount->set($this->request->gget('amount'));

		// process payment
		$handler = $this->application->getCreditCardHandler($transaction);
		if ($this->request->isValueSet('ccType'))
		{
			$handler->setCardType($this->request->gget('ccType'));
		}

		$handler->setCardData($this->request->gget('ccNum'), $this->request->gget('ccExpiryMonth'), $this->request->gget('ccExpiryYear'), $this->request->gget('ccCVV'));

		if ($this->config->get('CC_AUTHONLY'))
		{
			$result = $handler->authorize();
		}
		else
		{
			$result = $handler->authorizeAndCapture();
		}

		if ($result instanceof TransactionResult)
		{
			$order->isPaid->set(true);

			$transaction = Transaction::getNewInstance($order, $result);
			$transaction->setHandler($handler);
			$transaction->comment->set($this->request->gget('comment'));
			$transaction->save();

			if ($order->totalAmount->get() <= $order->capturedAmount->get())
			{
				$order->isPaid->set(true);
				$order->save();
			}

			$this->request->set('id', $transaction->getID());
			return $this->getTransactionUpdateResponse();
		}
		elseif ($result instanceof TransactionError)
		{
			return new JSONResponse(false, 'failure', $this->translate('_err_processing_cc'));
		}
		else
		{
			throw new Exception('Unknown transaction result type: ' . get_class($result));
		}
	}

	public function changeOrderPaidStatus()
	{
		$order = CustomerOrder::getInstanceById($this->request->gget('id'));
		if (0 == $this->request->gget('status'))
		{
			foreach ($order->getTransactions() as $transaction)
			{
				$transaction->void();
			}
		}
		else
		{
			$transaction = Transaction::getNewOfflineTransactionInstance($order, $order->getDueAmount());
			$transaction->user->set($this->user);
			$transaction->save();
		}

		$order->isPaid->set($this->request->gget('status') == true);
		$order->save();

		return new RawResponse();
	}
	
	public function changeOfflinePaymentMethod()
	{
		try {
			$request = $this->getRequest();
			$transaction = Transaction::getInstanceById($request->gget('id'));
			$handlerID = $request->gget('handlerID');
			$transaction->setOfflineHandler($handlerID);
			$transaction->save();
			return new JSONResponse(
				array('handlerID' => $handlerID,
					  'name' => OfflineTransactionHandler::getMethodName($handlerID)),
				'saved');
		}
			catch(Exception $e)
		{
			return new JSONResponse(null, 'error');
		}
	}

	private function getTransactionUpdateResponse()
	{
		$response = new CompositeJSONResponse();
		$response->addAction('transaction', 'backend.payment', 'transaction');
		$response->addAction('totals', 'backend.payment', 'totals');
		return $response;
	}

	/**
	 *  Return a structured transactions array (tree of transactions with related sub-transactions)
	 *
	 *  @return array
	 */
	private function getTransactionArray(CustomerOrder $order)
	{
		$transactions = array();
		foreach ($order->getTransactions()->toArray() as $transaction)
		{
			
			$transactions[$transaction['ID']] = $transaction;
			if (isset($transaction['ParentTransaction']))
			{
				$parent = $transaction['ParentTransaction']['ID'];
				$transactions[$parent]['transactions'][] =& $transactions[$transaction['ID']];
			}
		}
		return $transactions;
	}

	private function buildCaptureForm()
	{
		return new Form($this->buildCaptureValidator());
	}

	private function buildCaptureValidator()
	{
		$validator = $this->getValidator("paymentCapture", $this->request);
		$validator->addCheck('amount', new IsNotEmptyCheck($this->translate('_err_enter_amount')));
		$validator->addCheck('amount', new MinValueCheck($this->translate('_err_amount_not_positive'), 0.000001));

		$validator->addFilter('amount', new NumericFilter());

		return $validator;
	}

	private function buildOfflinePaymentForm()
	{
		return new Form($this->buildOfflinePaymentValidator());
	}

	private function buildOfflinePaymentValidator()
	{
		$validator = $this->getValidator("offlinePayment", $this->request);
		$validator->addCheck('amount', new IsNotEmptyCheck($this->translate('_err_enter_amount')));
		$validator->addCheck('amount', new MinValueCheck($this->translate('_err_amount_not_positive'), 0.01));

		$validator->addFilter('amount', new NumericFilter());

		return $validator;
	}

	private function buildCreditCardForm()
	{
		return new Form($this->buildCreditCardValidator());
	}

	private function buildCreditCardValidator()
	{
		$validator = $this->getValidator("creditCard", $this->request);

		$validator->addCheck('amount', new IsNotEmptyCheck($this->translate('_err_enter_amount')));
		$validator->addCheck('amount', new MinValueCheck($this->translate('_err_amount_negative'), 0));

		$validator->addCheck('ccNum', new IsNotEmptyCheck($this->translate('_err_enter_cc_num')));
//		$validator->addCheck('ccType', new IsNotEmptyCheck($this->translate('_err_select_cc_type')));
		$validator->addCheck('ccExpiryMonth', new IsNotEmptyCheck($this->translate('_err_select_cc_expiry_month')));
		$validator->addCheck('ccExpiryYear', new IsNotEmptyCheck($this->translate('_err_select_cc_expiry_year')));

		$validator->addFilter('ccCVV', new RegexFilter('[^0-9]'));
		$validator->addFilter('amount', new NumericFilter);
		$validator->addFilter('ccNum', new RegexFilter('[^ 0-9]'));

		return $validator;
	}
	
	private $availableOfflinePaymentMethods = null;

	private function appendOfflineTransactionData($transactions)
	{
		foreach($transactions as &$transaction)
		{
			if($transaction['methodType'] == Transaction::METHOD_OFFLINE)
			{
				if($this->availableOfflinePaymentMethods === null)
				{
					$this->availableOfflinePaymentMethods = array();
					foreach(OfflineTransactionHandler::getEnabledMethods() as $methodID)
					{
						$this->availableOfflinePaymentMethods[] = array('ID'=>$methodID, 'name'=>OfflineTransactionHandler::getMethodName($methodID));
					}
				}
				if(isset($transaction['serializedData'], $transaction['serializedData']['handlerID']))
				{
					$transaction['handlerID'] = $transaction['serializedData']['handlerID'];
				}
				$transaction['availableOfflinePaymentMethods'] = $this->availableOfflinePaymentMethods;
			}
		}
		return $transactions;
	}

}

?>