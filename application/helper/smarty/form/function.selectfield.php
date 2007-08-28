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
	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	
	$options = $params['options'];
	if (empty($options))
	{
		$options = array();
	}
	unset($params['options']);

	$defaultValue = $params['value'];
	unset($params['value']);
	
	if (!isset($params['id']))
	{
	  	$params['id'] = $params['name'];
	}
	
	// Check permissions
	if($formParams['readonly'])
	{
       $params['disabled'] = 'disabled'; 
	}
	
	
	$content = '<select';
	foreach ($params as $name => $param) {
		$content .= ' ' . $name . '="' . $param . '"'; 
	}
	$content .= ">\n";
	$fieldValue = $formHandler->get($params['name']);
	if (is_null($fieldValue))
	{
        $fieldValue = $defaultValue;
	}
	
	foreach ($options as $value => $title)
	{
	    if(preg_match('/optgroup_\d+/', $value))
	    {
	        $content .= "\t" . '<optgroup label="' . htmlspecialchars($title) . '" />' . "\n";
	    }
	    else
	    {
			if ($fieldValue == $value && (strlen($fieldValue) == strlen($value)))
			{
				$content .= "\t" . '<option value="' . $value . '" selected="selected">' . htmlspecialchars($title)  . '</option>' . "\n";
			}
			else
			{
				$content .= "\t" . '<option value="' . $value . '">' . htmlspecialchars($title) . '</option>' . "\n";
			}
	    }
	}
	$content .= "</select>";
	
	return $content;
}

?>