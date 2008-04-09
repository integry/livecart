/**
 *	@author Integry Systems
 */

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

		if (indicatorId)
		{
			this.indicatorContainerId = indicatorId;
			Element.show(this.indicatorContainerId);
		}

		if (!options)
		{
			options = {};
		}

		options.method = method;
		options.parameters = params;
		options.onComplete = this.postProcessResponse.bind(this, this.parseURI(url));
		options.onFailure = this.reportError;

		document.body.style.cursor = 'progress';

		this.request = new Ajax.Request(url, options);
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
		if(this.indicatorContainerId)
		{
			Element.hide(this.indicatorContainerId);
		}
	},

	showIndicator: function()
	{
		if(this.indicatorContainerId)
		{
			Element.show(this.indicatorContainerId);
		}
	},

	postProcessResponse: function(url, response)
	{
		document.body.style.cursor = 'default';
		this.hideIndicator();

		if (response.getResponseHeader('Content-type').match(/text\/javascript/))
		{
			response.responseData = response.responseText.evalJSON();
		}

		if (response.getResponseHeader('Content-type').match(/text\/javascript/) && $('confirmations'))
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

		new Insertion.Top(color + 'Zone',
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

	initialize: function(formOrUrl, container, indicator, insertionPosition, onComplete)
	{
		var url = "";
		var method = "";
		var params = "";
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

		LiveCart.ajaxUpdaterInstance = this;

		if (indicatorId)
		{
			this.indicatorContainerId = indicatorId;
			Element.show(this.indicatorContainerId);
		}

		var updaterOptions = { method: method,
							   parameters: params,
							   onComplete: this.postProcessResponse.bind(this),
							   onFailure: this.reportError.bind(this)
							   };

		if (insertionPosition != undefined)
		{
			switch(insertionPosition)
			{
				case 'top':
					updaterOptions.insertion = Insertion.Top;
				break;

				case 'bottom':
					updaterOptions.insertion = Insertion.Bottom;
				break;

				case 'before':
					updaterOptions.insertion = Insertion.Before;
				break;

				case 'after':
					updaterOptions.insertion = Insertion.After;
				break;

				default:
					alert('Invalid insertion position value in AjaxUpdater');
				break;
			}
		}

		document.body.style.cursor = 'progress';

		var ajax = new Ajax.Updater({success: containerId},
						 url,
						 updaterOptions);

	},

	hideIndicator: function()
	{
		if (this.indicatorContainerId)
		{
			this.indicatorContainerId.hide();
		}
	},

	showIndicator: function()
	{
		if (this.indicatorContainerId)
		{
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