<?php

ClassLoader::import('application.helper.CreateHandleString');

/**
 *  Return an alternative value if the primary value is empty.
 *
 *  This is useful for short-hand syntax in templates where it is necessary
 *	to display a value that may possibly be stored in different variables.
 *
 *  For example:
 *  {$product.shortDescription|@or:$product.longDescription}
 *
 *  @package application.helper.smarty
 *  @author Integry Systems
 */
function smarty_modifier_handle($string)
{
	return CreateHandleString($string);
}

?>