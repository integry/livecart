<?php

/**
 * Creates a handle string that is usually used as part of URL to uniquely
 * identify some record
 * Example: "Some Record TITLE!!!" becomes "some-record-title"
 * @param string $str
 * @return string
 *
 * @todo test with multibyte strings
 */
function createHandleString($str)
{
	$wordSeparator = '.';
	
	$str = strtolower(trim(strip_tags(stripslashes($str))));		

	// fix accented characters
    $from = array();
	for ($k = 192; $k <= 255; $k++) 
    {
		$from[] = chr($k);
	}

	$repl = array ('A','A','A','A','A','A','A','E','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','O','U','U','U','U','Y','b','b','a','a','a','a','a','a','a','e','e','e','e','e','i','i','i','i','n','n','o','o','o','o','o','o','o','u','u','u','u','y','y','y');		

    $str = str_replace($from, $repl, $str);
	
	// non alphanumeric characters
	$str = preg_replace('/[^a-z0-9]/', $wordSeparator, $str);
	
	// double separators
	$str = preg_replace('/[\\' . $wordSeparator . ']{2,}/', $wordSeparator, $str);
	
    // separators from beginning and end
	$str = preg_replace('/^[\\' . $wordSeparator . ']/', '', $str);
	$str = preg_replace('/[\\' . $wordSeparator . ']$/', '', $str);
			        
	return $str;
}
	
?>