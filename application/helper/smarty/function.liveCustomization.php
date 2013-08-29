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
function smarty_function_liveCustomization($params, Smarty_Internal_Template $smarty)
{
	$app = $smarty->getApplication();
	if ($app->isCustomizationMode())
	{
		// theme dropdown
		$themes = array_merge(array('barebone' => 'barebone'), array_diff($app->getRenderer()->getThemeList(), array('barebone')));
		$smarty->assign('themes', $themes);
		$smarty->assign('currentTheme', $app->getTheme());

		if (!isset($params['action']))
		{
			include_once('function.includeJs.php');
			include_once('function.includeCss.php');

			smarty_function_includeJs(array('file' => "library/prototype/prototype.js"), $smarty);
			smarty_function_includeJs(array('file' => "library/livecart.js"), $smarty);
			smarty_function_includeJs(array('file' => "library/form/ActiveForm.js"), $smarty);
			smarty_function_includeJs(array('file' => "library/form/Validator.js"), $smarty);
			smarty_function_includeJs(array('file' => "backend/Backend.js"), $smarty);
			smarty_function_includeJs(array('file' => "frontend/Customize.js"), $smarty);

			smarty_function_includeCss(array('file' => "frontend/LiveCustomization.css"), $smarty);
		}
		else
		{
			$smarty->assign('mode', $app->getCustomizationModeType());
			$smarty->assign('theme', $app->getTheme());

			if ('menu' == $params['action'])
			{
				return $smarty->fetch('customize/menu.tpl');
			}
			else if ('lang' == $params['action'])
			{
				return $smarty->fetch('customize/translate.tpl');
			}
		}
	}
}

?>