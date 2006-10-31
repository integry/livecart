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
		Element.hide(LiveCart.ajaxUpdaterInstance.indicatorContainerId);
	},
	
	reportError: function(response) 
	{
		alert('Error!\n\n' + response.responseText);
	}
}