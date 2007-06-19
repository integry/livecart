Backend.OrderedItem = {
    activeListCallbacks: 
    {
        beforeDelete: function(li){ 
            if(confirm(Backend.OrderedItem.messages.areYouSureYouWantToDelete)) 
            {
                return Backend.OrderedItem.Links.remove + "/?ShippingID=" + this.getRecordId(li);
            }
        },
        afterDelete: function(li, response){
            if(!response.error) {
                this.remove(li);
                var orderID = this.getRecordId(li, 3);
                Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
            }
        },
        beforeSort: function(li, order){ 
            var oldShipmentId = this.getRecordId(li, 2);
            var newShipmentId = this.getRecordId(this.ul, 1)
            
            if(oldShipmentId != newShipmentId)
            {
                return Backend.OrderedItem.Links.changeShipment + "/" + this.getRecordId(li) + "?from=" + oldShipmentId + "&to=" + newShipmentId
            }
        },
        afterSort: function(li, response){
            var response = eval("(" + response + ")");
            var orderID = this.getRecordId(li, 3);
            
            var shipmentsActiveList = ActiveList.prototype.getInstance('orderShipments_list_' + orderID);
            
            var oldShipmentLi = $("orderShipments_list_" + orderID + "_" + response.oldShipment.ID);
            var newShipmentLi = $("orderShipments_list_" + orderID + "_" + response.newShipment.ID)

            if('success' == response.status)
            {
                try
                {
                    // Old shipment changes
                    $A(["amount", 'shippingAmount', 'taxAmount', 'total']).each(function(type)
                    {
                        var price = oldShipmentLi.down(".shipment_" + type);
                            price.down('.pricePrefix').innerHTML = response.oldShipment.prefix;
                            price.down('.price').innerHTML = parseFloat(response.oldShipment[type]);
                            price.down('.priceSuffix').innerHTML = response.oldShipment.suffix;
                    });
               
                    // New shipment changes
                    $A(["amount", 'shippingAmount', 'taxAmount', 'total']).each(function(type)
                    {
                        var price = newShipmentLi.down(".shipment_" + type);
                            price.down('.pricePrefix').innerHTML = response.newShipment.prefix;
                            price.down('.price').innerHTML = parseFloat(response.newShipment[type]);
                            price.down('.priceSuffix').innerHTML = response.newShipment.suffix;
                    });
    
                    shipmentsActiveList.highlight(newShipmentLi);
                
                    Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
                }
                catch(e)
                {
                    console.info(e)
                }
            }
            else
            {
                li.id = 'orderShipmentsItems_list_' + orderID + '_' + response.oldShipment.ID + '_' + this.getRecordId(li); 
                oldShipmentLi.down('ul').appendChild(li);
                shipmentsActiveList.highlight(oldShipmentLi, 'red');
                
			    new Backend.SaveConfirmationMessage($('noRateInShippingServiceIsAvailableError'))
            }
        }
    },
    
    updateReport: function(report)
    {
        
        
        var reportValues = { 'subtotalAmount': 0, 'shippingAmount': 0, 'totalAmount': 0, 'taxAmount': 0 };
        document.getElementsByClassName('orderShipment_info', report.up('.tabPageContainer')).each(function(shipmentReport) 
        {
            reportValues['subtotalAmount'] += (parseFloat(shipmentReport.down('.shipment_amount').down('.price').innerHTML) || 0);
            reportValues['shippingAmount'] += (parseFloat(shipmentReport.down('.shipment_shippingAmount').down('.price').innerHTML) || 0);
            reportValues['taxAmount'] += (parseFloat(shipmentReport.down('.shipment_taxAmount').down('.price').innerHTML) || 0);
            reportValues['totalAmount'] += (parseFloat(shipmentReport.down('.shipment_total').down('.price').innerHTML) || 0);
        });
        
        report.down('.orderShipment_report_subtotal').down('.price').innerHTML = Math.round(reportValues['subtotalAmount'] * 100) / 100;
        report.down('.orderShipment_report_shippingAmount').down('.price').innerHTML = Math.round(reportValues['shippingAmount'] * 100) / 100;
        report.down('.orderShipment_report_tax').down('.price').innerHTML = Math.round(reportValues['taxAmount'] * 100) / 100;
        report.down('.orderShipment_report_total').down('.price').innerHTML = Math.round(reportValues['totalAmount'] * 100) / 100;
   },
    
   updateProductCount: function(input, orderID, itemID, shipmentID)
   {
       IntegerFilter(input);
       
       var price = input.up('tr').down('.orderShipmentsItem_info_price').down('.price');
       var total = input.up('tr').down('.orderShipmentsItem_info_total').down('.price');
       
       // Recalculate item cost
       total.innerHTML = Math.round(parseFloat(input.value) * parseFloat(price.innerHTML) * 100) / 100;
   },
    
   changeProductCount: function(input, orderID, itemID, shipmentID)
   {
       
       var price = input.up('tr').down('.orderShipmentsItem_info_price').down('.price');
       var total = input.up('tr').down('.orderShipmentsItem_info_total').down('.price');
       
       if(confirm('Update count'))
       {   
           new Ajax.Request(Backend.OrderedItem.Links.changeItemCount + "/" + itemID + "?count=" + input.value, {
                method: 'post',
                onSuccess: function(response) { 
                    var response = eval("(" + response.responseText + ")");
                    var shipment = Backend.Shipment.prototype.getInstance('orderShipments_list_' + orderID + '_' + shipmentID);
                    
                    shipment.setAmount(response.shipment.amount);
                    shipment.setTaxAmount(response.shipment.taxAmount);
                    shipment.setShippingAmount(response.shipment.shippingAmount);
                    shipment.setTotal(response.shipment.total);
                    
                   input.lastValue = input.value;
                   
                   Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
                }
           });
       }
       else
       {
           input.value = input.lastValue;
           Backend.OrderedItem.updateProductCount(input, orderID);
       }
   }
};


