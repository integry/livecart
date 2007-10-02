<?php

/**
 *
 * @package library.shipping
 * @author Integry Systems 
 */
class ShippingMethodManager
{
	public static function getHandlerList()
	{
		$ret = array();
		
		foreach (new DirectoryIterator(dirname(__file__) . '/method') as $method)
		{
			if (substr($method->getFileName(), 0, 1) != '.')
			{
				$ret[] = substr($method->getFileName(), 0, -4);
			}
		}
		
		return $ret;	
	}
}

?>