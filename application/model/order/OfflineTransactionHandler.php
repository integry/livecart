<?php

ClassLoader::import('library.payment.TransactionPayment');
ClassLoader::import('library.payment.TransactionResult');

class OfflineTransactionHandler extends TransactionPayment
{
	public static function isVoidable()
	{
		return true;
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