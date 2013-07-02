/**
 *	@author Integry Systems
 */

function validateForm(form, event)
{
	Element.saveTinyMceFields(form);
	ActiveForm.prototype.resetErrorMessages(form);

	var isFormValid = true;
	var focusField = true;

	if (!form)
	{
		return isFormValid;
	}

	$H(jQuery(form._validator).val().evalJSON()).each(function(checks)
	{
		var formElement = form[checks.key];
		if (!formElement) return;

		$H(checks.value).each(function(formElement, check)
		{
			if (window[check.key] && !window[check.key](formElement, check.value.param)) // If element is not valid
			{
				// radio button group
				if (!formElement.parentNode && formElement.length)
				{
					formElement = formElement[formElement.length - 1];
				}

				ActiveForm.prototype.setErrorMessage(formElement, check.value.error, focusField);
				isFormValid = false;
				focusField = false;
				throw $break;
			}
		}.bind(this, formElement));
	}.bind(this));

	if (isFormValid)
	{
		var parentForm = jQuery(form.parentNode).closest('form')[0];
		if (parentForm)
		{
			isFormValid = validateForm(parentForm);
		}
	}

	if (!isFormValid && event)
	{
		event.stopPropagation();
		console.log('stopping');
	}

	return isFormValid;
}

function applyFilters(form, ev)
{
	if(!ev || !ev.target)
	{
		ev = window.event;
		ev.target = ev.srcElement;
	}

	var filter = jQuery(form._filter).val().evalJSON();

	var element = ev.target;
	elementFilters = filter[element.name];

	if ('undefined' == elementFilters)
	{
	  	return false;
	}

	for (k in elementFilters)
	{
		if (typeof elementFilters[k] == 'object' && window[k])
		{
			eval(k + '(element, elementFilters[k]);');
		}
	}
}

/*********************************************
	Checks (validators)
*********************************************/
function trim(strValue)
{
 	var objRegExp = /^(\s*)$/;
	//check for all spaces
	if(objRegExp.test(strValue))
	{
		strValue = strValue.replace(objRegExp, '');
	   	if( strValue.length == 0)
	   	{
			return strValue;
	   	}
	}
   	//check for leading & trailing spaces
   	objRegExp = /^(\s*)([\W\w]*)(\b\s*$)/;
   	if(objRegExp.test(strValue))
   	{
	   //remove leading and trailing whitespace characters
	   strValue = strValue.replace(objRegExp, '$2');
	}
  	return strValue;
}

function IsNotEmptyCheck(element, params)
{
	// radio buttons
	if (!element.parentNode && element.length)
	{
		for (k = 0; k < element.length; k++)
		{
			if (element[k].checked)
			{
				return true;
			}
		}
	}

	else
	{
		if (element.getAttribute("type") == "checkbox")
		{
			return element.checked;
		}

		return (element.value.length > 0);
	}
}

function IsEmptyCheck(element, params)
{
	return !IsNotEmptyCheck(element, params);
}

function MinLengthCheck(element, params)
{
	return (element.value.length >= params.minLength);
}

function IsLengthBetweenCheck(element, params)
{
	var len = element.value.length;
	return ((len >= params.minLength && len <= params.maxLength) || (len == 0 && params.allowEmpty));
}

function PasswordEqualityCheck(element, params)
{
	return (element.value == element.form.elements.namedItem(params.secondPasswordFieldname).value);
}

function MaxLengthCheck(element, params)
{
	return (element.value.length <= params.maxLength);
}

function IsValidEmailCheck(el, params)
{
	var pattern = /^[a-zA-Z0-9][a-zA-Z0-9\._\-]+@[a-zA-Z0-9_\-][a-zA-Z0-9\._\-]+\.[a-zA-Z]{2,}$/;
	return el.value.match(pattern);
}

function IsValueInSetCheck(element, params)
{

}

function IsNumericCheck(element, constraint)
{
  	if (element.value == '')
  	{
  		return true;
  	}
	re = new RegExp(/(^-?\d+\.\d+$)|(^-?\d+$)|(^-?\.\d+$)/);
	return(re.exec(element.value));
}

function IsIntegerCheck(element, constraint)
{
  	if (constraint.letEmptyString && element.value == '')
  	{
  		return true;
  	}
	re = new RegExp(/^-?\d+$/);
	return(re.exec(element.value));
}

function MinValueCheck(element, constraint)
{
  	return element.value >= constraint.minValue || element.value == '';
}

function MaxValueCheck(element, constraint)
{
  	return element.value <= constraint.maxValue || element.value == '';
}

function IsEqualCheck(element, params)
{
	return (element.value == params.value);
}

function IsNotEqualCheck(element, params)
{
	return (element.value != params.value);
}

function IsFileTypeValidCheck(element, params)
{
	if (!element.value)
	{
		return true;
	}

	var ext = element.value.split(/\./).pop().toLowerCase();
	return params.extensions.indexOf(ext) > -1;
}

function IsFileUploadedCheck(element, params)
{
	if (element.value.length > 0)
	{
		return true;
	}
}

function NotPastDateCheck(element, params)
{
	var today = new Date();
	today.setHours(0,0,0,0)

	var chosenDate = jQuery(element).closest('div.date').data('datepicker').date;

	if (!chosenDate)
	{
		return true;
	}

	return chosenDate >= today;
}

function OrCheck(element, constraints)
{
	var form = element.form ? element.form : $A(element)[0].form;

	var pass = false;
	constraints.each(function(constraint)
	{
		if (!window[constraint[1]])
		{
			pass = true;
			return;
		}

		var valFunc = eval(constraint[1]);
		var el = form.elements.namedItem(constraint[0]);
		var params = constraint[2];

		if (el && valFunc(el, params))
		{
			pass = true;
		}
	});

	return pass;
}

/*********************************************
	Filters
*********************************************/
function NumericFilter(elm, params)
{
	var value = elm.value;

	// Remove leading zeros
	value = value.replace(/^0+/, '');
	if(!value) return;

	value = value.replace(',' , '.');

	// only keep the last comma
	parts = value.split('.');

	value = '';
	for (k = 0; k < parts.length; k++)
	{
		value += parts[k] + ((k == (parts.length - 2)) && (parts.length > 1) ? '.' : '');
	}

	// split digits and decimal part
	parts = value.split('.');

	// leading comma (for example: .5 converted to 0.5)
	if ('' == parts[0] && 2 == parts.length)
	{
	  	parts[0] = '0';
	}

	//next remove all characters save 0 though 9
	//in both elms of the array
	dollars = parts[0].replace(/^-?[^0-9]-/gi, '');

	if ('' != dollars && '-' != dollars)
	{
		dollars = parseInt(dollars);

		if(!dollars) dollars = 0;
	}

	if (2 == parts.length)
	{
		cents = parts[1].replace(/[^0-9]/gi, '');
		dollars += '.' + cents;
	}

	elm.value = dollars;
}

function IntegerFilter(element, params)
{
	element.value = element.value.replace(/[^\d]/, '');
	element.value = element.value.replace(/^0/, '');

	if(element.value == '')
	{
		element.value = 0;
	}
}

function RegexFilter(element, params)
{
	var regex = new RegExp(params['regex'], 'gi');
	element.value = element.value.replace(regex, '');
}
