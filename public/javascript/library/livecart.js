var LiveCart = {
    ajaxUpdaterInstance: null
}

LiveCart.AjaxRequest = Class.create();
LiveCart.AjaxRequest.prototype = {

}

LiveCart.AjaxUpdater = Class.create();
LiveCart.AjaxUpdater.prototype = {

    indicatorContainerId: null,

    /**
     * AjaxUpdater constructor
     *
     * @param formOrUrl mixed Form object or URL string
     * @param options assoc array Option container.
     * Available options:
   	 * containerId:
   	 * indicatorId:
   	 * insertion:
   	 * onComplete:
     */
    /*
    initialize: function(formOrUrl, options)
    {
    	LiveCart.ajaxUpdaterInstance = this;
    	var url = "";
        var method = "";
        var params = "";
        if (typeof formOrUrl == "object")
        {
            var form = formOrUrl;
            url = form.action;
            method = form.method;
            params = Form.serialize(form);
        }
        else
        {
            url = formOrUrl;
            method = "post";
        }

        var updaterOptions = { method: method,
						       parameters: params,
							   onFailure: this.reportError};
        if (options != undefined)
        {
			if (option.indicatorId != undefined)
			{
        		this.indicatorContainerId = indicatorId;
        		this.showIndicator();
			}
			if (options.onComplete != undefined)
			{
				updaterOptions.onComplete = options.onComplete;
			}
			else
			{
				updaterOptions.onComplete = this.postProcessResponse;
			}

	        if (options.insertion != undefined)
	        {
	            switch(options.insertion)
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
        }
		new Ajax.Updater({success: containerId},
                         url,
                         updaterOptions);
    },
    */

    initialize: function(formOrUrl, containerId, indicatorId, insertionPosition)
    {
        var url = "";
        var method = "";
        var params = "";
        if (typeof formOrUrl == "object")
        {
            var form = formOrUrl;
            url = form.action;
            method = form.method;
            params = Form.serialize(form);
        }
        else
        {
            url = formOrUrl;
            method = "post";
        }
        LiveCart.ajaxUpdaterInstance = this;
        this.indicatorContainerId = indicatorId;
        Element.show(this.indicatorContainerId);

        var updaterOptions = { method: method,
                               parameters: params,
                               onComplete: this.postProcessResponse,
                               onFailure: this.reportError};

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
		
        new Ajax.Updater({success: containerId},
                         url,
                         updaterOptions);
    },


	hideIndicator: function()
	{
		Element.hide(LiveCart.ajaxUpdaterInstance.indicatorContainerId);
	},

	showIndocator: function()
	{
		Element.show(this.indicatorContainerId);
	},

    postProcessResponse: function(response)
    {
		document.body.style.cursor = 'default';
        LiveCart.ajaxUpdaterInstance.updateHead(response);
        LiveCart.ajaxUpdaterInstance.runJavaScripts(response.responseText);
        LiveCart.ajaxUpdaterInstance.hideIndicator();
    },

    reportError: function(response)
    {
        alert('Error!\n\n' + response.responseText);
    },

    /**
     * Update HTML head with HTTPXMLRequest (static method, scary :D)
     *
     * You can use <head>...</head> in your templates to update page head nodes (such as title, script, link). It also
     * Should be a first tag u used in your template. If script function sees that such node allready exists in head
     * then that node is removed and recreated with new attributes.
     *
     * @todo When new node is loaded there is no way to completely remove it from head until you refresh the page
     * @todo Test on all browsers (now it is tested only with Firefox)
     *
     * @param responce object
     */
    updateHead: function(response)
    {
        var headTag = "</head>";

        if(response.responseText.indexOf(headTag) === -1) return;

        var headTagEnd = response.responseText.indexOf(headTag) + headTag.length;
        var newHeadElements = (new DOMParser()).parseFromString(response.responseText.substring(0, headTagEnd), "text/xml").getElementsByTagName("head")[0].getElementsByTagName("*");

        var oldHeadElements = document.getElementsByTagName("head")[0];

        var oldTitle = oldHeadElements.getElementsByTagName("title")[0];
        if(oldTitle)
        {
            document.title = oldTitle.firstChild.nodeValue;
        }

        for(var i = 0; i < newHeadElements.length; i++)
        {
            var similarElements = oldHeadElements.getElementsByTagName(newHeadElements[i].tagName);
            var element = document.createElement(newHeadElements[i].tagName);

            var elementAttributes = newHeadElements[i].attributes;
            var attributesLength = newHeadElements[i].attributes.length;
            for(var j = 0; j < attributesLength; j++)
            {
                var attr = newHeadElements[i].attributes[j].nodeName;
                var value = newHeadElements[i].attributes[j].nodeValue;
                element[attr] = value;
            }

            if(newHeadElements[i].firstChild)
            {
                element.appendChild(document.createTextNode(newHeadElements[i].firstChild.nodeValue));
            }

            for(var j = 0; j < similarElements.length; j++)
            {
                // Delete old element
                switch(element.tagName.toLowerCase())
                {
                    case 'script':
                        if(similarElements[j].src = element.src)
                        {
                            Element.remove(similarElements[j]);
                        }
                        oldHeadElements.appendChild(element);
                    break;
                    case 'link':
                        if(similarElements[j].href = element.href)
                        {
                            Element.remove(similarElements[j]);
                        }
                        oldHeadElements.appendChild(element);
                    break;
                    case 'title':
                        document.title = element.firstChild.nodeValue;
                    break;
                    default:
                        Element.remove(similarElements[j]);
                        oldHeadElements.appendChild(element);
                    break;
                }
            }
        }
    },

    runJavaScripts: function(response)
    {
        var innerScripts = response.match(/\<script.*?\>([\s\S]*?)\<\/script\>/igm);
        if(innerScripts)
        {
            innerScripts.each(function(innerScript)
            {
                    eval(innerScript.replace(/\<\/?script[^\>]*\>/img, ""));
            });
        }
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