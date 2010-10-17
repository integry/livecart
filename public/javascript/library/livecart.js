/**
 *	@author Integry Systems
 */

Function.prototype.inheritsFrom = function( parentClassOrObject )
{
	if ( parentClassOrObject.constructor == Function )
	{
		//Normal Inheritance
		var c = function() {}
		c.prototype = parentClassOrObject.prototype;
		this.prototype = new c();
		this.prototype.constructor = this;
		this.prototype.parent = parentClassOrObject.prototype;
	}
	else
	{
		//Pure Virtual Inheritance
		this.prototype = parentClassOrObject;
		this.prototype.constructor = this;
		this.prototype.parent = parentClassOrObject;
	}

	this.parent = this.prototype.parent;
	this.prototype.parentConstructor = parentClassOrObject;

	this.prototype.callConstructor = function(args)
	{
		return this.parentConstructor.apply(this, args);
	};

	if (this.methods)
	{
		$H(this.methods).each(function(func)
		{
			this.prototype[func[0]] = func[1];
		}.bind(this)
		);
	}

	return this;
}

// not really working yet
Function.prototype.inherit = function()
{
	var extendedClass = function()
	{
		this.callConstructor(arguments);
	}

	var c = function() {}
	c.prototype = this.prototype;
	extendedClass.prototype = new c();
	extendedClass.prototype.constructor = this;
	extendedClass.prototype.parent = this.prototype;

	extendedClass.parent = this.prototype.parent;
	this.prototype.parentConstructor = this;

	return extendedClass;
}

var LiveCart = {
	ajaxUpdaterInstance: null
}

