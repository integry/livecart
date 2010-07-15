<?php

require_once dirname(__FILE__) . '/OrderTestCommon.php';

/**
 *	Test Transaction model
 *
 *  @author Integry Systems
 *  @package test.model.order
 */
class TransactionTest extends OrderTestCommon
{
	function testTransactionCurrencyConverting()
	{
		$eur = Currency::getNewInstance('EUR');
		$eur->rate->set('3.452');
		$eur->save();

		$this->products[0]->setPrice($this->usd, '9.99');
		$this->order->addProduct($this->products[0], 1);
		$this->order->save();

		//$this->order->finalize();

		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID(), true);
		$order->loadAll();
		$details = new LiveCartTransaction($order, $eur);

		ActiveRecord::clearPool();
		$order = CustomerOrder::getInstanceByID($this->order->getID(), true);
		$order->loadAll();

		$result = new TransactionResult();
		$result->amount->set($details->amount->get());
		$result->currency->set($details->currency->get());

		$transaction = Transaction::getNewInstance($order, $result);

		$this->assertEqual($transaction->amount->get(), '9.99');
	}

}