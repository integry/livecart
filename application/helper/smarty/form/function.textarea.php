<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty.form
 * @author Integry Systems
 */
function smarty_function_textarea($params, $smarty)
{
	if (empty($params['name']))
	{
		$params['name'] = $smarty->getTemplateVars('input_name');
	}

	// @todo: can be removed when all TinyMCE editors are instantiated via Angular
	if (empty($params['id']) && empty($params['tinymce']))
	{
		$params['id'] = uniqid();
	}

	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	$fieldName = $params['name'];

	if (!empty($formParams['model']))
	{
		$params['ng-model'] = $formParams['model'] . '.' . $params['name'];
	}

	$params = $smarty->applyFieldValidation($params, $formHandler);

	if (!empty($params['tinymce']))
	{
		if (is_bool($params['tinymce']))
		{
			$params['tinymce'] = 'getTinyMceOpts()';
		}

		$params['ui-tinymce'] = $params['tinymce'];
		unset($params['tinymce']);
	}

	// Check permissions
	if($formParams['readonly'])
	{
		$params['readonly'] = 'readonly';
	}

	$content = '<div class="controls"><textarea';
	foreach ($params as $name => $param) {
		$content .= ' ' . $name . '="' . $param . '"';
	}

	$content .= '>' . htmlspecialchars($formHandler->get($fieldName), ENT_QUOTES, 'UTF-8') . '</textarea></div>';

	return $content;
}

?>