LiveCart.AjaxRequest = Class.create();
LiveCart.AjaxRequest.prototype = {
	requestCount: 0,

	onComplete: false,

	indicatorContainerId: false,

	request: false,

	initialize: function(formOrUrl, indicatorId, onComplete, options)
	{
		var url = "";
		var method = "";
		var params = "";

		this.onComplete = onComplete;

		if (typeof formOrUrl == "object")
		{
			if (window.tinyMCE)
			{
				tinyMCE.triggerSave();
			}

			var form = formOrUrl;
			url = form.action;
			method = form.method;
			params = Form.serialize(form);

			if (!indicatorId)
			{
				var controls = form.down('fieldset.controls');
				if (controls)
				{
					indicatorId = controls.down('.progressIndicator');
				}
				else
				{
					indicatorId = form.down('.progressIndicator');
				}
			}
		}
		else
		{
			url = formOrUrl;
			method = "post";
		}

		url = this.fixUrl(url);

		if (indicatorId && $(indicatorId))
		{
			this.adjustIndicatorVisibility = ($(indicatorId).style.visibility == 'hidden');

			if ('SELECT' == indicatorId.tagName)
			{
				var selectIndicator = document.createElement('span');
				selectIndicator.className = 'progressIndicator';
				var nextSibling = indicatorId.nextSibling;
				if (nextSibling)
				{
					nextSibling.parentNode.insertBefore(selectIndicator, nextSibling);
				}
				else
				{
					indicatorId.parentNode.appendChild(selectIndicator);
				}

				indicatorId = selectIndicator;

				this.adjustIndicatorVisibility = true;
			}
			else if (('INPUT' == indicatorId.tagName) && (('checkbox' == indicatorId.type) || ('radio' == indicatorId.type)))
			{
				this.replacedIndicator = indicatorId;
				indicatorId = $(document.createElement('span'));
				indicatorId.className = this.replacedIndicator.className;
				indicatorId.id = this.replacedIndicator.id;
				indicatorId.addClassName('checkbox');
				indicatorId.checked = this.replacedIndicator.checked;

				if (this.replacedIndicator.parentNode)
				{
					this.replacedIndicator.parentNode.replaceChild(indicatorId, this.replacedIndicator);
				}
			}

			this.indicatorContainerId = indicatorId;
			this.showIndicator();
		}

		if (!options)
		{
			options = {};
		}

		options.method = method;

		if (!options.parameters)
		{
			options.parameters = params;
		}

		options.parameters += (escape(options.parameters) ? '&' : '') + 'ajax=true';

		options.onComplete = this.postProcessResponse.bind(this, this.parseURI(url));
		options.onFailure = this.reportError;

		document.body.style.cursor = 'progress';

		this.request = new Ajax.Request(url, options);
	},

	fixUrl: function(url)
	{
		// fix repeting ? in URLs
		var urlParts = url.split(/\?/);
		var url = urlParts.shift();
		if (urlParts.length)
		{
			url += '?' + urlParts.join('&');
		}

		// fix &amp;s
		url = url.replace(/&amp;/, '&');

		return url;
	},

	parseURI: function(URI)
	{
		if(!URI) return {};

		var splitedURI = URI.split("?");
		var URL = splitedURI[0];
		var queryString = splitedURI[1];
		var query = {};

		if(queryString)
		{
			$A(queryString.split("&")).each(function(paramString) {
				var params = paramString.split("=");

				var match = params[0].match(/(.*)\[(\d*)\]$/);
				if(match)
				{
					if(!query[match[1]]) query[match[1]] = $H({});

					if(match[2] == "")
					{
						match[2] = query[match[1]].size();
					}

					query[match[1]][match[2]] = params[1];
				}
				else
				{
					query[params[0]] = params[1];
				}
			});
		}

		return {
			'url': URL,
			'queryString': queryString,
			'query': query
		};
	},

	hideIndicator: function()
	{
		if (!this.indicatorContainerId)
		{
			return;
		}

		if (this.replacedIndicator && this.indicatorContainerId.parentNode)
		{
			this.indicatorContainerId.parentNode.replaceChild(this.replacedIndicator, this.indicatorContainerId);
			this.replacedIndicator.checked = this.indicatorContainerId.checked;
			Element.show(this.replacedIndicator);
			this.adjustIndicatorVisibility = true;
		}

		if (this.adjustIndicatorVisibility)
		{
			Element.hide(this.indicatorContainerId);
		}

		$(this.indicatorContainerId).removeClassName('progressIndicator');
	},

	showIndicator: function()
	{
		if (this.indicatorContainerId)
		{
			$(this.indicatorContainerId).addClassName('progressIndicator');
			Element.show(this.indicatorContainerId);
		}
	},

	postProcessResponse: function(url, response)
	{
		document.body.style.cursor = 'default';
		this.hideIndicator();

		var contentType = response.getResponseHeader('Content-type');

		if (contentType && contentType.match(/text\/javascript/))
		{
			var responseData = response.responseText.evalJSON();
			try
			{
				response.responseData = responseData;
			}
			catch (e)
			{
				// IE 6 won't let add new properties to the request object
				response = { responseData: responseData }
			}

			if (responseData.__redirect)
			{
				window.location.href = responseData.__redirect;
			}

			Observer.processArray(responseData);
		}

		if (contentType && contentType.match(/text\/javascript/) && $('confirmations'))
		{
			var confirmations = $('confirmations');
			if(!confirmations.down('#yellowZone')) new Insertion.Top('confirmations', '<div id="yellowZone"></div>');
			if(!confirmations.down('#redZone')) new Insertion.Top('confirmations', '<div id="redZone"></div>');
			if(!confirmations.down('#bugZone')) new Insertion.Top('confirmations', '<div id="bugZone"></div>');

			try
			{
				if(window.selectPopupWindow && window.selectPopupWindow.document)
				{
					var win = window.selectPopupWindow;

					var confirmations = win.$('confirmations');
					if(confirmations)
					{
						if(!confirmations.down('#yellowZone')) new win.Insertion.Top('confirmations', '<div id="yellowZone"></div>');
						if(!confirmations.down('#redZone')) new win.Insertion.Top('confirmations', '<div id="redZone"></div>');
						if(!confirmations.down('#bugZone')) new win.Insertion.Top('confirmations', '<div id="bugZone"></div>');
					}
				}
			}
			catch(e)
			{

			}

			try
			{
				// Show confirmation
				if(response.responseData.status)
				{
					this.showConfirmation(response.responseData);
				}
			}
			catch (e)  { this.showBug(); }
		}

		if (this.onComplete)
		{
			this.onComplete(response, url);
		}
	},

	showBug: function()
	{
		new Insertion.Top('bugZone',
		'<div style="display: none;" id="confirmation_' + (++LiveCart.AjaxRequest.prototype.requestCount) + '" class="bugMessage">' +
			'<img class="closeMessage" src="image/silk/cancel.png"/>' +
			'<div>' + Backend.internalErrorMessage + '</div>' +
		'</div>');

		new Backend.SaveConfirmationMessage($('confirmation_' + LiveCart.AjaxRequest.prototype.requestCount));

		try
		{
			if(window.selectPopupWindow)
			{
				var win = window.selectPopupWindow;
				if(win.$('confirmations'))
				{
					new win.Insertion.Top('bugZone',
					'<div style="display: none;" id="confirmation_' + (++LiveCart.AjaxRequest.prototype.requestCount) + '" class="bugMessage">' +
						'<img class="closeMessage" src="image/silk/cancel.png"/>' +
						'<div>' + Backend.internalErrorMessage + '</div>' +
					'</div>');

					new Backend.SaveConfirmationMessage(win.$('confirmation_' + LiveCart.AjaxRequest.prototype.requestCount));
				}
			}
		}
		catch(e)
		{

		}
	},

	showConfirmation: function(responseData)
	{
		if(!responseData.message) return;

		var color = null;
		if('success' == responseData.status) color = 'yellow';
		if('failure' == responseData.status) color = 'red';

		new Insertion.Top('confirmations',
		'<div style="display: none;" id="confirmation_' + (++LiveCart.AjaxRequest.prototype.requestCount) + '" class="' + color + 'Message">' +
			'<img class="closeMessage" src="image/silk/cancel.png"/>' +
			'<div>' + responseData.message + '</div>' +
		'</div>');

		new Backend.SaveConfirmationMessage($('confirmation_' + LiveCart.AjaxRequest.prototype.requestCount));

		try
		{
			if(window.selectPopupWindow && window.selectPopupWindow.document)
			{
				var win = window.selectPopupWindow;

				new win.Insertion.Top(color + 'Zone',
				'<div style="display: none;" id="confirmation_' + (++LiveCart.AjaxRequest.prototype.requestCount) + '" class="' + color + 'Message">' +
					'<img class="closeMessage" src="image/silk/cancel.png"/>' +
					'<div>' + responseData.message + '</div>' +
				'</div>');

				new win.Backend.SaveConfirmationMessage(win.$('confirmation_' + LiveCart.AjaxRequest.prototype.requestCount));
			}
		}
		catch(e)
		{

		}
	},

	reportError: function(response)
	{
		alert('Error!\n\n' + response.responseText);
	}
}

