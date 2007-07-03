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
        $order = CustomerOrder::getInstanceById($this->request->getValue('id'));
        
        $transactions = $this->getTransactionArray($order);
        
        foreach ($transactions as $id => $transaction)
        {
            if (isset($transaction['ParentTransaction']))
            {
                unset($transactions[$id]);
            }
        }
        
        $orderArray = $order->toArray(array('payments' => true));
        $captureForm = $this->buildCaptureForm();
        $captureForm->setValue('amount', $orderArray['amountNotCaptured']);
        
		$response = new ActionResponse();
        $response->set('transactions', $transactions);
        $response->set('order', $orderArray);
        $response->set('offlinePaymentForm', $this->buildOfflinePaymentForm());
        $response->set('capture', $captureForm);
        return $response;
    }
    
    public function void()
    {
        $transaction = Transaction::getInstanceById($this->request->getValue('id'));
        
        $voidTransaction = $transaction->void();
        
        if ($voidTransaction instanceof Transaction)
        {
	        $voidTransaction->user->set($this->user);
	        $voidTransaction->comment->set($this->request->getValue('comment'));
	        $voidTransaction->save();

            return $this->getTransactionUpdateResponse();
        }
        else
        {
            return new JSONResponse(array('error' => true, 'msg' => $voidTransaction->getMessage()));
        }
    }

    public function capture()
    {
        $transaction = Transaction::getInstanceById($this->request->getValue('id'));
        
        $captureTransaction = $transaction->capture($this->request->getValue('amount'), $this->request->getValue('complete'));
        
        if ($captureTransaction instanceof Transaction)
        {
			$captureTransaction->user->set($this->user);
	        $captureTransaction->comment->set($this->request->getValue('comment'));
	        $captureTransaction->save();

	        if ($this->request->getValue('complete'))
	        {
				$transaction->isCompleted->set(true);
				$transaction->save();
			}

            return $this->getTransactionUpdateResponse();
        }
        else
        {
            return new JSONResponse(array('error' => true, 'msg' => $captureTransaction->getMessage()));
        }
    }
    
    public function addOffline()
    {
        $order = CustomerOrder::getInstanceById($this->request->getValue('id'));
        $transaction = Transaction::getNewOfflineTransactionInstance($order, $this->request->getValue('amount'));
        $transaction->comment->set($this->request->getValue('comment'));
        $transaction->user->set($this->user);        
        $transaction->save();
        
        $this->request->setValue('id', $transaction->getID());
        return $this->getTransactionUpdateResponse();
    }

    public function totals()
    {
        $transaction = Transaction::getInstanceById($this->request->getValue('id'));
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
        $transaction = Transaction::getInstanceById($this->request->getValue('id'));
        $transactions = $this->getTransactionArray($transaction->order->get());
        
        $orderArray = $transaction->order->get()->toArray(array('payments' => true));
        $captureForm = $this->buildCaptureForm();
        $captureForm->setValue('amount', $orderArray['amountNotCaptured']);        
        
        $response = new ActionResponse();
        $response->set('transaction', $transactions[$transaction->getID()]);
        $response->set('capture', $captureForm);
        return $response;
    }
    
    public function ccForm()
    {
        $order = CustomerOrder::getInstanceById($this->request->getValue('id'));

        $response = new ActionResponse();
        
		$response->setValue('currency', $this->request->getValue('currency', $this->store->getDefaultCurrencyCode())); 
        
        $ccHandler = Store::getInstance()->getCreditCardHandler();
        if ($ccHandler)
        {
			$response->setValue('ccHandler', $ccHandler->toArray());
			$response->setValue('ccForm', $this->buildCreditCardForm());
			
			$months = range(1, 12);
			$months = array_combine($months, $months);
			
			$years = range(date('Y'), date('Y') + 20);
			$years = array_combine($years, $years);
			
			$response->setValue('months', $months);
			$response->setValue('years', $years);
		}

        $orderArray = $order->toArray(array('payments' => true));
        $form = $this->buildCreditCardForm();
        $form->setValue('amount', $orderArray['amountDue']);
        $form->setValue('name', $order->user->get()->getName());
                
        $response->set('ccTypes', Store::getInstance()->getCardTypes($ccHandler));
        $response->set('order', $orderArray);
        $response->set('ccForm', $form);
        return $response;
    }
    
    public function processCreditCard()
    {
        $order = CustomerOrder::getInstanceById($this->request->getValue('id'));
        
		if (!$this->buildCreditCardValidator()->isValid())
		{
            return new ActionRedirectResponse('backend.payment', 'ccForm', array('id' => $order->getID()));
        }       
        
        // set up transaction details
        $transaction = new LiveCartTransaction($order, $order->currency->get());
        $transaction->amount->set($this->request->getValue('amount'));
        
        // process payment
        $handler = Store::getInstance()->getCreditCardHandler($transaction);
        if ($this->request->isValueSet('ccType'))
        {
            $handler->setCardType($this->request->getValue('ccType'));
        }
        		
        $handler->setCardData($this->request->getValue('ccNum'), $this->request->getValue('ccExpiryMonth'), $this->request->getValue('ccExpiryYear'), $this->request->getValue('ccCVV'));
        
        if ($this->config->getValue('CC_AUTHONLY'))
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
            $transaction->comment->set($this->request->getValue('comment'));
            $transaction->save();
            
            $this->request->setValue('id', $transaction->getID());
            return $this->getTransactionUpdateResponse();
        }
        elseif ($result instanceof TransactionError)
        {
            return new JSONResponse(array('error' => 'true', 'msg' => $this->translate('_err_processing_cc')));
        }
        else
        {
            throw new Exception('Unknown transaction result type: ' . get_class($result));
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
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildCaptureValidator());
    }

    private function buildCaptureValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");        

        $validator = new RequestValidator("paymentCapture", $this->request);
        $validator->addCheck('amount', new IsNotEmptyCheck($this->translate('_err_enter_amount')));
        $validator->addCheck('amount', new MinValueCheck($this->translate('_err_amount_negative'), 0));

        $validator->addFilter('amount', new NumericFilter());
       
        return $validator;
    }
    
    private function buildOfflinePaymentForm()
    {
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildOfflinePaymentValidator());
    }

    private function buildOfflinePaymentValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");        

        $validator = new RequestValidator("offlinePayment", $this->request);
        $validator->addCheck('amount', new IsNotEmptyCheck($this->translate('_err_enter_amount')));
        $validator->addCheck('amount', new MinValueCheck($this->translate('_err_amount_negative'), 0));

        $validator->addFilter('amount', new NumericFilter());
       
        return $validator;
    }    
    
    private function buildCreditCardForm()
    {
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->buildCreditCardValidator());        
    }

    private function buildCreditCardValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");        
        $validator = new RequestValidator("creditCard", $this->request);

        $validator->addCheck('amount', new IsNotEmptyCheck($this->translate('_err_enter_amount')));
        $validator->addCheck('amount', new MinValueCheck($this->translate('_err_amount_negative'), 0));

        $validator->addCheck('ccNum', new IsNotEmptyCheck($this->translate('_err_enter_cc_num')));
//        $validator->addCheck('ccType', new IsNotEmptyCheck($this->translate('_err_select_cc_type')));
        $validator->addCheck('ccExpiryMonth', new IsNotEmptyCheck($this->translate('_err_select_cc_expiry_month')));
        $validator->addCheck('ccExpiryYear', new IsNotEmptyCheck($this->translate('_err_select_cc_expiry_year')));
                
    	$validator->addFilter('ccCVV', new RegexFilter('[^0-9]'));
    	$validator->addFilter('amount', new NumericFilter);
    	$validator->addFilter('ccNum', new RegexFilter('[^ 0-9]'));
       
        return $validator;
    }    
}

?>