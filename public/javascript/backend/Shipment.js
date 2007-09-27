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
            try 
            { 
                response = eval('(' + response + ')'); 
            } 
            catch(e) 
            { 
                return false; 
            }
            
            if(!response.error) {
                var orderID = this.getRecordId(li, 3);
				var shipment = null;
				
				if(response.item.downloadable)
				{
                    var parent = $$("#tabOrderProducts_" + orderID + "Content .downloadableShipment li").first();
					shipment = Backend.Shipment.prototype.getInstance(parent);
					
					if(!parent.down(".activeList li"))
					{
					   Element.hide("order" + orderID + "_downloadableShipments");
					}
				}
				else
				{
					var parent = $(li.id.replace(/orderShipmentsItems_list_([\w\W]+)_\d+/, "orderShipments_list_$1"));
	                shipment = Backend.Shipment.prototype.getInstance(parent);
				}
				
                shipment.setAmount(response.item.Shipment.amount);
                shipment.setTaxAmount(response.item.Shipment.taxAmount);
                shipment.setShippingAmount(response.item.Shipment.shippingAmount);
                shipment.setTotal(response.item.Shipment.total);
				
				Backend.CustomerOrder.Editor.prototype.getInstance(orderID, false).toggleStatuses();
				shipment.toggleStatuses();
                Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
            }
			
			return false;
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
				
                Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
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
                    var shipment = null;

	                if(response.Shipment.downloadable)
	                {
	                    var parent = $$("#tabOrderProducts_" + response.Shipment.Order.ID + "Content .downloadableShipment li").first();
	                    shipment = Backend.Shipment.prototype.getInstance(parent);
	                }
	                else
	                {
	                    shipment = Backend.Shipment.prototype.getInstance('orderShipments_list_' + response.Shipment.Order.ID + '_' + response.Shipment.ID);
	                }
					
                    var li = $('orderShipmentsItems_list_' + response.Shipment.Order.ID + '_' + response.Shipment.ID + '_' + response.ID);
					if(!response.Shipment.isDeleted)
					{
	                    shipment.itemsActiveList.highlight(li);
	                    
	                    shipment.setAmount(response.Shipment.amount);
	                    shipment.setTaxAmount(response.Shipment.taxAmount);
	                    shipment.setShippingAmount(response.Shipment.shippingAmount);
	                    shipment.setTotal(response.Shipment.total);
	                    
	                    input.lastValue = input.value;
					}
					else
					{
                        shipment.itemsActiveList.remove(li);
					}
                       
                    Backend.OrderedItem.updateReport($("orderShipment_report_" + response.Shipment.Order.ID));
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
    STATUS_NEW: 0,
    STATUS_PROCESSING: 1,
    STATUS_AWAITING: 2,
    STATUS_SHIPPED: 3,
    STATUS_RETURNED: 4,
    STATUS_DELETE: -1,
	
	instances: {},
	    
    initialize: function(root, options)
    {
		this.options = options || {};
        this.findUsedNodes(root);
        this.bindEvents();
        this.shipmentsActiveList = ActiveList.prototype.getInstance(this.nodes.shipmentsList);
        
        if(this.nodes.form)
        {
			this.itemsActiveList = ActiveList.prototype.getInstance(this.nodes.itemsList, Backend.OrderedItem.activeListCallbacks);	
			// this.itemsActiveList.makeStatic();
			
	        // Remember last status value
	        this.nodes.status.lastValue = this.nodes.status.value; 
            this.toggleStatuses();
			Form.State.backup(this.nodes.form);
        }
    },

    toggleStatuses: function()
    {
		if(!this.nodes.form) return;
		
        var statusValue = parseInt(this.nodes.status.value);
        
        var migrations = {}
        migrations[this.STATUS_NEW]                     = [this.STATUS_NEW,this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_SHIPPED,this.STATUS_DELETE]
        migrations[this.STATUS_PROCESSING]              = [this.STATUS_NEW,this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_SHIPPED,this.STATUS_DELETE]
        migrations[this.STATUS_AWAITING]                = [this.STATUS_NEW,this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_SHIPPED,this.STATUS_DELETE]
        migrations[this.STATUS_SHIPPED]                 = [this.STATUS_SHIPPED,this.STATUS_RETURNED]
        migrations[this.STATUS_RETURNED]                = [this.STATUS_NEW,this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_RETURNED,this.STATUS_SHIPPED,this.STATUS_DELETE]
        migrations[this.STATUS_DELETE]                  = [];
		
        $A(this.nodes.status.options).each(function(option) {
            if(migrations[statusValue].include(parseInt(option.value)))
            {
                Element.show(option);
            }
            else
            {
                Element.hide(option);
            }
        }.bind(this));
		
		if(!this.options.isShipped && (
		      this.nodes.itemsList.childElements().size() == 0 || 
			 !this.nodes.form.elements.namedItem('USPS').value)
		){
			Element.hide(this.nodes.status.options[this.STATUS_SHIPPED]);
		}
    },
	
    getInstance: function(rootNode, options)
    {
        var rootId = $(rootNode).id;
        if(!Backend.Shipment.prototype.instances[rootId])
        {
            Backend.Shipment.prototype.instances[rootId] = new Backend.Shipment(rootId, options);
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
            this.orderID = this.nodes.root.id.match(/orderShipments_new_(\d+)_form/)[1];  
            this.ID = false;      
        }
        else
        {
            this.nodes.itemsList = this.nodes.root.down('ul');
            this.orderID = this.nodes.form.elements.namedItem('orderID').value;
            this.ID = this.nodes.form.elements.namedItem('ID').value;
            this.nodes.status = this.nodes.form.elements.namedItem('status');    
        }
                
		
        this.nodes.shipmentsList = $(this.options.isShipped ? 'orderShipments_list_' + this.orderID + '_shipped' : 'orderShipments_list_' + this.orderID);
    },
    
    bindEvents: function()
    {
		if(this.nodes.form)
		{   
            // Bind service changes                
            Event.observe("orderShipment_change_usps_" + this.ID, 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance(e.target.up("li")).toggleUSPS(); }.bind(this)); 
            Event.observe("orderShipment_USPS_" + this.ID + "_submit", 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance(e.target.up("li")).toggleUSPS(); }.bind(this)); 
            Event.observe("orderShipment_USPS_" + this.ID + "_cancel", 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance(e.target.up("li")).toggleUSPS(true); }.bind(this)); 
            Event.observe("orderShipment_USPS_" + this.ID + "_select", 'change', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance(e.target.up("li")).USPSChanged(); }.bind(this)); 
            
            // Bind status changes
            Event.observe("orderShipment_status_" + this.ID, 'change', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance(e.target.up("li")).changeStatus(); }.bind(this)); 

			// Bind Items events
			this.nodes.itemsList.childElements().each(function(itemLi)
			{
				var itemID = itemLi.id.match(/\d+$/)[0];
				
				var countNode = $("orderShipmentsItem_count_" + itemID);
                var itemTable = $("orderShipmentsItems_list_" + this.orderID + "_" + this.ID + "_" + itemID);
				
                countNode.lastValue = countNode.value; 
		        Event.observe(countNode, 'focus', function(e) { window.lastFocusedItemCount = this; }); 
		        Event.observe(countNode, 'keyup', function(e) {  Backend.OrderedItem.updateProductCount(this, this.orderID, itemID,  this.ID) }); 
		        Event.observe(countNode, 'blur', function(e) { Backend.OrderedItem.changeProductCount(this, this.orderID, itemID,  this.ID) }, false); 
		        Event.observe(itemTable, 'click', function(e) 
		        { 
		            var input = window.lastFocusedItemCount; 
		            if(input && input.value != input.lastValue) 
		            { 
		                input.blur(); 
		            } 
		        }.bind(this)); 
			}.bind(this));
		}
    },
	
    save: function(afterCallback, disableIndicator)
    {
        new LiveCart.AjaxRequest(
            Backend.Shipment.Links.create + '/?orderID=' + this.orderID + (disableIndicator ? "&noStatus=1" : ""),
            !disableIndicator ? $("orderShipments_new_" + this.orderID + "_indicator") : null,
            function(response) 
            { 
                var response = eval("(" + response.responseText + ")");
                this.afterSave(response);  
				
				if(afterCallback)
				{
				    afterCallback(response);   
				}
            }.bind(this)
        );
    },
    
    afterSave: function(response)
    {
        if(response.status == 'success')
        {		
            var shipmentItems = $$("#tabOrderProducts_" + this.id + "Content .shippableShipments .orderShipment");
            if(shipmentItems.length >= 1)
            {
                var firstShipment = ActiveList.prototype.getInstance(shipmentItems.first());
                Element.addClassName(firstShipment.ul, 'activeList_add_sort');
                firstShipment.createSortable();
            }
            
            var controls = $("orderShipment_" + this.orderID + "_controls_empty").innerHTML;
            var stats = $("orderShipment_" + this.orderID + "_total_empty").innerHTML;
            var legend = '<legend>' + Backend.Shipment.Messages.shipment + ' #' + response.shipment.ID + '</legend>';
            
            var inputID = '<input type="hidden" name="ID" value="' + response.shipment.ID + '" />';
            var inputOrderID = '<input type="hidden" name="orderID" value="' + this.orderID + '" />';
            var inputServiceID = '<input type="hidden" name="shippingServiceID" value="' + response.shipment.ShippingService.ID + '" />';
			
            var ul = '<ul id="orderShipmentsItems_list_' + this.orderID + '_' + response.shipment.ID + '" class="activeList_add_sort activeList_add_delete orderShipment activeList_accept_orderShipmentsItem"></ul>'
            var li = this.shipmentsActiveList.addRecord(response.shipment.ID, '<fieldset class="shipmentContainer">' + legend + '<form>' + inputID + inputOrderID + inputServiceID + controls + ul + stats + '</form></fieldset>');

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
            li.down("#orderShipment_status__feedback").id = "orderShipment_status_" + response.shipment.ID + "_feedback";
                        
                        
            for(var z = -1; z <= 3; z++)
            {
                li.down("#orderShipment_status__" + z).id  = "orderShipment_status_" + response.shipment.ID + "_" + z;
            }

            li.down('form').elements.namedItem('ID').value = response.shipment.ID;
            li.down('form').elements.namedItem('orderID').value = this.orderID;
            li.down('form').elements.namedItem('shippingServiceID').value = "";
            $("orderShipment_status_" + response.shipment.ID + "_3").hide();
			
			$("orderShipment_status_" + response.shipment.ID ).value = response.shipment.status;
            $("orderShipment_change_usps_" + response.shipment.ID).innerHTML = Backend.Shipment.Messages.shippingServiceIsNotSelected;

            Element.addClassName(li, 'orderShipment');
			
            $("order" + this.orderID + "_shippableShipments").show();
			
            this.shipmentsActiveList.highlight(li);
			
            if(window.selectPopupWindow)
            {
                Backend.SelectPopup.prototype.popup.location.reload();
				Backend.SelectPopup.prototype.popup.outerHeight = Backend.Shipment.prototype.getPopupHeight();
            }
			
			Backend.Shipment.prototype.toggleControls(this.orderID);
            Backend.Shipment.prototype.updateOrderStatus(this.orderID);
			
			Backend.Shipment.prototype.getInstance(li);
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
    
    addNewProductToShipment: function(productID, orderID, popup)
    {
        new LiveCart.AjaxRequest(
            Backend.OrderedItem.Links.createNewItem + "/?productID=" + productID + "&shipmentID=" + this.nodes.form.elements.namedItem('ID').value + "&orderID=" + this.nodes.form.elements.namedItem('orderID').value + "&downloadable=" + this.nodes.form.elements.namedItem('downloadable').value,
            false,
            function(response) 
            {
               response = eval("(" + response.responseText + ")");
     
	           try
			   {
                   popup.getElementById('productIndicator_' + productID).hide();
               }
			   catch(e)
			   {
			   	   // Popup is being refreshed
			   }
               if(response.status == 'success')
               {
			   	   this.nodes.root.up(".shipmentCategoty").show();
				
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
                   li.down('.orderShipmentsItem_info_name').down('a').innerHTML = response.item.Product.name_lang;
                   li.down('.orderShipmentsItem_info_name').down('a').href += response.item.Product.ID;
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
                   
                   Backend.OrderedItem.updateReport($("orderShipment_report_" + this.nodes.form.elements.namedItem('orderID').value));
				   
                   this.toggleStatuses();
               }
            }.bind(this)
        );
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
   
                       this.toggleStatuses();
                       ActiveList.prototype.highlight(this.nodes.root.down('fieldset'));
					   
                       Backend.OrderedItem.updateReport($("orderShipment_report_" + this.nodes.form.elements.namedItem('orderID').value));
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
    
    afterChangeStatus: function(response)
	{
        var orderID = this.nodes.form.elements.namedItem('orderID').value;
        var select = this.nodes.form.elements.namedItem('status');
		
        if(response.status == 'success')
        {  
			if(response.deleted)
			{
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
		                        
				if(this.shipmentsActiveList.ul.childElements().size())
				{
					this.shipmentsActiveList.ul.up('.shipmentCategoty').hide();
				}
								
		        if(shipmentItems.length == 1)
		        {
		            var firstItemsList = ActiveList.prototype.getInstance(shipmentItems[0]);
		            Element.removeClassName(firstItemsList.ul, 'activeList_add_sort');
		            firstItemsList.destroySortable();
		        }            
	        }
			else
			{                  
               ActiveList.prototype.highlight(this.nodes.root.down('fieldset'));
           
               if(3 == select.value)
               {
                   var newList = $(this.nodes.root.up('ul').id + '_shipped');
                   var oldList = this.nodes.root.up('ul');
				   
                   newList.appendChild(this.nodes.root);
                   
                   if(!oldList.childElements().size()) oldList.up('.shipmentCategoty').hide();
                   if(newList.childElements().size()) newList.up('.shipmentCategoty').show();
                   
                   this.itemsActiveList.makeStatic();
                   this.nodes.root.style.paddingLeft = '0px';
                   this.nodes.root.down('a.orderShipment_change_usps').hide();
                   
                   document.getElementsByClassName("orderShipmentsItem_count", this.nodes.root).each(function(countInput)
                   {
                      countInput.hide(); 
                      countInput.up('.orderShipmentsItem_info_count').appendChild(document.createTextNode(countInput.value));
                   });
        
                   $("order" + this.nodes.form.elements.namedItem('orderID').value + "_shippedShipments").show();
               }
               else if(4 == select.value)
               {
			   	   var newList = $(this.nodes.root.up('ul').id.replace(/_shipped/, ""));
				   var oldList = this.nodes.root.up('ul');
				   
                   newList.appendChild(this.nodes.root);
				   
                   if(!oldList.childElements().size()) oldList.up('.shipmentCategoty').hide();
                   if(newList.childElements().size()) newList.up('.shipmentCategoty').show();
				   
                   this.itemsActiveList.makeActive();
                   
                   this.nodes.root.down('a.orderShipment_change_usps').show();
                   document.getElementsByClassName("orderShipmentsItem_count", this.nodes.root).each(function(countInput)
                   {
                      countInput.show(); 
                   });

                   Backend.Shipment.prototype.toggleControls(orderID);
               }
                   
               select.lastValue = select.value;
		   }
		   
	       try
	       {       
	          if(window.selectPopupWindow)
	          {
	              Backend.SelectPopup.prototype.popup.location.reload();
	              Backend.SelectPopup.prototype.popup.outerHeight = Backend.Shipment.prototype.getPopupHeight();
	          }   
	       }
	       catch(e) { }
				
		   setTimeout(function() { Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID)) }.bind(this), 50);
	       Backend.CustomerOrder.Editor.prototype.getInstance(orderID, false).toggleStatuses();
           Backend.Shipment.prototype.updateOrderStatus(this.orderID);
	       this.toggleStatuses();
	   }
       else
       {
           ActiveList.prototype.highlight(this.nodes.root.down('fieldset'), 'red');
           select.value = select.lastValue;
       }
	},
	
	
    changeStatus: function(confirmed)
    {
        var select = this.nodes.form.elements.namedItem('status');
        
		var proceed = false;
		if(confirmed)
		{
			proceed = true;
		}
        else
		{
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
	            case "4": 
	                proceed = confirm(Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToReturned); 
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
		}
		
        var url = "";
        if("-1" == select.value)
        {
			url = Backend.Shipment.Links.remove + "/" + this.nodes.form.elements.namedItem('ID').value;
		}
		else
		{
			url = Backend.Shipment.Links.changeStatus + "/" + this.nodes.form.elements.namedItem('ID').value + "?status=" + this.nodes.form.elements.namedItem('status').value;
        }
		
        new LiveCart.AjaxRequest(
            url,
            $("orderShipment_status_" + this.nodes.form.elements.namedItem('ID').value + "_feedback"),
            function(response) {
               var response = eval("(" + response.responseText + ")");
            
               this.afterChangeStatus(response);
           }.bind(this)
        );
    },
    
	updateOrderStatus: function(orderID)
	{
       setTimeout(function()
       {
            var orderStatus = $("order_" + orderID + "_status");
            var orderForm = $("orderInfo_" + orderID + "_form");
            var statuses = $$("#tabOrderProducts_" + orderID + "Content .shippableShipments select[name=status], #tabOrderProducts_" + orderID + "Content .shippedShipments select[name=status]")
            
            var lowestStatus = 100;
            var isNew = true;
            statuses.each(function(statusElement)
            {
                if(statusElement.value < lowestStatus)
                {
                    
                    lowestStatus = statusElement.value;
                }

                if(statusElement.value > Backend.Shipment.prototype.STATUS_NEW)
                {
                    isNew = false
                }
            });
            
            var newOrderStatus = (!isNew && lowestStatus == Backend.Shipment.prototype.STATUS_NEW) ? Backend.Shipment.prototype.STATUS_PROCESSING : lowestStatus;
            if(newOrderStatus != orderStatus.value)
            {
                orderStatus.value = newOrderStatus;
            }
       }.bind(this), 100);
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
    },
    
    /**
     *  Do not display shipment status controls for orders that have only one shipment
     */ 
    toggleControls: function(orderID) 
    {
	    var shippableControls = document.getElementsByClassName("orderShipment_controls", $("order" + orderID + "_shippableShipments"));
        var shippedControls = document.getElementsByClassName("orderShipment_controls", $("order" + orderID + "_shippedShipments"));
		var allControls = $A(shippableControls.concat(shippedControls));
       
	    var size = shippableControls.size();
		var shippedSize = shippedControls.size();
		
            
        if(shippedSize > 0)
        {
            shippableControls.invoke("show");
        }
		else
		{
	        shippableControls.each(function(otherControls)
	        {
				if(otherControls.down("select[name=status]").value == -1)
				{
					size--;
				}
				
				console.info(size);
				
	            if(size <= 1) 
				{
					shippableControls.invoke("hide");
					throw $break;
				}
		        else 
				{
					otherControls.show();
				}
	        }.bind(this));
        }
    },
	
	getPopupHeight: function()
	{
        var orderID = Backend.CustomerOrder.Editor.prototype.getCurrentId();
        var ulList = $("orderShipments_list_" + orderID).childElements();

	    return (550 + ($A(ulList).size() > 1 ? (50 + $A(ulList).size() * 30) : 0) );
	},
	
	initializePage: function(orderID, downloadableShipmentID)
	{
        orderID = parseInt(orderID);
		downloadableShipmentID = parseInt(downloadableShipmentID);
		
        Backend.Shipment.prototype.toggleControls(orderID);
		
	    window.onbeforeunload = function() 
	    { 	        
	        var shipmentsContainer = $('tabOrderProducts_' + orderID + 'Content'); 
	        var ordersManagerContainer = $("orderManagerContainer"); 
	        
	        if(ordersManagerContainer.style.display != 'none' && shipmentsContainer && shipmentsContainer.style.display != 'none') 
	        { 
	            var customerOrder = Backend.CustomerOrder.Editor.prototype.getInstance(orderID); 
	            if (customerOrder.hasEmptyShipments())
	            {
                    return Backend.Shipment.Messages.emptyShipmentsWillBeRemoved; 
                }                
	        } 
	    }.bind(this);
	               
        
        Event.observe(window, "unload", function() 
        { 
			var orderID = Backend.CustomerOrder.Editor.prototype.getCurrentId();			
            
            var shipmentsContainer = $('tabOrderProducts_' + orderID + 'Content'); 
            var ordersManagerContainer = $("orderManagerContainer"); 
            
            if(ordersManagerContainer.style.display != 'none' && shipmentsContainer && shipmentsContainer.style.display != 'none') 
            { 
                var customerOrder = Backend.CustomerOrder.Editor.prototype.getInstance(orderID); 
                customerOrder.removeEmptyShipmentsFromHTML(); 
            }
		}, false); 
				     
        Event.observe("order" + orderID + "_addProduct", 'click', function(e) 
        { 
            Event.stop(e); 
            
            var ulList = $("orderShipments_list_" + orderID).childElements();
            
            var showPopup = function()
            {
                new Backend.SelectPopup( Backend.OrderedItem.Links.addProduct, Backend.OrderedItem.Messages.selectProductTitle, 
                { 
                    onObjectSelect: function() 
                    { 
                        var form = this.popup.document.getElementById("availableShipments");
                        
                        $A(form.getElementsByTagName('input')).each(function(element) {
                            if(element.checked || !shipmentID) shipmentID = element.value;
                        }.bind(this)); 
    
                        if(!this.downloadable)
                        {
							var shipmentContainer = $('orderShipments_list_' + orderID + '_' + shipmentID);
							
							var shipmentID = null;
							if(shipmentContainer)
							{
                                Backend.Shipment.prototype.getInstance(shipmentContainer).addNewProductToShipment(this.objectID, orderID, this.popup.document);
                            }
							else
							{
				                var newForm = Backend.Shipment.prototype.getInstance("orderShipments_new_" + orderID + "_form");
				                newForm.save(function(a, b, c)
				                {
                                    var shipmentContainer = $('orderShipments_list_' + orderID + '_' + a.shipment.ID);
									var shipment = Backend.Shipment.prototype.getInstance(shipmentContainer)
									
									shipment.addNewProductToShipment(this.objectID, orderID, this.popup.document);
				                }.bind(this), true);
								
								window.selectPopupWindow
							}
                        }
						
                        else
                        {
                            Backend.Shipment.prototype.getInstance('orderShipments_list_downloadable_' + orderID + '_' + downloadableShipmentID).addNewProductToShipment(this.objectID, orderID, this.popup.document);
                        }
                    },
                    
                    height: Backend.Shipment.prototype.getPopupHeight() - (ulList.size() > 0 ? 30 : 0)
                });
            }.bind(this);
            
            showPopup();
        }.bind(this)); 
                        
        Event.observe("orderShipments_new_" + orderID + "_show", "click", function(e) 
        { 
            Event.stop(e); 
			
			var menu = new ActiveForm.Slide("orderShipments_menu_" + orderID);
			menu.show(null, "orderShipments_new_" + orderID + "_controls");
        }.bind(this)); 
        
        Event.observe("orderShipments_new_" + orderID + "_cancel", "click", function(e) 
        { 
            Event.stop(e); 
            var menu = new ActiveForm.Slide("orderShipments_menu_" + orderID);
            menu.hide(null, "orderShipments_new_" + orderID + "_controls");
        }.bind(this)); 
            
        Event.observe("orderShipments_new_" + orderID + "_submit", "click", function(e) 
        { 
            Event.stop(e); 
            var menu = new ActiveForm.Slide("orderShipments_menu_" + orderID);
            menu.hide(null, "orderShipments_new_" + orderID + "_controls");
            Backend.Shipment.prototype.getInstance("orderShipments_new_" + orderID + "_form").save(); ; 
        }.bind(this)); 
	
//        Backend.Shipment.prototype.toggleControls(orderID);
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
        try 
        { 
            response = eval('(' + response + ')'); 
        } 
        catch(e) 
        { 
            return false; 
        }
		
        if(!response.error) {
            var orderID = this.getRecordId(li, 2);
            Backend.OrderedItem.updateReport($("orderShipment_report_" + orderID));
			
			return true;
        }
		
		return false;
    }
}
