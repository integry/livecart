<?php

/**
 * Smarty form helper
 *
 * <code>
 * </code>
 *
 * @package application.helper.smarty.form
 * @author Integry Systems
 *
 * @todo Include javascript validator source
 */
function smarty_block_form(&$params, $content, $smarty, &$repeat)
{
	if ($repeat)
	{
		// Check permissions
		$params['readonly'] = false;
		if(isset($params['readonly']) && $params['readonly'])
		{
				$params['class'] .= ' formReadonly';
				$params['readonly'] = true;
		}
		else
		{
			if(isset($params['role']))
			{	
				ClassLoader::import('application.helper.AccessStringParser');
				if(!AccessStringParser::run($params['role']))
				{
					if(!isset($params['class']))
					{
						$params['class'] = '';
					} 
					
					$params['class'] .= ' formReadonly';
					$params['readonly'] = true;
				}
			}
		}
	}
	else
	{
		$formHandler = $params['handle'];
		$formAction = $params['action'];
		$role = isset($params['role']) ? $params['role'] : false;
		
		unset($params['handle']);
		unset($params['role']);
		unset($params['action']);
			

		if (!empty($params['url']))
		{
			$actionURL = $params['url'];
			unset($params['url']);
		}	
		elseif ('self' == $formAction)
		{
			$actionURL = $_SERVER['REQUEST_URI'];
		}
		else
		{
			$vars = explode(" ", $formAction);
			$URLVars = array();
		
			foreach ($vars as $var)
			{
				$parts = explode("=", $var, 2);
				$URLVars[$parts[0]] = $parts[1];
			}
		
			try
			{
				$actionURL = $smarty->getApplication()->getRouter()->createURL($URLVars, true);
			}
			catch (RouterException $e)
			{
				$actionURL = "INVALID_FORM_ACTION_URL";
			}
		}
		
		if (!empty($params['onsubmit']))
		{
			$customOnSubmit = $params['onsubmit'];
			unset($params['onsubmit']);
		}
	
		$onSubmit = "";
		$validatorField = "";
		$preValidate = "";
		
		if (isset($params['prevalidate']))
		{	  
			$preValidate = $params['prevalidate'] . '; ';
			unset($params['prevalidate']);
		}
		
		if ($formHandler->isClientSideValidationEnabled())
		{
			if (!empty($customOnSubmit))
			{
				$onSubmit = $preValidate . 'if (!validateForm(this)) { return false; } ' . $customOnSubmit;
			}
			else
			{
				$onSubmit = 'return validateForm(this);';
			}		
			
			$validatorField = '<input type="hidden" disabled="disabled" name="_validator" value="' . $formHandler->getValidator()->getJSValidatorParams() . '"/>';
			$filterField = '<input type="hidden" disabled="disabled" name="_filter" value="' . $formHandler->getValidator()->getJSFilterParams() . '"/>';
		
			$params['onkeyup'] = 'applyFilters(this, event);';
		}
		else
		{
			$onSubmit = $customOnSubmit;
		}
	
		if ($onSubmit)
		{
			$params['onsubmit'] = $onSubmit;
		}
			
		// pass URL query parameters with hidden fields for GET forms
		if (empty($params['method']) || strtolower($params['method']) == 'get')
		{
			if (strpos($actionURL, '?'))
			{
				$q = substr($actionURL, strpos($actionURL, '?') + 1);
				$actionURL = substr($actionURL, 0, strpos($actionURL, '?'));
			}
			
			if (!empty($q))
			{
				$pairs = explode('&', $q);
				$values = array();
				foreach ($pairs as $pair)
				{
					list($key, $value) = explode('=', $pair, 2);
					$values[$key] = $value;
				}
	
				$hidden = array();
				foreach ($values as $key => $value)
				{
					$hidden[] = '<input type="hidden" name="' . $key . '" value="' . $value . '" />';				
				}
	
				$content = implode("\n", $hidden) . $content;
			}				
		}
	
		if (empty($params['method']))
		{
			$params['method'] = 'get';	
		}
		else
		{
			$params['method'] = strtolower($params['method']);
		}
	
		$formAttributes = "";
		unset($params['readonly']);
		foreach ($params as $param => $value)
		{
			$formAttributes .= $param . '="' . $value . '" ';
		}
	
		$form = '<form action="'.$actionURL.'" '.$formAttributes.'>' . "\n";
		$form .= $validatorField;
		$form .= $filterField;
		$form .= $content;
		$form .= "</form>";
		
		return $form;
	}
}

?>