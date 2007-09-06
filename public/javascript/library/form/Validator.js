function validateForm(form)
{
	Element.saveTinyMceFields(form);
    ActiveForm.prototype.resetErrorMessages(form);

    var validatorData = form._validator.value;
	var validator = validatorData.evalJSON();   
    var isFormValid = true;
    var focus = true;

	$H(validator).each(function(field)
	{
		if (!form[field.key]) return;
        
		var formElement = form[field.key];
        $H(field.value).each(function(func) 
		{                
			if (window[func.key] && !window[func.key](formElement, func.value.param)) // If element is not valid
			{
                // radio button group
                if (!formElement.parentNode && formElement.length)
                {
                    formElement = formElement[formElement.length - 1];
                }
                
                ActiveForm.prototype.setErrorMessage(formElement, func.value.error, focus);
				isFormValid = false;
                focus = false;
			}
	    });
	});
    
	return isFormValid;
}

function applyFilters(form, ev)
{	
    if(!ev || !ev.target) 
    { 
        ev = window.event; 
        ev.target = ev.srcElement;
    }

	var filterData = form.elements.namedItem('_filter').value;
	var filter = filterData.evalJSON();

	element = ev.target;	
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

function MinLengthCheck(element, params)
{
	return (element.value.length >= params.minLength);
}

function PasswordEqualityCheck(element, params)
{
    return (element.value == element.form.elements.namedItem(params.secondPasswordFieldname).value);
}

function MaxLengthCheck(element, params)
{
	return (element.value.length <= params.maxLength);
}

function IsValidEmailCheck(element, params)
{
	re = new RegExp(/^[a-zA-Z0-9][a-zA-Z0-9\._\-]+@[a-zA-Z0-9_\-][a-zA-Z0-9\._\-]+\.[a-zA-Z]{2,}$/);
	return (re.exec(element.value));
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

/*********************************************
	Filters
*********************************************/
function NumericFilter(elm, params)
{
    elm.focus();
    
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
    element.focus();
    
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