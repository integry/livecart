<?php

/**
 *
 *
 *  @package application.helper.smarty
 *  @author Integry Systems
 */
function smarty_modifier_mark_substring($string, $substring, $start = '<strong>', $end = '</strong>')
{
	return str_replace($substring, $start.$substring.$end, $string);
}

?>