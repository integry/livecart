<?php

/**
 *
 *
 *  @package application.helper.smarty
 *  @author Integry Systems
 */
function smarty_modifier_str_pad_left($string, $count, $pad = ' ')
{
	return str_pad_left($string, $count, $pad);
}

function str_pad_left($string, $count, $pad = ' ')
{
	return iconv_str_pad($string, $count, $pad, STR_PAD_LEFT);
}

function iconv_str_pad( $input, $pad_length, $pad_string = '', $pad_type = 1, $charset = "UTF-8" )
{
   $str = '';
//       $length = $pad_length - iconv_strlen( $input, $charset );
	$length = $pad_length - preg_match_all('/./u', $input, $dummy);
   if( $length > 0)
   {
	   if( $pad_type == STR_PAD_RIGHT )
	   {
		   $str = $input . str_repeat( $pad_string, $length );
	   } elseif( $pad_type == STR_PAD_LEFT )
	   {
		   $str = str_repeat( $pad_string, $length ) . $input;
	   } elseif( $pad_type == STR_PAD_BOTH )
	   {
		   $str = str_repeat( $pad_string, floor( $length / 2 ));
		   $str .= $input;
		   $str .= str_repeat( $pad_string, ceil( $length / 2 ));
	   } else
	   {
		   $str = str_repeat( $pad_string, $length ) . $input;
	   }
   } else
   {
	   $str = $input;
   }

   return $str;
}

?>