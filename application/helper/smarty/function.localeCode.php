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
function smarty_function_localeCode($params, LiveCartSmarty $smarty)
{
	return $smarty->getApplication()->getLocale()->getLocaleCode();
}

?>