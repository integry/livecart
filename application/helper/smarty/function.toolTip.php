<?php
/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application/helper/smarty
 * @author Integry Systems
 */
function smarty_function_toolTip($params, Smarty_Internal_Template $smarty)
{
	$tip = $params['label'];
	$hint = !empty($params['hint']) ? $params['hint'] : '_tip' . $tip;
	$app = $smarty->getApplication();

	$json = json_encode($app->translate($hint));
	$json = str_replace('<', '\u003C', $json);
	$json = str_replace('>', '\u003E', $json);
	$json = str_replace("'", '\u0027', $json);

	return '<span class="acronym" onmouseover=\'tooltip.show(' . $json. ', 200);\' onmouseout="tooltip.hide();">' . $app->translate($tip) . '</span>';
}

?>
