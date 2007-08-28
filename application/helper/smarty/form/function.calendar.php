<?php
/**
 * ...
 *
 * @param array $params 
 * 			(string)id => Field id (This field is required)
 *          (string)format => Date format (default: %d-%b-%Y)
 *          (bool)noform => Sometimes calendar must be put not inside the form, or dinamically. You should pass noForm=true if you don't want to depend on form
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper.smarty.form
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_function_calendar($params, $smarty) 
{    
    if(!isset($params['noform']))
    {
    	$formParams = $smarty->_tag_stack[0][1];
    	$formHandler = $formParams['handle'];
    	if (!($formHandler instanceof Form))
    	{
    		throw new HelperException('Element must be placed in {form} block');
    	}
    	$fieldName = $params['name'];
    }
	
    if(!isset($params['id']))
    {
        throw new HelperException('Calendar input field should have an ID. (Paramater name - "id")');
    }
    
    $params['format'] = isset($params['format']) ? $params['format'] : "%d-%b-%Y";
	$format = $params['format'];
	unset($params['format']);
    	
	if(isset($params['noform']))
    {
        $value = $params['value'];
        $fieldName = $params['name'];
    }
    else
    {
        $value = $formHandler->get($fieldName);
    }
    unset($params['noform']);
    unset($params['value']);
    unset($params['name']);
    
	$params['class'] = !isset($params['class']) ? 'date' : $params['class']. ' date';
	
	$output  = '<input type="text" value="'.$value.'" name="'.$params['id'].'_visible" '; 
	foreach ($params as $n => $v)  
	    $output .= ' ' . $n . '="' . $v . '"'; 
	$output .= "/>";
	
	$output .= '<input type="hidden" class="hidden" class="calendar" name="'.$fieldName.'" value="'.$value.'" id="'.$params['id'].'_real" />';
	$output .= '<img src="image/silk/calendar.png" id="'.$params['id'].'_button" class="calendar_button" title="Date selector" onmouseover="Element.addClassName(this, \'calendar_button_hover\');" onmouseout="Element.removeClassName(this, \'calendar_button_hover\');" />';
	$output .= <<<JAVASCRIPT
<script type="text/javascript">
	$("{$params['id']}_button").realInput = $("{$params['id']}_real");
	$("{$params['id']}_button").showInput = $("{$params['id']}");
	$("{$params['id']}").realInput = $("{$params['id']}_real");
	$("{$params['id']}").showInput = $("{$params['id']}");

    Event.observe($("{$params['id']}"),        "keyup",     Calendar.updateDate );
    Event.observe($("{$params['id']}"),        "blur",      Calendar.updateDate );
    Event.observe($("{$params['id']}_button"), "mousedown", Calendar.updateDate );

    Calendar.setup({
        inputField:     "{$params['id']}",
        inputFieldReal: "{$params['id']}_real",    
        ifFormat:       "{$format}",
        button:         "{$params['id']}_button",
        align:          "BR",
        singleClick:    true
    });
</script>
JAVASCRIPT;

	return $output;
}

?>