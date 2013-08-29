<?php

/**
 * Smarty form helper
 *
 * <code>
 * </code>
 *
 * @package application/helper/smarty/form
 * @author Integry Systems
 *
 * @todo Include javascript validator source
 */
function smarty_block_form($params, $content, $smarty, &$repeat)
{
	$params = $smarty->filterParams($params);

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

		// disable default validation when using Angular
		if (!empty($params['model']))
		{
			$formHandler->enableClientSideValidation(false);

			if (empty($params['name']))
			{
				$params['name'] = 'form';
			}

			$params['ng-init'] = 'isSubmitted=0';
		}

		$formAction = $params['action'];
		$role = isset($params['role']) ? $params['role'] : false;

		unset($params['handle']);
		unset($params['role']);
		unset($params['action']);

		$params = array_merge($params, $formHandler->getParams());

		if (!empty($params['url']))
		{
			$actionURL = $params['url'];
			unset($params['url']);
		}
		else if ($formAction && ('self' != $formAction))
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
		else if ('self' == $formAction)
		{
			$actionURL = urldecode($_SERVER['REQUEST_URI']);
		}

		if (!empty($params['onsubmit']))
		{
			$customOnSubmit = $params['onsubmit'];
			unset($params['onsubmit']);
		}
		else
		{
			$customOnSubmit = '';
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
				$onSubmit = $preValidate . 'if (!validateForm(this, event)) { return false; } ' . $customOnSubmit;
			}
			else
			{
				$onSubmit = 'return validateForm(this, event);';
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

		if (empty($params['class']))
		{
			$params['class'] = '';
		}

		if (strpos($params['class'], 'form-') === false)
		{
			$params['class'] .= ' form-horizontal';
			$params['class'] = trim($params['class']);
		}

		if (empty($params['class']))
		{
			$params['class'] = '';
		}

		if (strpos($params['class'], 'form-') === false)
		{
			$params['class'] .= ' form-horizontal';
		}

		$formAttributes = "";
		unset($params['readonly']);

		if ($actionURL)
		{
			$params['action'] = $actionURL;
		}

		foreach ($params as $param => $value)
		{
			$formAttributes .= $param . '="' . $value . '" ';
		}

		$form = '<form '.$formAttributes.' novalidate>' . "\n";
		$form .= $validatorField;
		$form .= isset($filterField) ? $filterField : '';
		$form .= $content;
		$form .= '</form>';

		return $form;
	}
}

?>
