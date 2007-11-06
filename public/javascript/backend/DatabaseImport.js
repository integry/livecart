Backend.DatabaseImport = Class.create();
Backend.DatabaseImport.prototype = 
{
    form: null,
    
    request: null,
    
    initialize: function(form)
    {
        this.form = form;
        this.request = new LiveCart.AjaxRequest(this.form, null, this.formResponse.bind(this));
    },
    
    formResponse: function(originalRequest)
    {
        if (originalRequest.responseData.errors)
        {
            ActiveForm.prototype.setErrorMessages(this.form, originalRequest.responseData.errors);
        }
    }
}