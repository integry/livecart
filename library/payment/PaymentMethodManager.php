<?php

class PaymentMethodManager
{
	public function getCreditCardHandlerList()
	{
		return self::getPaymentHandlerList(dirname(__FILE__) . '/method/cc');
	}
	
	public function getExpressPaymentHandlerList()
	{
		return self::getPaymentHandlerList(dirname(__FILE__) . '/method/express');
	}
	
	private function getPaymentHandlerList($dir)
	{
		$ret = array();
		
		foreach (new DirectoryIterator($dir) as $method)
		{
			if (substr($method->getFileName(), 0, 1) != '.')
			{
				$ret[] = basename($method->getFileName(), '.php');
			}
		}
		
		return $ret;	
	}
}

?>