Backend.OrderedItem.SelectProductPopup = Class.create();
Backend.OrderedItem.SelectProductPopup.prototype = {
    height: 520,
    width:  1000,
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
        Backend.OrderedItem.SelectProductPopup.prototype.popup = window.open(this.link, this.title, 'resizable=1,width=' + this.width + ',height=' + this.height);
        Backend.OrderedItem.SelectProductPopup.prototype.popup.focus();
                        
        Event.observe(window, 'unload', function() { Backend.OrderedItem.SelectProductPopup.prototype.popup.close(); });
        
        window.selectProductPopup = this;
    },
    
    getSelectedProduct: function(productID)
    {
        this.productID = productID;
        
        var self = this;
        setTimeout(function() { self.onProductSelect.call(self); }, 100)
        
    }
}

Backend.Shipment = Class.create();
Backend.Shipment.prototype = 
{
    instances: {},
    
    initialize: function(root)
    {
        try
        {
            this.findUsedNodes(root);
            this.bindEvents();
            this.shipmentsActiveList = ActiveList.prototype.getInstance(this.nodes.shipmentsList);
            
            if(this.nodes.form.elements.namedItem('ID').value)
            {
                Form.State.backup(this.nodes.form);
            }
        }
        catch(e)
        {
            console.info(e);
        }
    },
        
    getInstance: function(rootNode)
    {
        var rootId = $(rootNode).id;
        if(!Backend.Shipment.prototype.instances[rootId])
        {
            Backend.Shipment.prototype.instances[rootId] = new Backend.Shipment(rootId);
        }
        
        return Backend.Shipment.prototype.instances[rootId];
    },
    
    findUsedNodes: function(root)
    {
        this.nodes = {};
        
        this.nodes.root = $(root);
        this.nodes.form = this.nodes.root.tagName == 'FORM' ? this.nodes.root : this.nodes.root.down('form');

        this.nodes.controls = this.nodes.root.down('.controls');
        if(this.nodes.controls)
        {
            this.nodes.save = this.nodes.controls.down('.submit');
            this.nodes.cancel = this.nodes.controls.down('.cancel');
        }
        
        this.nodes.shipmentsList = $('orderShipments_list_' + this.nodes.form.elements.namedItem('orderID').value);

        if(!this.nodes.form.elements.namedItem('ID').value)
        {
            this.nodes.menu = $('orderShipments_menu_' + this.nodes.form.elements.namedItem('orderID').value);
            this.nodes.menuCancelLink = $('orderShipments_new_' + this.nodes.form.elements.namedItem('orderID').value + "_cancel");
            this.nodes.menuShowLink = $('orderShipments_new_' + this.nodes.form.elements.namedItem('orderID').value + "_show");
            this.nodes.menuForm = $('orderShipments_new_' + this.nodes.form.elements.namedItem('orderID').value + "_form");
        }
    },
    
    bindEvents: function()
    {
       var self = this;
       
       
       if(!this.nodes.form.elements.namedItem('ID').value)
       {
           Event.observe(this.nodes.save, 'click', function(e) { Event.stop(e); self.save(); });
           Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancel(); });
           Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); self.cancel(); });
       }
    },
    
    showNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuCancelLink]);
        ActiveForm.prototype.showNewItemForm(this.nodes.menuShowLink, this.nodes.menuForm); 
        ActiveList.prototype.collapseAll();
    },
    
    hideNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuShowLink]);
        ActiveForm.prototype.hideNewItemForm(this.nodes.menuCancelLink, this.nodes.menuForm); 
    },
    
    save: function()
    {
        var self = this;
        
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        var action = this.nodes.form.elements.namedItem('ID').value
            ? Backend.Shipment.Links.update
            : Backend.Shipment.Links.create;
            
        new Ajax.Request(action, {
            method: 'post',
            parameters: Form.serialize(this.nodes.form),
            onSuccess: function(response) { 
                var response = eval("(" + response.responseText + ")");
                self.afterSave(response);     
            }
        });

        console.info('save');
    },
    
    afterSave: function(response)
    {
        if(response.status == 'success')
        {
            ActiveForm.prototype.resetErrorMessages(this.nodes.form);
            if(!this.nodes.form.elements.namedItem('ID').value)
            {
                var title = '<h3 class="orderShipment_title">' + this.nodes.form.elements.namedItem('shippingServiceID').options[this.nodes.form.elements.namedItem('shippingServiceID').selectedIndex].text + '</h3>';
                var stats = $("orderShipment_" + this.nodes.form.elements.namedItem('orderID').value + "_info_empty").innerHTML;
                var ul = '<ul id="orderShipmentsItems_list_' + this.nodes.form.elements.namedItem('orderID').value + '_' +response.shipment.ID + '" class="activeList_add_sort activeList_add_delete orderShipmentsItem activeList_accept_orderShipmentsItem"></ul>'
                
                var li = this.shipmentsActiveList.addRecord(response.shipment.ID, title + stats + ul);

                ActiveList.prototype.getInstance($('orderShipmentsItems_list_' + this.nodes.form.elements.namedItem('orderID').value + '_' + response.shipment.ID), Backend.OrderedItem.activeListCallbacks);
                Element.addClassName(li, this.prefix  + 'item');
                                
                ActiveList.prototype.recreateVisibleLists();

                // Old shipment changes
                $A(["amount", 'shippingAmount', 'taxAmount', 'total']).each(function(type)
                {
                    var price = li.down(".shipment_" + type);
                        price.down('.pricePrefix').innerHTML = response.oldShipment.prefix;
                        price.down('.price').innerHTML = parseFloat(response.oldShipment[type]);
                        price.down('.priceSuffix').innerHTML = response.oldShipment.suffix;
                });
                
                Element.addClassName(li, 'orderShipment');
                
                Backend.OrderedItem.updateReport($("orderShipment_report_" + this.nodes.form.elements.namedItem('orderID').value));

                this.shipmentsActiveList.highlight(li);
                this.hideNewForm();
            }
            else
            {
                Form.State.backup(this.nodes.form);
                this.servicesActiveList.toggleContainer(this.nodes.root.up('li'), 'edit');
            }
        }
        else
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
        }
    },
    
    cancel: function()
    {
        if(!this.nodes.form.elements.namedItem('ID').value)
        {
            this.hideNewForm();
        }
        else
        {
            this.servicesActiveList.toggleContainerOff(this.nodes.root.up('.activeList_editContainer'));
            Form.State.restore(this.nodes.form);
        }
    },
    
    addNewProductToShipment: function(productID, orderID)
    {
        console.info(productID);
        
        var self = this;
        new Ajax.Request(Backend.OrderedItem.Links.createNewItem + "/?productID=" + productID + "&orderID=" + orderID, {
           method: 'get',
           onSuccess: function(response) {
               var evaluatedResponse;
               try
               {
                   evaluatedResponse = eval("(" + response.responseText + ")");
               
                   if(evaluatedResponse && evaluatedResponse.error && evaluatedResponse.error.length > 0)
                   {
                       // error
                       new Backend.SaveConfirmationMessage($('productRelationshipMsg_' + productID), { message: evaluatedResponse.error, type: 'red' });
                   }
                   else
                   {
                       var relatedList = ActiveList.prototype.getInstance($("productRelationship_list_" + productID + "_"));
                       relatedList.addRecord(relatedProductID, response.responseText, true);
                        var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
                       tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') + 1);
                   }
                   } 
               catch(e)
               {
                   console.info(e);
               }
           }
        });
    },
    
    
    toggleUSPS: function(cancel)
    {       
       var self = this;
       var uspsLink = $("orderShipment_change_usps_" + this.nodes.form.elements.namedItem('ID').value);
       var usps = $("orderShipment_USPS_" + this.nodes.form.elements.namedItem('ID').value);
       var uspsSelect = $("orderShipment_USPS_" + this.nodes.form.elements.namedItem('ID').value + "_select");
       
        if(usps.style.display == 'none')
        {
            Form.State.backup(this.nodes.form);
            
            new Ajax.Request(Backend.Shipment.Links.getAvailableServices + "/" + this.nodes.form.elements.namedItem('ID').value, {
               onSuccess: function(response) {
                   var response = eval("(" + response.responseText + ")");
                   
                   uspsLink.hide();
                   usps.show();   
                   uspsSelect.options.length = 0;
                   
                   uspsSelect.services = response.services;
                   $H(response.services).each(function(service)
                   {
                       uspsSelect.options[uspsSelect.options.length] = new Option(service.value.name, service.value.ID);
                       
                       if(service.value.ID == self.nodes.form.elements.namedItem('shippingServiceID').value)
                       {
                           uspsSelect.options[uspsSelect.options.length - 1].selected = true;
                       }
                   });
               } 
            });
        } 
        else 
        { 
           uspsLink.show();
           usps.hide();   
           
           if(!cancel)
           {
               new Ajax.Request(Backend.Shipment.Links.changeService + "/" + this.nodes.form.elements.namedItem('ID').value + "?serviceID=" + this.nodes.form.elements.namedItem('USPS').value, {
                  onSuccess: function(response) {
                      var response = eval("(" + response.responseText + ")");
                      
                      self.nodes.form.elements.namedItem('shippingServiceID').value = response.shipment.ShippingService.ID;
                  }
               });
           }
           else
           {
                this.setShippingAmount(uspsSelect.services[this.nodes.form.elements.namedItem('shippingServiceID').value].shipment.shippingAmount);
                Backend.OrderedItem.updateReport($("orderShipment_report_" + this.nodes.form.elements.namedItem('orderID').value));
           }
        }
    },
    
    USPSChanged: function()
    {
        var select = this.nodes.form.elements.namedItem('USPS');
        this.setShippingAmount(select.services[select.value].shipment.shippingAmount);
        this.setTaxAmount(select.services[select.value].shipment.taxAmount);
        this.setTotal(select.services[select.value].shipment.total);
        
        Backend.OrderedItem.updateReport($("orderShipment_report_" + this.nodes.form.elements.namedItem('orderID').value));
    },
    
    
    changeStatus: function()
    {
        console.info('changeStatus');
        
        var select = this.nodes.form.elements.namedItem('status');
        
        var proceed = false;
        switch(select.value)
        {
            case "0": 
                proceed = confirm(Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToNew); 
                break;
            case "1": 
                proceed = confirm(Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToPending); 
                break;
            case "2": 
                proceed = confirm(Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToAwaiting); 
                break;
            case "3": 
                proceed = confirm(Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToShipped); 
                break;
        }
        
        if(!proceed || (3 == select.value && !confirm(Backend.Shipment.Messages.youWontBeAableToRevertStatusFromShipped)))
        {
            select.value = select.lastValue;
            return;
        }
        
        new Ajax.Request(Backend.Shipment.Links.changeStatus + "/" + this.nodes.form.elements.namedItem('ID').value + "?status=" + this.nodes.form.elements.namedItem('status').value, {
           onSuccess: function(response) {
               var response = eval("(" + response.responseText + ")");
               
               select.lastValue = select.value;
               
               if(3 == select.value)
               {
               }
           }
        });
        
    },
    
    recalculateTotal: function()
    {
        // Recalculate subtotal 
        var subtotal = 0;
        document.getElementsByClassName('item_subtotal', this.nodes.root).each(function(itemSubtotal)
        {
            subtotal += parseFloat(itemSubtotal.down('.price').innerHTML);  
        });
        this.nodes.root.down(".shipment_amount").down('price').innerHTML = Math.round(subtotal * 100) / 100;
        
        
        // Recalculate total
        var total = 0;
        total += this.getAmount();
        total += this.getTaxAmount();
        total += this.getShippingAmount();
        
        this.setTotal(total)
    },
    
    setTotal: function(total)
    {
        this.nodes.root.down(".shipment_total").down('.price').innerHTML = Math.round(total * 100) / 100;
    },
    
    getTotal: function()
    {
        return parseFloat(this.nodes.root.down(".shipment_total").down('.price').innerHTML);
    },
    
    setAmount: function(amount)
    {
        this.nodes.root.down(".shipment_amount").down('.price').innerHTML = Math.round(amount * 100) / 100;
    },
    
    getAmount: function()
    {
        return parseFloat(this.nodes.root.down(".shipment_amount").down('.price').innerHTML);
    },
    
    setTaxAmount: function(tax)
    {
        this.nodes.root.down(".shipment_taxAmount").down('.price').innerHTML = Math.round(tax * 100) / 100;
    },
    
    getTaxAmount: function()
    {
        return parseFloat(this.nodes.root.down(".shipment_taxAmount").down('.price').innerHTML);
    },
    
    setShippingAmount: function(shippingAmount)
    {
        this.nodes.root.down(".shipment_shippingAmount").down('.price').innerHTML = Math.round(shippingAmount * 100) / 100;
    },
    
    getShippingAmount: function()
    {
        return  parseFloat(this.nodes.root.down(".shipment_shippingAmount").down('.price').innerHTML);
    }
}



Backend.Shipment.Callbacks =
{
    beforeDelete: function(li) { 
        if(confirm(Backend.Shipment.Messages.areYouSureYouWantToDelete)) 
        {
            return Backend.Shipment.Links.remove + "/" + this.getRecordId(li);
        }
    },
    afterDelete: function(li, response) {
        if(!response.error) {
            this.remove(li);
            var orderID = this.getRecordId(li, 2);
            Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
        }
    },
    
    beforeEdit:     function(li) 
    {
        if(!Backend.Shipment.Controller.prototype.getInstance(li.down('.productRelationshipGroup_form')))
        {
            return Backend.Shipment.Links.edit + "/" + this.getRecordId(li);
        }
        else
        {
            with(Backend.Shipment.Controller.prototype.getInstance(li.down('.productRelationshipGroup_form')))
            {
                if('block' != view.nodes.root.style.display) showForm();
                else hideForm();
            }
        }
    },
    afterEdit:      function(li, response) 
    { 
        try
        {
            response = eval("(" + response + ")");
        }
        catch(e)
        {
            console.info(e);
        }
    }
}