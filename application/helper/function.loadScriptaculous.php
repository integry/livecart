<?php

/**
 * Load JS files needed to initialize Scriptaculous
 *
 * Usage example:
 * <code>
 *	{loadScriptaculous}
 * </code>
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 * @author Rinalds Uzkalns <rinalds@integry.net>
 */
function smarty_function_loadScriptaculous($params, $smarty) 
{
	require_once('function.includeJs.php');
	smarty_function_includeJs(array('file' => 'library/scriptaculous/scriptaculous.js'), $smarty);
}

?>