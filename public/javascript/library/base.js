
AjaxUpdater Class.create();
AjaxUpdater = {
	
	indicatorContainerId: null,
	
	initialize: function(url, containerId, indicatorId, formToSubmit, method)
	{
		this.indicatorContainerId = indicatorId;
		Element.show(this.indicatorContainerId);
		paramStr = Form.serialize(formToSubmit);
		var axjaxUpdater = new Ajax.Updater(containerId, 
											url, 
											{ method: 'post', 
											  parameters: paramStr,
											  onComplete: this.hideIndicator }); 
	},
	
	hideIndicator: function()
	{
		Element.hide(this.indicatorContainerId);
	}
}