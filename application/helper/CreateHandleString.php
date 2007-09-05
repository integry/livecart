<?php

/**
 * Creates a handle string that is usually used as part of URL to uniquely
 * identify some record.
 * 
 * Basically it simply removes reserved URL characters and does some basic formatting
 *
 * @param string $str
 * @return string
 *
 * @todo test with multibyte strings
 */
function createHandleString($str)
{
	if (is_array($str))
	{
        $str = array_shift($str);
    }
    
    $str = str_replace(array('$', '&', '+', '/', ':', ';', '=', '?', '@', '.', ' ', '"', "'"), '-', $str);

	$str = preg_replace('/-{2,}/', '-', $str);
	$str = preg_replace('/^-/', '', $str);
	$str = preg_replace('/-$/', '', $str);
	
	$str = urlencode($str);
	
	return $str;
}
	
?>