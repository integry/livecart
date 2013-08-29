<?php

/**
 * Form field row
 *
 * @package application/helper/smarty
 * @author Integry Systems
 *
 * @package application/helper/smarty
 */
function smarty_block_label($params, $content, Smarty_Internal_Template $smarty, &$repeat)
{
	if (!$repeat)
	{
		$class = $smarty->getTemplateVars('last_fieldType') . ' ' . $params['class'];

		if (strpos($class, 'checkbox') === false)
		{
			$class = 'control-label col-lg-2 ' . $class;
		}

		$for = empty($params['for']) ? '' : ' for="' . $params['for'] . '"';

		$content = '<label class="' . $class . '"' . $for . '>' . $content . '</label>';

		return $content;
	}
}
?>