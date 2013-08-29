<?php

/**
 *
 *
 *  @package application/helper/smarty
 *  @author Integry Systems
 */
function smarty_modifier_mark_substring($string, $substring, $start = '<strong>', $end = '</strong>')
{
	$substring = preg_replace(
		array('/\//', '/\^/', '/\./', '/\$/', '/\|/', '/\(/', '/\)/', '/\[/', '/\]/', '/\*/', '/\+/', '/\?/', '/\{/', '/\}/', '/\,/'),
		array('\/', '\^', '\.', '\$', '\|', '\(', '\)', '\[', '\]', '\*', '\+', '\?', '\{', '\}', '\,'),
		$substring
	);
	return preg_replace('/('.$substring.')/i', $start.'$0'.$end, $string);
}

?>