LiveCart.AjaxUpdater = Class.create();
LiveCart.AjaxUpdater.prototype = {

	indicatorContainerId: null,

	initialize: function(formOrUrl, container, indicator, insertionPosition, onComplete, options)
	{
		var url = "";
		var method = "";
		var params = options ? (options.parameters || '') : '';
		this.onComplete = onComplete;

		var containerId = $(container);
		var indicatorId = $(indicator);

		if (typeof formOrUrl == "object")
		{
			if (window.tinyMCE)
			{
				tinyMCE.triggerSave();
			}

			var form = formOrUrl;
			url = form.action;
			method = form.method;
			params = Form.serialize(form);

			if (!indicatorId)
			{
				var controls = form.down('fieldset.controls');
				if (controls)
				{
					indicatorId = controls.down('.progressIndicator');
					if(indicatorId.style.visibility == 'hidden')
					{
						this.adjustIndicatorVisibility = true;
					}
				}
			}
		}
		else
		{
			url = formOrUrl;
			method = "post";
		}

		url = LiveCart.AjaxRequest.prototype.fixUrl(url);

		LiveCart.ajaxUpdaterInstance = this;

		if (indicatorId)
		{
			this.indicatorContainerId = indicatorId;
			Element.show(this.indicatorContainerId);
			this.adjustIndicatorVisibility = true;
		}

		if (!options)
		{
			options = {};
		}

		options.method = method;
		options.parameters = params;
		options.onComplete = this.postProcessResponse.bind(this);
		options.onFailure = this.reportError.bind(this);
		options.onSuccess = function()
			{
				if (window.ActiveForm && $(container))
				{
					ActiveForm.prototype.destroyTinyMceFields($(container));
				}
			}

		options.parameters += (options.parameters ? '&' : '') + 'ajax=true';

		if (insertionPosition != undefined && insertionPosition != false)
		{
			switch(insertionPosition)
			{
				case 'top':
					options.insertion = Insertion.Top;
				break;

				case 'bottom':
					options.insertion = Insertion.Bottom;
				break;

				case 'before':
					options.insertion = Insertion.Before;
				break;

				case 'after':
					options.insertion = Insertion.After;
				break;

				default:
					alert('Invalid insertion position value in AjaxUpdater'); // ?
				break;
			}
		}

		document.body.style.cursor = 'progress';

		var ajax = new Ajax.Updater({success: containerId},
						 url,
						 options);

	},

	hideIndicator: function()
	{
		if (!this.indicatorContainerId)
		{
			return;
		}

		if (this.adjustIndicatorVisibility)
		{
			Element.hide(this.indicatorContainerId);
		}

		$(this.indicatorContainerId).removeClassName('progressIndicator');
	},

	showIndicator: function()
	{
		if (this.indicatorContainerId)
		{
			$(this.indicatorContainerId).addClassName('progressIndicator');
			Element.show(this.indicatorContainerId);
		}
	},

	postProcessResponse: function(response)
	{
		document.body.style.cursor = 'default';
		response.responseText.evalScripts();
		this.hideIndicator();

		if (this.onComplete)
		{
		  	this.onComplete(response);
		}
	},

	reportError: function(response)
	{
		alert('Error!\n\n' + response.responseText);
	}
}

