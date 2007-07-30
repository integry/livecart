<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_function_weight($params, LiveCartSmarty $smarty)
{
    if(!isset($params['value']))
    {
        throw new ApplicationException("Please use 'value' attribute to specify weight");
    }
    
    $application = $smarty->getApplication();
    
    $units_hi = $application->translate($application->getConfig()->get('UNIT_SYSTEM') == 'ENGLISH' ? '_units_pounds' : '_units_kg');
    $units_lo = $application->translate($application->getConfig()->get('UNIT_SYSTEM') == 'ENGLISH' ? '_units_ounces' : '_units_g');

    $value_hi = (int)$params['value'];
    $value_lo = str_replace('0.', '', (string)($params['value'] - (int)$params['value']));
    
    return sprintf("%s %s %s %s", $value_hi, $units_hi, $value_lo, $units_lo);

}

?>