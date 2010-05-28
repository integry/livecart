<?php

/**
 * @package application.helper
 * @author Integry Systems
 * 
 */
class LiveCartSimpleXMLElement extends SimpleXMLElement
{
	public function addChild($key, $value=null)
	{
		// this version of addChild() escapes $value, 
		$child = parent::addChild($key);
		if($value != null)
		{
			$child[0] = $value;
		}
		return $child;
	}
}
?>