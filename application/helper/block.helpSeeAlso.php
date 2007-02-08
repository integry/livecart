<?php

/**
 * Smarty block plugin, for generating help reference sections
 *
 * @param array $params
 * @param Smarty $smarty
 * @param $repeat
 *
 * <code>
 *	{helpSeeAlso}
 *		{see language.edit}
 *  {/helpSeeAlso}
 * </code>
 *
 * @return string HTML code
 * @package application.helper
 */
function smarty_block_helpSeeAlso($params, $content, Smarty $smarty, &$repeat) 
{	
	if (!$repeat) 
	{		
		return '<div class="seeAlso"><fieldset><legend>See also</legend><ul>' . $content . '</ul></fieldset></div>';
	}
	
}

?>