<?php

include_once dirname(__file__) . '/modifier.str_pad_left.php';

/**
 *
 *
 *  @package application/helper/smarty
 *  @author Integry Systems
 */
function smarty_modifier_str_pad_iconv($string, $count, $pad = ' ', $pad_type = STR_PAD_RIGHT)
{
	return iconv_str_pad($string, $count, $pad, $pad_type);
}

?>