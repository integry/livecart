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

	$content = '';

	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	if (!isset($params['value']) && !($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}

	$fieldName = $params['name'];
	unset($params['name']);
	$application = $smarty->getApplication();

	if(!isset($params['class']))
	{
		$params['class'] = '';
	}

	$hideSwitch = false;
	if(isset($params['hideSwitch']))
	{
		unset($params['hideSwitch']);
		$hideSwitch = true;
	}

	$rootID = 'UnitConventer_Root_' . (isset($params['id']) ? $params['id'] : 'inc_' . rand(1, 10000000) . '_' . rand(1, 10000000));

	$content .= '<fieldset  id="' . $rootID . '" class="error UnitConventer_Root">';
	$content .= '	<span style="display: none">';
	$content .= '		<span class="UnitConventer_SwitcgToEnglishTitle">' . $application->translate('_switch_to_english_units') . '</span>';
	$content .= '		<span class="UnitConventer_SwitcgToMetricTitle">' . $application->translate('_switch_to_metric_units') . '</span>';
	$content .= '		<span class="UnitConventer_MetricHiUnit">' . $application->translate('_units_kg') . '</span>';
	$content .= '		<span class="UnitConventer_MetricLoUnit">' . $application->translate('_units_g') . '</span>';
	$content .= '		<span class="UnitConventer_EnglishHiUnit">' . $application->translate('_units_pounds') . '</span>';
	$content .= '		<span class="UnitConventer_EnglishLoUnit">' . $application->translate('_units_ounces') . '</span>';
	$content .= '	</span>';

	$content .= '<input type="hidden" name="' . $fieldName . '" value="' . (isset($params['value']) ? $params['value'] : $formHandler->get($fieldName)) . '"  class="UnitConventer_NormalizedWeight" />';
	$content .= '<input type="hidden" class="UnitConventer_UnitsType" value="' . $application->getConfig()->get('UNIT_SYSTEM') . '" />';

	unset($params['value']);

	// Hi value
	$hiParams = $params;
	$hiParams['class'] .= ' number UnitConventer_HiValue';
	if($hiParams['id'])
	{
		$hiParams['id'] = $hiParams['id'] . '_HiValue';
	}
	$content .= '<input type="text"';
	foreach ($hiParams as $name => $value) $content .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
	$content .= ' />';

	// Lo value
	$loParams = $params;
	$loParams['class'] .= ' number UnitConventer_LoValue';
	if($hiParams['id'])
	{
		$loParams['id'] = $loParams['id'] . '_HiValue';
	}
	$content .= '<input type="text"';
	foreach ($loParams as $name => $value) $content .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
	$content .= ' />';

	$content .= '<div class="errorText hidden"></div>';

	$content .= '   <a href="#" class="UnitConventer_SwitchUnits" ' . ($hideSwitch ? 'style="display: none;"' : '') . '>' . $application->translate($application->getConfig()->get('UNIT_SYSTEM') == 'ENGLISH' ? '_switch_to_english_units' : '_switch_to_metric_units') . '</a>';
	$content .= '   <script type="text/javascript">Backend.UnitConventer.prototype.getInstance("' . $rootID . '");</script>';
	$content .= '</fieldset >';

	return $content;
}
?>