/********************************************************************
 * Router / Url manipulator
 ********************************************************************/
Router =
{
	urlTemplate: '',

	setUrlTemplate: function(url)
	{
		url = url.replace(/controller/, '__c__');
		this.urlTemplate = url.replace(/action/, '__a__');
	},

	createUrl: function(controller, action, params)
	{
		var url = this.urlTemplate.replace(/__c__/, controller);
		url = url.replace(/__a__/, action);

		if (params)
		{
			$H(params).each(function(param)
			{
				url = this.setUrlQueryParam(url, param[0], param[1])
			}.bind(this));
		}

		return url;
	},

	setUrlQueryParam: function(url, key, value)
	{
		return url + (url.match(/\?/) ? '&' : '?') + key + '=' + value;
	}
}


Observer =
{
	observers: {},

	add: function(key, method, params)
	{
		if (!this.observers[key])
		{
			this.observers[key] = [];
		}

		this.observers[key].push([method, params]);
	},

	process: function(key, value)
	{
		if (!this.observers[key])
		{
			return;
		}

		$A(this.observers[key]).each(function(observer)
		{
			observer[0](value, observer[1]);
		});
	},

	processArray: function(responseData)
	{
		$H(responseData).each(function(v)
		{
			this.process(v[0], v[1]);
		}.bind(this));
	}
}

function decode64(inp)
{

var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + //all caps
"abcdefghijklmnopqrstuvwxyz" + //all lowercase
"0123456789+/=";

var out = ""; //This is the output
var chr1, chr2, chr3 = ""; //These are the 3 decoded bytes
var enc1, enc2, enc3, enc4 = ""; //These are the 4 bytes to be decoded
var i = 0; //Position counter

// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
var base64test = /[^A-Za-z0-9\+\/\=]/g;

if (base64test.exec(inp)) { //Do some error checking

	return false;

}
inp = inp.replace(/[^A-Za-z0-9\+\/\=]/g, "");

do { //Here?s the decode loop.

//Grab 4 bytes of encoded content.
enc1 = keyStr.indexOf(inp.charAt(i++));
enc2 = keyStr.indexOf(inp.charAt(i++));
enc3 = keyStr.indexOf(inp.charAt(i++));
enc4 = keyStr.indexOf(inp.charAt(i++));

//Heres the decode part. There?s really only one way to do it.
chr1 = (enc1 << 2) | (enc2 >> 4);
chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
chr3 = ((enc3 & 3) << 6) | enc4;

//Start to output decoded content
out = out + String.fromCharCode(chr1);

if (enc3 != 64) {
out = out + String.fromCharCode(chr2);
}
if (enc4 != 64) {
out = out + String.fromCharCode(chr3);
}

//now clean out the variables used
chr1 = chr2 = chr3 = "";
enc1 = enc2 = enc3 = enc4 = "";

} while (i < inp.length); //finish off the loop

//Now return the decoded values.
//return out;
return _utf8_decode(out);
}

 // private method for UTF-8 decoding
function _utf8_decode(utftext) {
	 var string = "";
	 var i = 0;
	 var c, c1, c2 = 0;

	 while ( i < utftext.length ) {

		 c = utftext.charCodeAt(i);

		 if (c < 128) {
			 string += String.fromCharCode(c);
			 i++;
		 }
		 else if((c > 191) && (c < 224)) {
			 c2 = utftext.charCodeAt(i+1);
			 string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
			 i += 2;
		 }
		 else {
			 c2 = utftext.charCodeAt(i+1);
			 c3 = utftext.charCodeAt(i+2);
			 string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
			 i += 3;
		 }

	 }

	 return string;
}

function fireEvent(element,event)
{
    if (document.createEventObject)
    {
        // dispatch for IE
        var evt = document.createEventObject();
        return element.fireEvent('on'+event,evt)
    }
    else
    {
        // dispatch for firefox + others
        var evt = document.createEvent("HTMLEvents");
        evt.initEvent(event, true, true ); // event type,bubbling,cancelable
        return !element.dispatchEvent(evt);
    }
}

Prototype.Browser.IE6 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 6;
Prototype.Browser.IE7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
Prototype.Browser.IE8 = Prototype.Browser.IE && !Prototype.Browser.IE6 && !Prototype.Browser.IE7;