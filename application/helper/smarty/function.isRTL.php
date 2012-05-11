<?php

/**
 * Returns current locale code
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_isRTL($params, Smarty_Internal_Template $smarty)
{
	$locale = $smarty->getApplication()->getLocale()->getLocaleCode();
	return in_array($locale, array('he', 'ar', 'fa'));
}

?>