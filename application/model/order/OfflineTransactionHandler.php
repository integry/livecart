<?php

ClassLoader::import('library.payment.TransactionPayment');
ClassLoader::import('library.payment.TransactionResult');

/**
 * 
 *	
 * @package application.model.order
 * @author Integry Systems <http://integry.com>   
 */
class OfflineTransactionHandler extends TransactionPayment
{
	public function isVoidable()
	{
		return true;
	}
	
	public function getValidCurrency($currency)
	{
		return $currency;
	}
	
	public function void()
	{
		$result = new TransactionResult();
		$result->amount->set($this->details->amount->get());
		$result->currency->set($this->details->currency->get());		
		$result->setTransactionType(TransactionResult::TYPE_VOID);
		return $result;
	}
}

?>