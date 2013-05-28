<?php

/**
 * Renders and displays a page content block
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_renderBlock($params, Smarty_Internal_Template $smarty)
{
	$block = $params['block'];
	if (substr($block, -1) == '}')
	{
		$block = substr($block, 0, -1);
	}

	return $smarty->getApplication()->getBlockContent($block, $params);
}

?>