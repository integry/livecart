Backend.TemplateHandler = Class.create();
Backend.TemplateHandler.prototype = 
{
	form: null,
	
	initialize: function(form)
	{
		this.form = form;
		this.form.onsubmit = this.submit.bindAsEventListener(this);
	},
	
	submit: function()
	{
		var indicator = document.getElementsByClassName('progressIndicator', this.form)[0];
		new LiveCart.AjaxRequest(this.form, indicator, this.saveComplete.bind(this));
		return false;
	},
	
	saveComplete: function(originalRequest)
	{
		var msgClass = originalRequest.responseText ? 'yellowMessage' : 'redMessage';			 
		var msg = new Backend.SaveConfirmationMessage(document.getElementsByClassName(msgClass, this.form)[0]);
		 
		msg.show();
		 
		opener.location.reload();	
	}
}