Backend.Payment = 
{
    init: function(container)
    {
        Event.observe(container.down('a.addOfflinePayment'), 'click', this.showOfflinePaymentForm);
        Event.observe(container.down('a.offlinePaymentCancel'), 'click', this.hideOfflinePaymentForm);
    },
    
    showOfflinePaymentForm: function(e)
    {
        var element = Event.element(e);
        slideForm(element.up('div.menuContainer').down('div.addOffline'), element.up('ul.paymentMenu'));
    },

    hideOfflinePaymentForm: function(e)
    {
        var element = Event.element(e);
        restoreMenu(element.up('div.addOffline'), element.up('div.menuContainer').down('ul.paymentMenu'));
    },
    
    submitOfflinePaymentForm: function(e)
    {
        new Backend.Payment.AddOffline(e);
    }
}

Backend.Payment.AddOffline = Class.create();
Backend.Payment.AddOffline.prototype = 
{
    form: null,
    
    event: null,
    
    initialize: function(e)
    {
        this.form = Event.element(e);    
        this.event = e;
        new LiveCart.AjaxRequest(this.form, null, this.complete.bind(this));
    },
    
    complete: function(originalRequest)
    {
        Backend.Payment.hideOfflinePaymentForm(this.event);    
        
        var ul = this.form.up('div.tabPageContainer').down('ul.transactions');
        ul.innerHTML += originalRequest.responseText;
        new Effect.Highlight(ul.lastChild, {startcolor:'#FBFF85', endcolor:'#EFF4F6'})
    }
}