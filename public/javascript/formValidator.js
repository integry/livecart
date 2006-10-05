/**
 * Validates form
 */
function Form_Validator(form) {
  	
  	for (k = 0; k <= form.elements.length - 1;k++) {
	    
	    el = form.elements[k];
	    val = el.getAttribute('validate').parseJSON();
		for (var i in val) {
		  
			var constraint = val[i]['value'];
			
			/* Check if validation function exists */
			func = 'Form_Validate_' + i;			
			eval('func_exists = window.' + func + ';');

			if (!func_exists) {			  
			  	continue;			  
			}

			/* Validate */
			ev = 'var is_valid = ' + func + '(el,constraint);';
		  	eval(ev);

		  	if (!is_valid) {
			    
			    alert(val[i]['err']);
			    el.focus();
				return false;
			    
			}

		} 
	    
	}
  		  
	return true;  	
  
}

/**
 * Tests if value is not empty
 */
function Form_Validate_require(element, param) {

	return (element.value.length > 0);

}

/**
 * Tests if value reaches minimum length
 */
function Form_Validate_minlength(element, param) {

	return (element.value.length >= param);

}

/**
 * Tests if value doesn't exceed maximum length
 */
function Form_Validate_maxlength(element, param) {

	return (element.value.length <= param);

}

/**
 * Tests if value isn't smaller than the min value
 */
function Form_Validate_minvalue(element, param) {

	return (Number(element.value) >= Number(param));

}

/**
 * Tests if value isn't larger than the max value
 */
function Form_Validate_maxvalue(element, param) {

	return (Number(element.value) <= Number(param));

}

/**
 * Tests if field value matches the required value
 */
function Form_Validate_reqvalue(element, param) {

	return element.value == param;

}

/**
* Tests for custom regular expressions
*
* For example, regexp for validating an URL:
* /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
*/
function Form_Validate_regexp(element, param) {

	reg = new RegExp(param);

	return reg.test(element.value);

}

    
/*
    json.js
    2006-04-28

    This file adds these methods to JavaScript:

        object.toJSONString()

            This method produces a JSON text from an object. The
            object must not contain any cyclical references.

        array.toJSONString()

            This method produces a JSON text from an array. The
            array must not contain any cyclical references.

        string.parseJSON()

            This method parses a JSON text to produce an object or
            array. It will return false if there is an error.
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