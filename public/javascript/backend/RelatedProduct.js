if(!Backend) Backend = {};
if(!Backend.Product) Backend.Product = {};
Backend.RelatedProduct = {
    activeListCallbacks: 
    {
        beforeDelete: function(li){ 
            if(confirm(Backend.RelatedProduct.messages.areYouSureYouWantToDelete)) 
            {
                return Backend.RelatedProduct.links.deleteRelated + "/?relatedProductID=" + this.getRecordId(li);
            }
        },
        afterDelete: function(li, response){
            if(!response.error) {
                this.remove(li);
                var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
                tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') - 1);
            }
        },
        beforeSort: function(li, order){ 
            return Backend.RelatedProduct.links.sort + '&' + order;
        },
        afterSort: function(li, response){ console.info('afterSort') }
    },
    addProductToList: function(productID, relatedProductID)
    {
        var self = this;
        new Ajax.Request(this.links.related + "/?relatedProductID=" + relatedProductID , {
           method: 'get',
           onSuccess: function(response) {
                console.info(response.responseText);
                var evaluatedResponse;
                try
                {
                    evaluatedResponse = eval("(" + response.responseText + ")");
                }
                catch(e) {}
                
                if(evaluatedResponse && evaluatedResponse.error && evaluatedResponse.error.length > 0)
                {
                    // error
                    new Backend.SaveConfirmationMessage($('productRelationshipMsg_' + productID), { message: evaluatedResponse.error, type: 'red' });
                }
                else
                {
                    try
                    {
                        var relatedList = ActiveList.prototype.getInstance($("productRelationships_" + productID));
                        relatedList.addRecord(relatedProductID, response.responseText, true);

                        var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
                        tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') + 1);
                    } 
                    catch(e)
                    {
                        console.info(e);
                    }
                }
               
           }
        });
    }
};


Backend.RelatedProduct.SelectProductPopup = Class.create();
Backend.RelatedProduct.SelectProductPopup.prototype = {
    height: 520,
    width:  800,
    onProductSelect: function() {},
    
    initialize: function(link, title, options)
    {
        try
        {
            this.link = link;
            this.title = title;
            
            if(options.onProductSelect) this.onProductSelect = options.onProductSelect;
            
            this.createPopup();
        }
        catch(e)
        {
            console.info(e);
        }
    },
    
    createPopup: function()
    {
        Backend.RelatedProduct.SelectProductPopup.prototype.popup = window.open(this.link, this.title, 'resizable=1,width=' + this.width + ',height=' + this.height);
        Backend.RelatedProduct.SelectProductPopup.prototype.popup.focus();
                        
        Event.observe(window, 'unload', function() { Backend.RelatedProduct.SelectProductPopup.prototype.popup.close(); });
        
        window.selectProductPopup = this;
    },
    
    getSelectedProduct: function(productID)
    {
        this.productID = productID;
        // Backend.RelatedProduct.SelectProductPopup.prototype.popup.opener.focus();
        // Backend.RelatedProduct.SelectProductPopup.prototype.popup.close();
        
        var self = this;
        setTimeout(function() { self.onProductSelect.call(self); }, 100)
        
    }
}