
function validateForm(form)
{
	window.event.preventDefault();
	
	var validatorData = form._validator.value;
	validator = validatorData.parseJSON();

	for (var fieldName in validator)
	{
		if (fieldName == "toJSONString")
		{
			continue;
		}
		var formElement = form[fieldName];
		for (var functionName in  validator[fieldName])
		{
			if (functionName == "toJSONString")
			{
				continue;
			}
			var params = validator[fieldName][functionName]['param'];
			var errorMsg = validator[fieldName][functionName]['error'];
			//alert(functionName + params + errorMsg);
			
			var functionExists = false;
			eval('functionExists = window.' + functionName + ';');
			if (!functionExists)
			{
				alert('No validation function defined: ' + functionName + '!');
				break;
			}
				
			var isFieldValid = false;
			eval('isFieldValid = ' + functionName + '(formElement, params);');
			if (!isFieldValid)
			{
				alert(errorMsg);
				formElement.focus();				
				return false;
			}
		}
	}
	
	form.submit();
}

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

// Filter functions
function TrimFilter(element)
{  
  	element.value = trim(element.value);	
}

function NumericFilter(element)
{
  	element.value = trim(element.value.replace(",", "."));  	
}

// Validate functions
function IsNotEmptyCheck(element, constraint)
{
	if (element.getAttribute("type") == "checkbox") {
		return element.checked;
	}
	return (element.value.length > 0);
}

function MinLengthCheck(element, constraint)
{
	return (element.value.length >= constraint.minLength);
}

function EmailCheck(element, constraint)
{
	re = new RegExp(/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/);
	return (re.exec(element.value));
}

function UploadImageCheck(element, constraint)
{	
	re = new RegExp(/^.*(\.(gif|jpg|png))$/i);
	return (re.exec(element.value));
}

function IsNumericCheck(element, constraint)
{  	
  	if (constraint.letEmptyString && element.value == '')
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

/*
    json.js
*/
(function () {
    var m = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        s = {
            array: function (x) {
                var a = ['['], b, f, i, l = x.length, v;
                for (i = 0; i < l; i += 1) {
                    v = x[i];
                    f = s[typeof v];
                    if (f) {
                        v = f(v);
                        if (typeof v == 'string') {
                            if (b) {
                                a[a.length] = ',';
                            }
                            a[a.length] = v;
                            b = true;
                        }
                    }
                }
                a[a.length] = ']';
                return a.join('');
            },
            'boolean': function (x) {
                return String(x);
            },
            'null': function (x) {
                return "null";
            },
            number: function (x) {
                return isFinite(x) ? String(x) : 'null';
            },
            object: function (x) {
                if (x) {
                    if (x instanceof Array) {
                        return s.array(x);
                    }
                    var a = ['{'], b, f, i, v;
                    for (i in x) {
                        v = x[i];
                        f = s[typeof v];
                        if (f) {
                            v = f(v);
                            if (typeof v == 'string') {
                                if (b) {
                                    a[a.length] = ',';
                                }
                                a.push(s.string(i), ':', v);
                                b = true;
                            }
                        }
                    }
                    a[a.length] = '}';
                    return a.join('');
                }
                return 'null';
            },
            string: function (x) {
                if (/["\\\x00-\x1f]/.test(x)) {
                    x = x.replace(/([\x00-\x1f\\"])/g, function(a, b) {
                        var c = m[b];
                        if (c) {
                            return c;
                        }
                        c = b.charCodeAt();
                        return '\\u00' +
                            Math.floor(c / 16).toString(16) +
                            (c % 16).toString(16);
                    });
                }
                return '"' + x + '"';
            }
        };

    Object.prototype.toJSONString = function () {
        return s.object(this);
    };

    Array.prototype.toJSONString = function () {
        return s.array(this);
    };
})();

String.prototype.parseJSON = function () {
    try {
        return !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(
                this.replace(/"(\\.|[^"\\])*"/g, ''))) &&
            eval('(' + this + ')');
    } catch (e) {
        return false;
    }
};