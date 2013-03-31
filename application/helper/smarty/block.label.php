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
		$class = $smarty->getTemplateVars('last_fieldType') . ' ' . $params['class'];

		$for = $smarty->getTemplateVars('last_fieldID');
		if (!empty($params['for']))
		{
			$for = $params['for'];
		}

		$label = '<label class="' . $class . '" for="' . $for . '">';

		$content = $label . $content . '</label>';

		return $content;
	}
}
?>