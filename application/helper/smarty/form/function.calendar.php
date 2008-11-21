<?php
/**
 * ...
 *
 * @param array $params
 * 			(string)id => Field id (This field is required)
 *		  (string)format => Date format (default: %d-%b-%Y)
 *		  (bool)noform => Sometimes calendar must be put not inside the form, or dinamically. You should pass noForm=true if you don't want to depend on form
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty.form
 * @author Integry Systems
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

	$output .= '<input type="hidden" class="hidden" class="calendar" name="'.$fieldName.'" value="'.$value.'" class="calendar-real" id="'.$params['id'].'_real" />';
	$output .= '<img src="image/silk/calendar.png" id="'.$params['id'].'_button" class="calendar_button" title="Date selector" onmouseover="Element.addClassName(this, \'calendar_button_hover\');" onmouseout="Element.removeClassName(this, \'calendar_button_hover\');" />';
	$output .= <<<JAVASCRIPT
<script type="text/javascript">
	var visible = $("{$params['id']}");
	var button = $("{$params['id']}_button");
	var realInput = $(button.parentNode).down('.hidden');
	button.realInput = realInput;

	button.showInput = visible;
	visible.realInput = realInput;
	visible.showInput = visible;

	var updateDate = function(e)
	{
		Calendar.updateDate.bind(this)();
		Event.stop(e);
	};

	Event.observe(visible, "dbclick", updateDate.bind(visible));
	Event.observe(visible, "keyup",	 updateDate.bind(visible));
	Event.observe(visible, "blur",	updateDate.bind(visible));
	Event.observe(button, "mousedown", updateDate.bind(button));
	Event.observe(button, "click", updateDate.bind(button));
	Event.observe(visible, "blur", function() { if (!this.value) { this.realInput.value = ''; } });

	window.setTimeout(function()
		{
			Calendar.setup({
				inputField:	 "{$params['id']}",
				inputFieldReal: "{$params['id']}_real",
				ifFormat:	   "{$format}",
				button:		 "{$params['id']}_button",
				align:		  "BR",
				singleClick:	true
			});
		}, 200);

	visible.ondblclick = button.onclick;
</script>
JAVASCRIPT;

	return $output;
}

?>