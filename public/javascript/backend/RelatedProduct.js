if(!Backend) Backend = {};
if(!Backend.Product) Backend.Product = {};
Backend.RelatedProduct = {
    addProductToList: function(productID, relatedProductID)
    {
        var self = this;
        new Ajax.Request(this.links.related + "/" + relatedProductID , {
           method: 'get',
           onSuccess: function(response) {
                var relatedList = ActiveList.prototype.getInstance($("relatedProducts_" + productID));
                relatedList.addRecord(relatedProductID, response.responseText);
           }
        });
    }
};


Backend.RelatedProduct.SelectProductPopup = Class.create();
Backend.RelatedProduct.SelectProductPopup.prototype = {
    height: 400,
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
        this.popup = window.open(this.link, this.title, 'width=' + this.width + ',height=' + this.height);
        this.popup.selectProductPopup = this;
    },
    
    getSelectedProduct: function(productID)
    {
        this.productID = productID;
        this.popup.opener.focus();
        this.popup.close();
        
        var self = this;
        setTimeout(function() { self.onProductSelect.call(self); }, 100)
        
    }
}