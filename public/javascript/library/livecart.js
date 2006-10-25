var LiveCart = {
	ajaxUpdaterInstance: null
}

LiveCart.AjaxUpdater = Class.create();
LiveCart.AjaxUpdater.prototype = {
	
	indicatorContainerId: null,
	
	/**
	 *
	 */
	initialize: function(formToSubmit, containerId, indicatorId, insertionPosition)
	{
		LiveCart.ajaxUpdaterInstance = this;
		this.indicatorContainerId = indicatorId;
		Element.show(this.indicatorContainerId);
		paramStr = Form.serialize(formToSubmit);
		
		var updaterOptions = { method: formToSubmit.method, 
							   parameters: paramStr,
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
						 formToSubmit.action, 
						 updaterOptions);
	},
	
	hideIndicator: function()
	{
		Element.hide(LiveCart.ajaxUpdaterInstance.indicatorContainerId);
	},
	
	reportError: function(response)
	{
		alert('Error!\n\n' + response.responseText);
	}
}