<?php

/**
 * Form field row
 *
 * @package application.helper.smarty
 * @author Integry Systems
 *
 * @package application.helper.smarty
 */
function smarty_block_input($params, $content, Smarty_Internal_Template $smarty, &$repeat)
{
	if (!$repeat)
	{
		$formParams = $smarty->_tag_stack[0][1];
		$formHandler = $formParams['handle'];
		$isRequired = $formHandler ? $formHandler->isRequired($params['name']) : false;

		$fieldType = $smarty->getTemplateVars('last_fieldType');

		$content = '<div class="input ' . $fieldType . ' ' . ($isRequired ? ' required' : '') . (!empty($params['class']) ? ' ' . $params['class'] : '' ) . '">' .
						$content .
						'<div class="errorText hidden"></div>
					</div>';

		$smarty->assign('last_fieldType', '');

		return $content;
	}
	else
	{
		$smarty->assign('last_fieldType', '');
		$smarty->assign('input_name', $params['name']);
	}
}
?>