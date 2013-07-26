<?php
/**
 *
 * @package application.helper.smarty.form
 * @author Integry Systems
 */
function smarty_function_metricsfield($params, Smarty_Internal_Template $smarty)
{
	if (empty($params['name']))
	{
		$params['name'] = $smarty->getTemplateVars('input_name');
	}

	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	if (!isset($params['value']) && !($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}

	if (!empty($formParams['model']))
	{
		$params['ng-model'] = $formParams['model'] . '.' . $params['name'];
	}

	$application = $smarty->getApplication();
	$params['m_sw'] = $application->translate('_switch_to_english_units');
	$params['en_sw'] = $application->translate('_switch_to_metric_units');
	$params['m_hi'] = $application->translate('_units_kg');
	$params['m_lo'] = $application->translate('_units_g');
	$params['en_hi'] = $application->translate('_units_lbs');
	$params['en_lo'] = $application->translate('_units_oz');
	$params['type'] = strtolower($application->getConfig()->get('UNIT_SYSTEM'));

	$content = '<weight-input ' . $smarty->appendParams($content, $params) .'></weight-input>';

	$content = $smarty->formatControl($content, $params);

	return $content;
}
?>