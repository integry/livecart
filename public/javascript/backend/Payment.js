Backend.Payment = 
{
    init: function(container)
    {
        Event.observe(container.down('a.addOfflinePayment'), 'click', this.showOfflinePaymentForm);
        Event.observe(container.down('a.cancelOfflinePayment'), 'click', this.hideOfflinePaymentForm);
        Event.observe(container.down('a.offlinePaymentCancel'), 'click', this.hideOfflinePaymentForm);
    },
    
    showOfflinePaymentForm: function(e)
    {
	 	Event.stop(e);
        var element = Event.element(e);
		
		var menu = new ActiveForm.Slide(element.up(".menuContainer").down('ul.paymentMenu'));
		menu.show("offlinePayment", element.up('div.menuContainer').down('div.addOffline'));
    },

    hideOfflinePaymentForm: function(e)
    {
	 	Event.stop(e);
        var element = Event.element(e);
		
        var menu = new ActiveForm.Slide(element.up(".menuContainer").down('ul.paymentMenu'));
        menu.hide("offlinePayment", element.up('div.menuContainer').down('div.addOffline'));
    },
    
    submitOfflinePaymentForm: function(e)
    {
	 	Event.stop(e);
        new Backend.Payment.AddOffline(e);
    },
    
    showVoidForm: function(transactionID, event)
    {
	 	Event.stop(event);
		var cont = $('transaction_' + transactionID);	

		var menu = new ActiveForm.Slide(cont.down('.transactionMenu'));
		menu.show("voidMenu", cont.down('.voidForm'));
	},
	
	hideVoidForm: function(transactionID, event)
    {
	 	Event.stop(event);
		var cont = $('transaction_' + transactionID);		
		
        var menu = new ActiveForm.Slide(cont.down('.transactionMenu'));
        menu.hide("voidMenu", cont.down('.voidForm')); 
	},
	
	voidTransaction: function(transactionID, form, event)
	{
	 	Event.stop(event);
		new Backend.Payment.TransactionAction(transactionID, form);
	},

    showCaptureForm: function(transactionID, event)
    {
	 	Event.stop(event);
		var cont = $('transaction_' + transactionID);	
		
		var menu = new ActiveForm.Slide(cont.down('.transactionMenu'));
		menu.show("captureMenu", cont.down('.captureForm'));
	},
	
	hideCaptureForm: function(transactionID, event)
    {
	 	Event.stop(event);	
        var cont = $('transaction_' + transactionID);   
			
        var menu = new ActiveForm.Slide(cont.down('.transactionMenu'));
        menu.hide("captureMenu", cont.down('.captureForm'));
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
        
        var cont = this.form.up('div.tabPageContainer');
        
        // update totals
        cont.down('.paymentSummary').innerHTML = originalRequest.responseData.totals;

        var ul = cont.down('ul.transactions');
        ul.innerHTML += originalRequest.responseData.transaction;
        new Effect.Highlight(ul.lastChild, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});
    }
}

Backend.Payment.AddCreditCard = Class.create();
Backend.Payment.AddCreditCard.prototype = 
{
    form: null,
    
    window: null,
    
    initialize: function(form, window)
    {
        this.form = form;
        this.window = window;
        new LiveCart.AjaxRequest(this.form, null, this.complete.bind(this));
    },
    
    complete: function(originalRequest)
    {        
        if (originalRequest.responseData.status == 'failure')
        {
            return;
        }        
     
        this.window.close();
        
        var cont = this.window.opener.document.getElementById('orderManagerContainer').down('div.tabPageContainer');     
        
        // update totals
        cont.down('.paymentSummary').innerHTML = originalRequest.responseData.totals;

        var ul = cont.down('ul.transactions');
        ul.innerHTML += originalRequest.responseData.transaction;
        new Effect.Highlight(ul.lastChild, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});
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
		
		if (confirm(this.form.down('span.confirmation').innerHTML))
		{
            new LiveCart.AjaxRequest(this.form, null, this.complete.bind(this));
        }
	},

    complete: function(originalRequest)
    {   
        var resp = originalRequest.responseData;
        
        if (resp.status == 'failure')
        {
            return;
        }
        
        var cont = this.form.up('div.tabPageContainer');
        
        // update totals
        cont.down('.paymentSummary').innerHTML = resp.totals;
        
        // update transaction
        var newli = document.createElement('ul');
        newli.innerHTML = resp.transaction;
		
		var li = cont.down('ul.transactions').down('#transaction_' + this.id);
		var newChild = newli.firstChild;
		
		li.parentNode.replaceChild(newChild, li);
        new Effect.Highlight(newChild, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});
    }
}