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

    private function getTransactionUpdateResponse()
    {
        $response = new CompositeJSONResponse();
        $response->addAction('transaction', 'backend.payment', 'transaction');
        $response->addAction('totals', 'backend.payment', 'totals');
        return $response;
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
}

?>