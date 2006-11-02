var LiveCart = {
	ajaxUpdaterInstance: null
}

LiveCart.AjaxUpdater = Class.create();
LiveCart.AjaxUpdater.prototype = {
	
	indicatorContainerId: null,
	
	/**
	 * 
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
							   onComplete: this.hideIndicator,
							   onFailure: this.reportError};
		
		if (insertionPosition != undefined)
		{
			switch(insertionPosition) {
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

		new Ajax.Updater({success: containerId},
						 url, 
						 updaterOptions);
	},
	
	
	hideIndicator: function(response)
	{
		// It would better if i could use something like "this" here
		LiveCart.ajaxUpdaterInstance.updateHead(response);
		LiveCart.ajaxUpdaterInstance.runJavaScripts(response);
		
		Element.hide(LiveCart.ajaxUpdaterInstance.indicatorContainerId);
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
		var scripts = response.responseText.match(/<script.*?>([\s\S]*?)<\/script>/igm);
		for(var i = 0; i < scripts.length; i++)
		{
			var code = scripts[i].replace(/<\/?script.*>/img, "");
			if(code.length > 0) eval(code);
		}
	}
}