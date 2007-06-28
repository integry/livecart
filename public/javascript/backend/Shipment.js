Backend.OrderedItem = {
    activeListCallbacks: 
    {
        beforeDelete: function(li){ 
            if(confirm(Backend.OrderedItem.Messages.areYouSureYouWantToDelete)) 
            {
                return Backend.OrderedItem.Links.remove + "/" + this.getRecordId(li);
            }
        },
        afterDelete: function(li, response){
            response = eval("(" + response + ")");
            
            if(!response.error) {
                this.remove(li);
                var orderID = this.getRecordId(li, 3);
                Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
                
                var shipment = Backend.Shipment.prototype.getInstance('orderShipments_list_' + orderID + '_' + response.item.Shipment.ID);
                
                shipment.hideShippedStatus();

                shipment.setAmount(response.item.Shipment.amount);
                shipment.setTaxAmount(response.item.Shipment.taxAmount);
                shipment.setShippingAmount(response.item.Shipment.shippingAmount);
                shipment.setTotal(response.item.Shipment.total);
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
       if(input.value == input.lastValue) return;

       var price = input.up('tr').down('.orderShipmentsItem_info_price').down('.price');
       var total = input.up('tr').down('.orderShipmentsItem_info_total').down('.price');
       
       if(confirm(Backend.OrderedItem.Messages.areYouRealyWantToUpdateItemsCount))
       {   
           new Ajax.Request(Backend.OrderedItem.Links.changeItemCount + "/" + itemID + "?count=" + input.value, {
                method: 'post',
                onSuccess: function(response) { 
                    var response = eval("(" + response.responseText + ")");
                    var shipment = Backend.Shipment.prototype.getInstance('orderShipments_list_' + orderID + '_' + shipmentID);
                    
                    shipment.itemsActiveList.highlight($('orderShipmentsItems_list_' + orderID + '_' + shipmentID + '_' + itemID));
                    
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
            
            if(this.nodes.form)
            {
                this.itemsActiveList = ActiveList.prototype.getInstance(this.nodes.itemsList);
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

        var orderID = null;
        if(!this.nodes.form)
        {
            this.orderID = orderID = root.match(/orderShipments_new_(\d+)_form/)[1];
            
        }
        else
        {
            this.nodes.itemsList = this.nodes.root.down('.activeList');
            orderID = this.nodes.form.elements.namedItem('orderID').value;
        }
                
        this.nodes.shipmentsList = $('orderShipments_list_' + orderID);
    },
    
    bindEvents: function()
    {
    },
    
    save: function()
    {
        var self = this;
        
        if(this.nodes.form)
        {
            ActiveForm.prototype.resetErrorMessages(this.nodes.form);
            var action = Backend.Shipment.Links.update
            var data = Form.serialize(this.nodes.form);
        }
        else
        {
            var action = Backend.Shipment.Links.create + '/?orderID=' + this.orderID;
            var data = '';
        }
            
        new Ajax.Request(action, {
            method: 'post',
            parameters: data,
            onSuccess: function(response) { 
                var response = eval("(" + response.responseText + ")");
                self.afterSave(response);     
            }
        });

        console.info('save');
    },
    
    afterSave: function(response)
    {
        var self = this;
        
        if(response.status == 'success')
        {
            var shipmentItems = document.getElementsByClassName("orderShipmentsItem", self.nodes.shipmentsList);
            if(shipmentItems.length == 1)
            {
                var firstShipmentItems = ActiveList.prototype.getInstance(shipmentItems[0]);
                Element.addClassName(firstShipmentItems.ul, 'activeList_add_sort');
                firstShipmentItems.createSortable();
            }
            
            var controls = $("orderShipment_" + this.orderID + "_controls_empty").innerHTML;
            var stats = $("orderShipment_" + this.orderID + "_total_empty").innerHTML;
            
            var inputID = '<input type="hidden" name="ID" value="' + response.shipment.ID + '" />';
            var inputOrderID = '<input type="hidden" name="orderID" value="' + this.orderID + '" />';
            var inputServiceID = '<input type="hidden" name="shippingServiceID" value="' + response.shipment.ShippingService.ID + '" />';

            var ul = '<ul id="orderShipmentsItems_list_' + this.orderID + '_' + response.shipment.ID + '" class="activeList_add_sort activeList_add_delete orderShipmentsItem activeList_accept_orderShipmentsItem"></ul>'
            var li = this.shipmentsActiveList.addRecord(response.shipment.ID, '<form>' + inputID + inputOrderID + inputServiceID + controls + ul + stats + '</form>');

            var newShipmentActiveList = ActiveList.prototype.getInstance($('orderShipmentsItems_list_' + this.orderID + '_' + response.shipment.ID), Backend.OrderedItem.activeListCallbacks);
            Element.addClassName(li, this.prefix  + 'item');
            ActiveList.prototype.recreateVisibleLists();
            
            if(shipmentItems.length == 0)
            {
                Element.removeClassName(newShipmentActiveList.ul, 'activeList_add_sort');
                newShipmentActiveList.destroySortable();
            }

            // Old shipment changes
            $A(["amount", 'shippingAmount', 'taxAmount', 'total']).each(function(type)
            {
                var price = li.down(".shipment_" + type);
                    price.down('.pricePrefix').innerHTML = response.shipment.prefix;
                    price.down('.price').innerHTML = parseFloat(response.shipment[type]) || 0;
                    price.down('.priceSuffix').innerHTML = response.shipment.suffix;
            });


            li.down("#orderShipment_status_").id       = "orderShipment_status_" + response.shipment.ID;
            li.down("#orderShipment_change_usps_").id  = "orderShipment_change_usps_" + response.shipment.ID;
            li.down("#orderShipment_USPS_").id         = "orderShipment_USPS_" + response.shipment.ID;
            li.down("#orderShipment_USPS__submit").id  = "orderShipment_USPS_" + response.shipment.ID + "_submit";
            li.down("#orderShipment_USPS__cancel").id  = "orderShipment_USPS_" + response.shipment.ID + "_cancel";
            li.down("#orderShipment_USPS__select").id  = "orderShipment_USPS_" + response.shipment.ID + "_select";
            li.down("#orderShipment_addProduct_").id   = "orderShipment_addProduct_" + response.shipment.ID;
                        
            for(var z = -1; z <= 3; z++)
            {
                li.down("#orderShipment_status__" + z).id  = "orderShipment_status_" + response.shipment.ID + "_" + z;
            }

            li.down('form').elements.namedItem('ID').value = response.shipment.ID;
            li.down('form').elements.namedItem('orderID').value = this.orderID;
            li.down('form').elements.namedItem('shippingServiceID').value = "";
            $("orderShipment_status_" + response.shipment.ID + "_3").hide();


            Event.observe("orderShipment_change_usps_" + response.shipment.ID, 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + self.orderID + '_' + response.shipment.ID).toggleUSPS();  });
            Event.observe("orderShipment_USPS_" + response.shipment.ID + "_submit", 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + self.orderID + '_' + response.shipment.ID).toggleUSPS();  });       
            Event.observe("orderShipment_USPS_" + response.shipment.ID + "_cancel", 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + self.orderID + '_' + response.shipment.ID).toggleUSPS(true);  });
            Event.observe("orderShipment_USPS_" + response.shipment.ID + "_select", 'change', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + self.orderID + '_' + response.shipment.ID).USPSChanged();  });

            $("orderShipment_change_usps_" + response.shipment.ID).innerHTML = Backend.Shipment.Messages.shippingServiceIsNotSelected;

            $("orderShipment_status_" + response.shipment.ID).lastValue = $("orderShipment_status_" + response.shipment.ID).value;
            Event.observe("orderShipment_status_" + response.shipment.ID, 'change', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + self.orderID + '_' + response.shipment.ID).changeStatus();  });

            Event.observe($("orderShipment_addProduct_" + response.shipment.ID), 'click', function(e) {
                Event.stop(e);
                new Backend.OrderedItem.SelectProductPopup(
                    Backend.OrderedItem.Links.addProduct, 
                    Backend.OrderedItem.Messages.selectProductTitle, 
                    {
                        onProductSelect: function() { 
                            Backend.Shipment.prototype.getInstance('orderShipments_list_' + self.orderID + '_' + response.shipment.ID).addNewProductToShipment(this.productID, self.orderID); 
                        }
                    }
                );
            });
            
            
            Element.addClassName(li, 'orderShipment');
         
            Backend.OrderedItem.updateReport($("orderShipment_report_" + this.orderID));

            this.shipmentsActiveList.highlight(li);
        }
    },
    
    cancel: function()
    {
        if(!this.nodes.form)
        {
            this.hideNewForm();
        }
        else
        {
            this.servicesActiveList.toggleContainerOff(this.nodes.root.up('.activeList_editContainer'));
            Form.State.restore(this.nodes.form);
        }
    },
    
    addNewProductToShipment: function(productID)
    {
        var self = this;
        
        new Ajax.Request(Backend.OrderedItem.Links.createNewItem + "/?productID=" + productID + "&shipmentID=" + self.nodes.form.elements.namedItem('ID').value, {
           method: 'get',
           onSuccess: function(response) {
               var evaluatedResponse;
               try
               {
                   response = eval("(" + response.responseText + ")");
               
                   if(response.status == 'succsess')
                   {
                       var li = null;
                       var itemsList = ActiveList.prototype.getInstance($("orderShipmentsItems_list_" + self.nodes.form.elements.namedItem('orderID').value + "_" + self.nodes.form.elements.namedItem('ID').value));
                           
                       if(!response.item.isExisting)
                       {
                           li = itemsList.addRecord(response.item.ID, $("orderShipmentItem_" + self.nodes.form.elements.namedItem('orderID').value + "_empty").innerHTML);
                       }
                       else
                       {
                           li = $("orderShipmentsItems_list_" + self.nodes.form.elements.namedItem('orderID').value + "_" + response.item.Shipment.ID + "_" + response.item.ID);
                       }
                       
                       li.down('.orderShipmentsItem_info_sku').innerHTML = response.item.Product.sku;
                       li.down('.orderShipmentsItem_info_name').innerHTML = response.item.Product.name;
                       li.down('.orderShipmentsItem_count').value = response.item.count;
                       
                       var price = li.down('.orderShipmentsItem_info_price');
                       price.down('.pricePrefix').innerHTML = response.item.Shipment.prefix;
                       price.down('.price').innerHTML = response.item.price
                       price.down('.priceSuffix').innerHTML = response.item.Shipment.suffix;
                       
                       var priceTotal = li.down('.item_subtotal');
                       priceTotal.down('.pricePrefix').innerHTML = response.item.Shipment.prefix;
                       priceTotal.down('.price').innerHTML = Math.round(response.item.price * response.item.count * 100) / 100;
                       priceTotal.down('.priceSuffix').innerHTML = response.item.Shipment.suffix;
                       
                       var countInput = li.down('.orderShipmentsItem_count');
                       countInput.lastValue = countInput.value;
                       
                       Event.observe(countInput, 'focus', function(e) { window.lastFocusedItemCount = this; });      
                       Event.observe(countInput, 'keyup', function(e) { Backend.OrderedItem.updateProductCount(countInput, self.nodes.form.elements.namedItem('orderID').value, response.item.ID, response.item.Shipment.ID) });
                       Event.observe(countInput, 'blur', function(e) { Backend.OrderedItem.changeProductCount(countInput, self.nodes.form.elements.namedItem('orderID').value, response.item.ID, response.item.Shipment.ID) });
                       Event.observe(countInput, 'click', function(e) { 
                           var input = window.lastFocusedItemCount;
                           if(input.value != input.lastValue) { input.blur(); } 
                       });
                       
                       self.setAmount(response.item.Shipment.amount);
                       self.setTaxAmount(response.item.Shipment.taxAmount);
                       self.setShippingAmount(response.item.Shipment.shippingAmount);
                       self.setTotal(response.item.Shipment.total);
                       
                       itemsList.highlight(li)
                       
                       self.hideShippedStatus();
                   }
               } 
               catch(e)
               {
                   console.info(e);
               }
           }
        });
    },
    
    hideShippedStatus: function()
    {    
        var shippedOption = $("orderShipment_status_" + this.nodes.form.elements.namedItem('ID').value + "_3");
        if(!this.nodes.itemsList.down('li') || !this.nodes.form.elements.namedItem('shippingServiceID').value)
        {
            shippedOption.hide();
        }
        else
        {
            shippedOption.show();
        }
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
                       var prefix = service.value.shipment.prefix ? service.value.shipment.prefix : '';
                       var suffix = service.value.shipment.suffix ? service.value.shipment.suffix : '';
                       
                       uspsSelect.options[uspsSelect.options.length] = new Option(service.value.ShippingService.name_lang + " - " + prefix + service.value.shipment.shippingAmount + suffix, service.key);
                       
                       if(service.key == self.nodes.form.elements.namedItem('shippingServiceID').value)
                       {
                           uspsSelect.options[uspsSelect.options.length - 1].selected = true;
                       }
                   });
               } 
            });
        } 
        else 
        { 
           
           if(!cancel)
           {
               new Ajax.Request(Backend.Shipment.Links.changeService + "/" + this.nodes.form.elements.namedItem('ID').value + "?serviceID=" + this.nodes.form.elements.namedItem('USPS').value, {
                  onSuccess: function(response) {
                      var response = eval("(" + response.responseText + ")");
                      
                      console.info("orderShipment_change_usps_" + self.nodes.form.elements.namedItem('ID').value);
                      
                      self.nodes.form.elements.namedItem('shippingServiceID').value = response.shipment.ShippingService.ID;
                      $("orderShipment_change_usps_" + self.nodes.form.elements.namedItem('ID').value).innerHTML = response.shipment.ShippingService.name_lang;
                    
                      self.setAmount(response.shipment.amount);
                      self.setShippingAmount(response.shipment.shippingAmount);
                      self.setTaxAmount(response.shipment.taxAmount);
                      self.setTotal(response.shipment.total);
                                          
                      uspsLink.show();
                      usps.hide();   
                       
                      self.hideShippedStatus();
                  }
               });
           }
           else
           {
               uspsLink.show();
               usps.hide();   
               if(this.nodes.form.elements.namedItem('shippingServiceID').value)
               {
                   this.setShippingAmount(uspsSelect.services[this.nodes.form.elements.namedItem('shippingServiceID').value].shipment.shippingAmount);
               }
               
               Backend.OrderedItem.updateReport($("orderShipment_report_" + self.nodes.form.elements.namedItem('orderID').value));
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
        var self = this;
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
            case "-1": 
                proceed = confirm(Backend.Shipment.Messages.areYouSureYouWantToDeleteThisShipment); 
                break;
        }
        
        if(
            !proceed
            || (3 == select.value && !confirm(Backend.Shipment.Messages.youWontBeAableToRevertStatusFromShipped))
            || ("-1" == select.value && !confirm(Backend.Shipment.Messages.youWontBeAbleToUndelete))
        ) {
            select.value = select.lastValue;
            return;
        }
        
        if("-1" == select.value)
        {
            new Ajax.Request(Backend.Shipment.Links.remove + "/" + this.nodes.form.elements.namedItem('ID').value, {
               onSuccess: function(response) {
                   var response = eval("(" + response.responseText + ")");
                
                   var shipmentItems = document.getElementsByClassName("orderShipmentsItem", self.nodes.shipmentsList);
                   for(var i = 0; i < shipmentItems.length; i++)
                   {
                       if(shipmentItems[i] == self.nodes.root.down('.orderShipmentsItem'))
                       {
                           shipmentItems.splice(i, 1);
                           break;
                       }
                   }
                   
                   select.lastValue = select.value;
                   var orderID = self.nodes.form.elements.namedItem('orderID').value;
                   self.shipmentsActiveList.remove(self.nodes.root);
                                   
                   if(shipmentItems.length == 1)
                   {
                       var firstItemsList = ActiveList.prototype.getInstance(shipmentItems[0]);
                       Element.removeClassName(firstItemsList.ul, 'activeList_add_sort');
                       firstItemsList.destroySortable();
                   }
                   
                   Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
               }
            });
        }
        else
        {
            new Ajax.Request(Backend.Shipment.Links.changeStatus + "/" + this.nodes.form.elements.namedItem('ID').value + "?status=" + this.nodes.form.elements.namedItem('status').value, {
               onSuccess: function(response) {
                   var response = eval("(" + response.responseText + ")");
                   
                   select.lastValue = select.value;
                   
                   if(3 == select.value)
                   {
                       $(self.nodes.root.up('ul').id + '_shipped').appendChild(self.nodes.root);
                       
                       self.nodes.root.className = 'orderShipmentsItem';
                       
                       self.itemsActiveList.makeStatic();
                       self.nodes.root.style.paddingLeft = '0px';
                       self.nodes.root.down('a.orderShipment_change_usps').hide();
                       self.nodes.root.down('.orderShipment_controls').hide();
                       
                       document.getElementsByClassName("orderShipmentsItem_count", self.nodes.root).each(function(countInput)
                       {
                          countInput.hide(); 
                          countInput.up('.orderShipmentsItem_info_count').appendChild(document.createTextNode(countInput.value));
                       });
                   }
               }
            });
        }
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
    }
}