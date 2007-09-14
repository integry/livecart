var LiveCart = {
    ajaxUpdaterInstance: null
}

LiveCart.AjaxRequest = Class.create();
LiveCart.AjaxRequest.prototype = {
    requestCount: 0,
    
	onComplete: false,
    
    indicatorContainerId: false,
    
	initialize: function(formOrUrl, indicatorId, onComplete)
    {
        var url = "";
        var method = "";
        var params = "";
        
        this.onComplete = onComplete;
        
        if (typeof formOrUrl == "object")
        {
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
        
        var updaterOptions = { method: method,
                               parameters: params,
                               onComplete: this.postProcessResponse.bind(this, this.parseURI(url)),
                               onFailure: this.reportError
                               };
       
		document.body.style.cursor = 'progress';

        new Ajax.Request(url, updaterOptions);
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
		this.hideIndicator();
		
		if ('text/javascript' == response.getResponseHeader('Content-type') && $('confirmations'))
		{
            var confirmations = $('confirmations');
            if(!confirmations.down('#yellowZone')) new Insertion.Top('confirmations', '<div id="yellowZone"></div>');
            if(!confirmations.down('#redZone')) new Insertion.Top('confirmations', '<div id="redZone"></div>');
            if(!confirmations.down('#bugZone')) new Insertion.Top('confirmations', '<div id="bugZone"></div>');

            if(window.selectPopupWindow)
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
			
            try
            {
                response.responseData = response.responseText.evalJSON();
                
                // Show confirmation
                if(response.responseData.status)
                {
                    this.showConfirmation(response.responseData);
                }
            }
            catch (e)  { this.showBug(); }
        }

		document.body.style.cursor = 'default';
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
		
		if(window.selectPopupWindow)
		{
			var win = window.selectPopupWindow;
			
	        new win.Insertion.Top(color + 'Zone', 
	        '<div style="display: none;" id="confirmation_' + (++LiveCart.AjaxRequest.prototype.requestCount) + '" class="' + color + 'Message">' + 
	            '<img class="closeMessage" src="image/silk/cancel.png"/>' + 
	            '<div>' + responseData.message + '</div>' + 
	        '</div>');
			
            new win.Backend.SaveConfirmationMessage(win.$('confirmation_' + LiveCart.AjaxRequest.prototype.requestCount));  
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

/**
 * Converts an XMLDocument into HTMLElement
 *
 * Useful when receiving partial page content as XML via AJAX, which can be transformed to
 * inserted into document as HTMLElement.
 *
 * <code>
 * 		item = xml2HtmlElement(request.responseXML.firstChild);
 *		document.getElementById('someList').appendChild(item);
 * </code>
 *
 * Don't forget to set the correct Content-type header before sending the content:
 * <code>
 *      $response->setHeader('Content-type', 'application/xml');
 * </code>
 *
 * @param xml Element
 * @return HTMLElement
 */
function xml2HtmlElement(xml)
{
	var k = 0;
	var a = 0;
	var el = 0;
	var child = 0;

	if ('#text' == xml.nodeName)
	{
		el = document.createTextNode(xml.nodeValue);
	}
	else
	{
	  	el = document.createElement(xml.nodeName);
		el.nodeValue = xml.nodeValue;
		if (xml.attributes.length > 0)
		{
		  	for (a = 0; a < xml.attributes.length; a++)
		  	{
			    att = xml.attributes[a];
				el.setAttribute(att.name, att.value);
			}
		}
		if (xml.childNodes.length > 0)
		{
			for (k = 0; k < xml.childNodes.length; k++)
			{
				child = xml2HtmlElement(xml.childNodes[k]);
				el.appendChild(child);
			}
		}
	}
	return el;
}