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
            }
        }
    },
    
    updateReport: function(report)
    {
		var id = report.up('.tabPageContainer').id.match(/\w+_(\d+)\w+/)[1]
		
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
		
		var totalAmount = Math.round(reportValues['totalAmount'] * 100) / 100;
        report.down('.orderShipment_report_total').down('.price').innerHTML = totalAmount;
		
		$("tabOrderInfo_" + id + "Content").down('.order_totalAmount').innerHTML = totalAmount;
		Backend.CustomerOrder.prototype.updateLog(id);
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
           new LiveCart.AjaxRequest(
               Backend.OrderedItem.Links.changeItemCount + "/" + itemID + "?count=" + input.value, 
               input.up('.orderShipmentsItem_info_count').down('.progressIndicator'),
               function(response) 
               { 
                    var response = eval("(" + response.responseText + ")");
                    var shipment = Backend.Shipment.prototype.getInstance('orderShipments_list_' + orderID + '_' + shipmentID);
                    
                    shipment.itemsActiveList.highlight($('orderShipmentsItems_list_' + orderID + '_' + shipmentID + '_' + itemID));
                    
                    shipment.setAmount(response.shipment.amount);
                    shipment.setTaxAmount(response.shipment.taxAmount);
                    shipment.setShippingAmount(response.shipment.shippingAmount);
                    shipment.setTotal(response.shipment.total);
                    
                    input.lastValue = input.value;
                   
                    Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
                    
                    ActiveList.prototype.getInstance(input.up('.activeList')).highlight(input.up('li'));
                }.bind(this)
           );
       }
       else
       {
           input.value = input.lastValue;
           Backend.OrderedItem.updateProductCount(input, orderID);
       }
   }
};


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
        new LiveCart.AjaxRequest(
            Backend.Shipment.Links.create + '/?orderID=' + this.orderID,
            $("orderShipments_new_" + this.orderID + "_indicator"),
            function(response) 
            { 
                var response = eval("(" + response.responseText + ")");
                this.afterSave(response);     
            }.bind(this)
        );
    },
    
    afterSave: function(response)
    {
        if(response.status == 'success')
        {
            var shipmentItems = document.getElementsByClassName("orderShipmentsItem", this.nodes.shipmentsList);
            if(shipmentItems.length >= 1)
            {
				shipmentItems.each(function(item) 
				{
	                var firstShipmentItems = ActiveList.prototype.getInstance(item);
	                Element.addClassName(firstShipmentItems.ul, 'activeList_add_sort');
	                firstShipmentItems.createSortable();
				}.bind(this));
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
            li.down("#orderShipment_status__feedback").id = "orderShipment_status_" + response.shipment.ID + "_feedback";
                        
                        
            for(var z = -1; z <= 3; z++)
            {
                li.down("#orderShipment_status__" + z).id  = "orderShipment_status_" + response.shipment.ID + "_" + z;
            }

            li.down('form').elements.namedItem('ID').value = response.shipment.ID;
            li.down('form').elements.namedItem('orderID').value = this.orderID;
            li.down('form').elements.namedItem('shippingServiceID').value = "";
            $("orderShipment_status_" + response.shipment.ID + "_3").hide();


            Event.observe("orderShipment_change_usps_" + response.shipment.ID, 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + this.orderID + '_' + response.shipment.ID).toggleUSPS();  }.bind(this));
            Event.observe("orderShipment_USPS_" + response.shipment.ID + "_submit", 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + this.orderID + '_' + response.shipment.ID).toggleUSPS();  }.bind(this));       
            Event.observe("orderShipment_USPS_" + response.shipment.ID + "_cancel", 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + this.orderID + '_' + response.shipment.ID).toggleUSPS(true);  }.bind(this));
            Event.observe("orderShipment_USPS_" + response.shipment.ID + "_select", 'change', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + this.orderID + '_' + response.shipment.ID).USPSChanged();  }.bind(this));

            $("orderShipment_change_usps_" + response.shipment.ID).innerHTML = Backend.Shipment.Messages.shippingServiceIsNotSelected;

            $("orderShipment_status_" + response.shipment.ID).lastValue = $("orderShipment_status_" + response.shipment.ID).value;
            Event.observe("orderShipment_status_" + response.shipment.ID, 'change', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('orderShipments_list_' + this.orderID + '_' + response.shipment.ID).changeStatus();  }.bind(this));

            var self = this;
            Event.observe($("orderShipment_addProduct_" + response.shipment.ID), 'click', function(e) {
                Event.stop(e);
                new Backend.SelectPopup(
                    Backend.OrderedItem.Links.addProduct, 
                    Backend.OrderedItem.Messages.selectProductTitle, 
                    {
                        onObjectSelect: function() { 
                           Backend.Shipment.prototype.getInstance('orderShipments_list_' + self.orderID + '_' + response.shipment.ID).addNewProductToShipment(this.objectID, self.orderID); 
                        }
                    }
                );
            });
            
            
            Element.addClassName(li, 'orderShipment');

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
        new LiveCart.AjaxRequest(
            Backend.OrderedItem.Links.createNewItem + "/?productID=" + productID + "&shipmentID=" + this.nodes.form.elements.namedItem('ID').value + "&orderID=" + this.nodes.form.elements.namedItem('orderID').value + "&downloadable=" + this.nodes.form.elements.namedItem('downloadable').value,
            false,
            function(response) 
            {
               response = eval("(" + response.responseText + ")");
           
               if(response.status == 'success')
               {
                   var li = null;
                   
                   var itemsList = ActiveList.prototype.getInstance($("orderShipmentsItems_list_" + this.nodes.form.elements.namedItem('orderID').value + "_" + this.nodes.form.elements.namedItem('ID').value));
                       
                   if(!response.item.isExisting)
                   {
                       li = itemsList.addRecord(response.item.ID, $("orderShipmentItem_" + this.nodes.form.elements.namedItem('orderID').value + "_empty").innerHTML);
                   }
                   else
                   {
                       li = $("orderShipmentsItems_list_" + this.nodes.form.elements.namedItem('orderID').value + "_" + response.item.Shipment.ID + "_" + response.item.ID);
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
                   Event.observe(countInput, 'keyup', function(e) { Backend.OrderedItem.updateProductCount(countInput, this.nodes.form.elements.namedItem('orderID').value, response.item.ID, response.item.Shipment.ID) }.bind(this));
                   Event.observe(countInput, 'blur', function(e) { Backend.OrderedItem.changeProductCount(countInput, this.nodes.form.elements.namedItem('orderID').value, response.item.ID, response.item.Shipment.ID) }.bind(this));
                   Event.observe(countInput, 'click', function(e) { 
                       var input = window.lastFocusedItemCount;
                       if(input.value != input.lastValue) { input.blur(); } 
                   });
                   
                   this.setAmount(response.item.Shipment.amount);
                   this.setTaxAmount(response.item.Shipment.taxAmount);
                   this.setShippingAmount(response.item.Shipment.shippingAmount);
                   this.setTotal(response.item.Shipment.total);
                   
                   itemsList.highlight(li)
                   
                   this.hideShippedStatus();
				   
                   Backend.OrderedItem.updateReport($("orderShipment_report_" + this.nodes.form.elements.namedItem('orderID').value));
               }
            }.bind(this)
        );
    },
    
    hideShippedStatus: function()
    {    
        var shippedOption = $("orderShipment_status_" + this.nodes.form.elements.namedItem('ID').value + "_3");
        
        if(shippedOption)
        {        
            if(!this.nodes.itemsList.down('li') || !this.nodes.form.elements.namedItem('shippingServiceID').value)
            {
                shippedOption.hide();
            }
            else
            {
                shippedOption.show();
            }
        }
    },
    
    toggleUSPS: function(cancel)
    {       
       var uspsLink = $("orderShipment_change_usps_" + this.nodes.form.elements.namedItem('ID').value);
       var usps = $("orderShipment_USPS_" + this.nodes.form.elements.namedItem('ID').value);
       var uspsSelect = $("orderShipment_USPS_" + this.nodes.form.elements.namedItem('ID').value + "_select");
       
        if(usps.style.display == 'none')
        {
            Form.State.backup(this.nodes.form);
            
            new LiveCart.AjaxRequest(
                Backend.Shipment.Links.getAvailableServices + "/" + this.nodes.form.elements.namedItem('ID').value, 
                usps.up().down('.progressIndicator'),
                function(response) {
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
                        
                        if(service.key == this.nodes.form.elements.namedItem('shippingServiceID').value)
                        {
                            uspsSelect.options[uspsSelect.options.length - 1].selected = true;
                        }
                    }.bind(this));
                }.bind(this) 
            );
        } 
        else 
        { 
           if(!cancel)
           {
               new LiveCart.AjaxRequest(
                   Backend.Shipment.Links.changeService + "/" + this.nodes.form.elements.namedItem('ID').value + "?serviceID=" + this.nodes.form.elements.namedItem('USPS').value,
                   usps.up().down('.progressIndicator'),
                   function(response) {
                       var response = eval("(" + response.responseText + ")");
                      
                       this.nodes.form.elements.namedItem('shippingServiceID').value = response.shipment.ShippingService.ID;
                       $("orderShipment_change_usps_" + this.nodes.form.elements.namedItem('ID').value).innerHTML = response.shipment.ShippingService.name_lang;
                    
                       this.setAmount(response.shipment.amount);
                       this.setShippingAmount(response.shipment.shippingAmount);
                       this.setTaxAmount(response.shipment.taxAmount);
                       this.setTotal(response.shipment.total);
                                           
                       uspsLink.show();
                       usps.hide();   
					   
                       Backend.OrderedItem.updateReport($("orderShipment_report_" + this.nodes.form.elements.namedItem('orderID').value));
                        
                       this.hideShippedStatus();
                  }.bind(this)
               );
           }
           else
           {
               uspsLink.show();
               usps.hide();   
               if(this.nodes.form.elements.namedItem('shippingServiceID').value)
               {
                   this.setShippingAmount(uspsSelect.services[this.nodes.form.elements.namedItem('shippingServiceID').value].shipment.shippingAmount);
               }
               
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
		
        var orderID = this.nodes.form.elements.namedItem('orderID').value;
        
        if("-1" == select.value)
        {
            new LiveCart.AjaxRequest(
                Backend.Shipment.Links.remove + "/" + this.nodes.form.elements.namedItem('ID').value,
                $("orderShipment_status_" + this.nodes.form.elements.namedItem('ID').value + "_feedback"),
                function(response) {
                   var response = eval("(" + response.responseText + ")");
                
                   var shipmentItems = document.getElementsByClassName("orderShipmentsItem", this.nodes.shipmentsList);
                   for(var i = 0; i < shipmentItems.length; i++)
                   {
                       if(shipmentItems[i] == this.nodes.root.down('.orderShipmentsItem'))
                       {
                           shipmentItems.splice(i, 1);
                           break;
                       }
                   }
                   
                   select.lastValue = select.value;
                   this.shipmentsActiveList.remove(this.nodes.root);
                                   
                   if(shipmentItems.length == 1)
                   {
                       var firstItemsList = ActiveList.prototype.getInstance(shipmentItems[0]);
                       Element.removeClassName(firstItemsList.ul, 'activeList_add_sort');
                       firstItemsList.destroySortable();
                   }
                   
                   Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
               }.bind(this)
            );
        }
        else
        {
            new LiveCart.AjaxRequest(
                Backend.Shipment.Links.changeStatus + "/" + this.nodes.form.elements.namedItem('ID').value + "?status=" + this.nodes.form.elements.namedItem('status').value,
                $("orderShipment_status_" + this.nodes.form.elements.namedItem('ID').value + "_feedback"),
                function(response) {
                   var response = eval("(" + response.responseText + ")");
                   
                   select.lastValue = select.value;
                   
                   if(3 == select.value)
                   {
                       $(this.nodes.root.up('ul').id + '_shipped').appendChild(this.nodes.root);
                       
                       this.nodes.root.className = 'orderShipmentsItem';
                       
                       this.itemsActiveList.makeStatic();
                       this.nodes.root.style.paddingLeft = '0px';
                       this.nodes.root.down('a.orderShipment_change_usps').hide();
                       this.nodes.root.down('.orderShipment_controls').hide();
                       
                       document.getElementsByClassName("orderShipmentsItem_count", this.nodes.root).each(function(countInput)
                       {
                          countInput.hide(); 
                          countInput.up('.orderShipmentsItem_info_count').appendChild(document.createTextNode(countInput.value));
                       });
                   }
               }.bind(this)
            );
        }
		
        Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
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