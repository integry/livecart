<?php

class ShippingMethodManager
{
	private function getHandlerList()
	{
		$ret = array();
		
		foreach (new DirectoryIterator(dirname(__file__) . '/method') as $method)
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