<?php

/**
 * Creates a handle string that is usually used as part of URL to uniquely
 * identify some record.
 *
 * Basically it simply removes reserved URL characters and does some basic formatting
 *
 * @param string $str
 * @return string
 * @package application.helper
 * @author Integry Systems
 * @todo test with multibyte strings
 */
function createHandleString($str)
{
	static $cache = array();

	if (isset($cache[$str]))
	{
		return $cache[$str];
	}

	// optimized for performance
	return $cache[$str] = urlencode(preg_replace('/ {1,}/', '-', trim(strtr($str, '$&+\/:;=?@."\'#*><-,', '                        '))));
}

?>