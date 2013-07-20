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
function smarty_function_selectfield($params, $smarty)
{
	if (empty($params['name']))
	{
		$params['name'] = $smarty->getTemplateVars('input_name');
	}

	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];

	if (!empty($formParams['model']))
	{
		$params['ng-model'] = $formParams['model'] . '.' . $params['name'];
	}

	$options = $params['options'];
	if (empty($options))
	{
		$options = array();
	}
	unset($params['options']);

	$before = isset($params['before']) ? $params['before'] : '';
	$after = isset($params['after']) ? $params['after'] : '';

	$defaultValue = isset($params['value']) ? $params['value'] : '';

	unset($params['value'], $params['before'], $params['after']);

	// Check permissions
	if($formParams['readonly'])
	{
	   $params['disabled'] = 'disabled';
	}

	if ($formHandler)
	{
		$fieldValue = $formHandler->get($params['name']);
		if (is_null($fieldValue))
		{
			$fieldValue = $defaultValue;
		}

		$params['initialValue'] = $fieldValue;
	}

	$content = '<select';
	$content = $smarty->appendParams($content, $params);
	$content .= ">\n";

	if (isset($params['blank']))
	{
		$content .= '<option></option>';
	}

	$content .= $before;

	foreach ($options as $value => $title)
	{
		if(preg_match('/optgroup_\d+/', $value))
		{
			$content .= "\t" . '<optgroup label="' . htmlspecialchars($title) . '" />' . "\n";
		}
		else
		{
			if ((is_array($fieldValue) && in_array($value, $fieldValue)) || (!is_array($fieldValue) && ($fieldValue == $value) && (strlen($fieldValue) == strlen($value))))
			{
				$content .= "\t" . '<option value="' . $value . '" selected="selected">' . htmlspecialchars($title)  . '</option>' . "\n";
			}
			else
			{
				$content .= "\t" . '<option value="' . $value . '">' . htmlspecialchars($title) . '</option>' . "\n";
			}
		}
	}

	$content .= $after . '</select>';

	$content = $smarty->formatControl($content, $params);

	return $content;
}

?>
