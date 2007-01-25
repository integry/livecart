<?php

/**
 * Returns current locale code
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_localeCode($params, $smarty)
{
	return Store::getInstance()->getLocaleInstance()->getLocaleCode();
}

?>