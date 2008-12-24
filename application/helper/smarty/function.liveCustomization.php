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
function smarty_function_liveCustomization($params, LiveCartSmarty $smarty)
{
	$app = $smarty->getApplication();
	if ($app->isCustomizationMode())
	{
		if (!isset($params['action']))
		{
			include_once('function.includeJs.php');
			include_once('function.includeCss.php');

			smarty_function_includeJs(array('file' => "library/prototype/prototype.js"), $smarty);
			smarty_function_includeJs(array('file' => "library/scriptaculous/scriptaculous.js"), $smarty);
			smarty_function_includeJs(array('file' => "library/livecart.js"), $smarty);
			smarty_function_includeJs(array('file' => "backend/Backend.js"), $smarty);
			smarty_function_includeJs(array('file' => "frontend/Customize.js"), $smarty);

			smarty_function_includeCss(array('file' => "frontend/LiveCustomization.css"), $smarty);
		}
		else
		{
			$smarty->assign('mode', $app->getCustomizationModeType());
			$smarty->assign('theme', $app->getTheme());
			return $smarty->fetch('customize/menu.tpl');
		}
	}
}

?>