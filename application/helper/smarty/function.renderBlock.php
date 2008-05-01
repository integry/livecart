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
function smarty_function_renderBlock($params, LiveCartSmarty $smarty)
{
	//var_dump($smarty);
	if ($smarty->get_template_vars($params['block']))
	{
		var_dump($smarty->get_template_vars($params['block']));
	}
	return $smarty->getApplication()->getBlockContent($params['block']);
}

?>