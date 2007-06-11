<?php

class PaymentMethodManager
{
	public function getCreditCardHandlerList()
	{
		return self::getPaymentHandlerList(dirname(__FILE__) . '/method/cc');
	}
	
	private function getPaymentHandlerList($dir)
	{
		$ret = array();
		
		foreach (new DirectoryIterator($dir) as $method)
		{
			if (substr($method->getFileName(), 0, 1) != '.')
			{
				$ret[] = $method->getFileName();
			}
		}
		
		return $ret;	
	}
}

?>