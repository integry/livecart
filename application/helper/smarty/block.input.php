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

		if ($formHandler && $formHandler->getValidator())
		{
			$err = $formHandler->getValidator()->getErrorList();
			$msg = empty($err[$params['name']]) ? '' : $err[$params['name']];
		}
		else
		{
			$msg = '';
		}

		if ('checkbox' == $fieldType)
		{
			preg_match('/<input(.*) \/\>(.*)\<label(.*)\>(.*)\<\/label\>/msU', $content, $matches);
			if ($matches)
			{
				$content = '<label ' . $matches[3] . '><input ' . $matches[1] . ' /> ' . $matches[4] . '</label>';
			}

			//var_dump($matches);
		}

		$content = '<div class="control-group ' . ($msg ? 'has-error' : '') .  ' name_' . $params['name'] . ' ' . $fieldType . ' ' . ($isRequired ? ' required' : '') . (!empty($params['class']) ? ' ' . $params['class'] : '' ) . '">' .
						$content .
						'<div class="text-danger' . ($msg ? '' : ' hidden') . '">' . $msg . '</div>
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