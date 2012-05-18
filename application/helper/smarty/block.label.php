<?php

/**
 * Form field row
 *
 * @package application.helper.smarty
 * @author Integry Systems
 *
 * @package application.helper.smarty
 */
function smarty_block_label($params, $content, Smarty_Internal_Template $smarty, &$repeat)
{
	if (!$repeat)
	{
		$fieldType = $smarty->getTemplateVars('last_fieldType');

		if ('checkbox' == $fieldType)
		{
			$label = '<label class="' . $fieldType . '" for="' . $smarty->getTemplateVars('last_fieldID') . '">';
		}
		else
		{
			$label = '<label class="' . $fieldType . '">';
		}

		$content = $label . $content . '</label>';

		return $content;
	}
}
?>