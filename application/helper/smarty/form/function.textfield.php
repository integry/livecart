<?php

/**
 * Renders text field
 *
 * If you wish to use autocomplete on a text field an additional parameter needs to be passed:
 *	
 * <code>
 *	  autocomplete="controller=somecontroller field=fieldname"
 * </code>
 *
 * The controller needs to implement an autoComplete method, which must return the AutoCompleteResponse 
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper.smarty.form
 * @author Integry Systems
 */
function smarty_function_textfield($params, LiveCartSmarty $smarty) 
{
	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	$fieldName = $params['name'];
	if (!($formHandler instanceof Form))
	{
		throw new HelperException('Element must be placed in {form} block');
	}

    
    // this should never be done. ID is should always be unique value. 
    // Doing so breaks lots of javascript traversing. It misleads prototype
    // And I found it also introduces some errors in tinyMCE. If you
    // realy want such functionality come up with some clever way to generate
    // unique id.
//  if (!isset($params['id']))
//  {
//      $params['id'] = $params['name'];
//  }
	
	if (!isset($params['type']))
	{
		$params['type'] = 'text';
	}
	
	// Check permissions
	if($formParams['readonly'])
	{	
	    $params['readonly'] = 'readonly'; 
	}

    if(!isset($params['autocomplete']))
    {   
        $params['autocomplete'] = 'off'; 
    }
	
	
	$content = '<input';
	foreach ($params as $name => $param) {
		$content .= ' ' . $name . '="' . $param . '"'; 
	}

    $content .= ' value="' . htmlspecialchars($formHandler->get($fieldName), ENT_QUOTES, 'UTF-8') . '"';
	$content .= '/>';
	if (isset($params['autocomplete']) && $params['autocomplete'] != 'off')
	{
	  	$acparams = array();
		foreach (explode(' ', $params['autocomplete']) as $param)
	  	{
			list($p, $v) = explode('=', $param, 2);
			$acparams[$p] = $v;
		}
		 
		$url = $smarty->getApplication()->getRouter()->createURL(array('controller' => $acparams['controller'], 
													  'action' => 'autoComplete', 
													  'query' => 'field=' . $acparams['field']));
		  
		$content .= '<span id="autocomplete_indicator_' . $params['id'] . '" class="progressIndicator" style="display: none;"></span>';
		$content .= '<div id="autocomplete_' . $params['id'] . '" class="autocomplete"></div>';
		$content .= '<script type="text/javascript">
						new Ajax.Autocompleter("' . $params['id'] . '", "autocomplete_' . $params['id'] . '", "' . $url . '", {frequency: 0.2, paramName: "' . $acparams['field'] . '", indicator: "autocomplete_indicator_' . $params['id'] . '"});
					</script>';
	}
	
	return $content;
}

?>