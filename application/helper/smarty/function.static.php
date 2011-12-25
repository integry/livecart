<?php
/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_static($params, LiveCartSmarty $smarty)
{
	return $smarty->getApplication()->getPublicUrl(array_pop($params));
}

?>