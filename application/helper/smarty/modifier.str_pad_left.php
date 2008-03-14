<?php

/**
 *
 *
 *  @package application.helper.smarty
 *  @author Integry Systems
 */
function smarty_modifier_str_pad_left($string, $count)
{
	return str_pad_left($string, $count);
}

function str_pad_left($string, $count)
{
	return str_pad($string, $count, ' ', STR_PAD_LEFT);
}

?>