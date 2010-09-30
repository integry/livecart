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
function smarty_function_toolTip($params, LiveCartSmarty $smarty)
{
	$tip = $params['label'];
	$hint = !empty($params['hint']) ? $params['hint'] : '_tip' . $tip;
	$app = $smarty->getApplication();

	return '<span class="acronym" onmouseover=\'tooltip.show(' . json_encode($app->translate($hint)). ', 200);\' onmouseout="tooltip.hide();">' . $app->translate($tip) . '</span>';
}

?>