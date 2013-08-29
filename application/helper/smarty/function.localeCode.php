<?php

/**
 * Returns current locale code
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application/helper/smarty
 * @author Integry Systems
 */
function smarty_function_localeCode($params, Smarty_Internal_Template $smarty)
{
	return $smarty->getApplication()->getLocale()->getLocaleCode();
}

?>