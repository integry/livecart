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
    },
    
    showVoidForm: function(transactionID)
    {
		var cont = $('transaction_' + transactionID);		
		slideForm(cont.down('.voidForm'), cont.down('.transactionMenu')); 
		window.setTimeout(function(){ this.down('.voidForm').down('textarea').focus() }.bind(cont), 200);		
	},
	
	hideVoidForm: function(transactionID)
    {
		var cont = $('transaction_' + transactionID);		
		restoreMenu(cont.down('.voidForm'), cont.down('.transactionMenu')); 
	},
	
	voidTransaction: function(transactionID, form, event)
	{
	 	Event.stop(event);
		new Backend.Payment.TransactionAction(transactionID, form);
	},

    showCaptureForm: function(transactionID)
    {
		var cont = $('transaction_' + transactionID);		
		slideForm(cont.down('.captureForm'), cont.down('.transactionMenu')); 
		window.setTimeout(function(){ this.down('.captureForm').down('textarea').focus() }.bind(cont), 200);		
	},
	
	hideCaptureForm: function(transactionID)
    {
		var cont = $('transaction_' + transactionID);		
		restoreMenu(cont.down('.captureForm'), cont.down('.transactionMenu')); 
	},
	
	captureTransaction: function(transactionID, form, event)
	{
	 	Event.stop(event);
		new Backend.Payment.TransactionAction(transactionID, form);
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

Backend.Payment.TransactionAction = Class.create();
Backend.Payment.TransactionAction.prototype = 
{
	id: null,
	
	form: null,
	
	initialize: function(transactionID, form)
	{
		this.id = transactionID;
		this.form = form;
		new LiveCart.AjaxRequest(this.form, null, this.complete.bind(this));
	},

    complete: function(originalRequest)
    {
        var newli = document.createElement('ul');
        newli.innerHTML = originalRequest.responseText;
		
		var li = this.form.up('div.tabPageContainer').down('ul.transactions').down('#transaction_' + this.id);
		var newChild = newli.firstChild;
		
		li.parentNode.replaceChild(newChild, li);
        new Effect.Highlight(newChild, {startcolor:'#FBFF85', endcolor:'#EFF4F6'})
    }
}