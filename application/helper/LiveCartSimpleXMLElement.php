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
	
	function Xml2SimpleArray($xml)
	{
        foreach($xml->children() as $b)
        {
                $a = $b->getName();
                if(!$b->children())
                {
                        $arr[$a] = trim($b[0]);
                }
                else{
                        $arr[$a] = self::Xml2SimpleArray($b);
                }
        }
        
        return $arr;
	} 
}
?>
