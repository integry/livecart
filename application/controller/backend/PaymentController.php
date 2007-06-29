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
        
        $response = new ActionResponse();
        $response->setValue('transactions', $transactions);
        $response->setValue('order', $order->toArray());
        $response->setValue('offlinePaymentForm', $this->buildOfflinePaymentForm());
        return $response;
    }
    
    public function void()
    {
        $transaction = Transaction::getInstanceById($this->request->getValue('id'));
        
        $voidTransaction = $transaction->void();
        
        if ($voidTransaction instanceof Transaction)
        {
            return new ActionResponse();
        }
        else
        {
            return new JSONResponse(array('error' => true));
        }
    }
    
    public function addOffline()
    {
        $order = CustomerOrder::getInstanceById($this->request->getValue('id'));
        $transaction = Transaction::getNewOfflineTransactionInstance($order, $this->request->getValue('amount'));
        $transaction->comment->set($this->request->getValue('comment'));
        $transaction->save();
        
        return $this->getTransactionFragment($transaction);
    }

    /**
     *  Return a response that generates a page fragment with particular transaction
     *
     *  @return ActionResponse
     */   
    private function getTransactionFragment(Transaction $transaction)
    {
        $transactions = $this->getTransactionArray($transaction->order->get());
        
        $response = new ActionResponse();
        $response->set('transaction', $transactions[$transaction->getID()]);
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