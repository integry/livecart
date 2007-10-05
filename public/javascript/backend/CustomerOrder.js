Backend.CustomerOrder = Class.create();
Backend.CustomerOrder.prototype = 
{
  	Links: {},
    Messages: {},
    
    treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(groups)
	{
		Backend.CustomerOrder.prototype.treeBrowser = new dhtmlXTreeObject("orderGroupsBrowser","","", false);
        Backend.Breadcrumb.setTree(Backend.CustomerOrder.prototype.treeBrowser);
		
		Backend.CustomerOrder.prototype.treeBrowser.setOnClickHandler(this.activateGroup);
		
		Backend.CustomerOrder.prototype.treeBrowser.def_img_x = 'auto';
		Backend.CustomerOrder.prototype.treeBrowser.def_img_y = 'auto';
				
		Backend.CustomerOrder.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
       
		Backend.CustomerOrder.prototype.treeBrowser.showFeedback = 
			function(itemId) 
			{
				if (!this.iconUrls)
				{
					this.iconUrls = new Object();	
				}
				
				if (!this.iconUrls[itemId])
				{
                    this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
                    var img = this._globalIdStorageFind(itemId).htmlNode.down('img', 2);
                    img.originalSrc = img.src;
    				img.src = 'image/indicator.gif';                    
                }
			}
		
		Backend.CustomerOrder.prototype.treeBrowser.hideFeedback = 
			function(itemId)
			{
                if (null != this.iconUrls[itemId])
                {
        			this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
                    var img = this._globalIdStorageFind(itemId).htmlNode.down('img', 2);
                    img.src = img.originalSrc;
                    this.iconUrls[itemId] = null;                            
                }
			}
		
    	this.insertTreeBranch(groups, 0); 
        
        var orderID = window.location.hash.match(/order_(\d+)/);
        if (orderID && orderID[1])
        {
            Element.show($('loadingOrder'));
            Backend.CustomerOrder.prototype.openOrder(orderID[1], null, function() { 
			    Element.hide($('loadingOrder')); 
			});
        }       
        else
        {
			if(!Backend.ajaxNav.getHash().match(/group_\d+#\w+/)) 
			{
				window.location.hash = '#group_1#tabOrders__';
			}
		}
	    
		this.tabControl = TabControl.prototype.getInstance('orderGroupsManagerContainer', this.craftTabUrl, this.craftContainerId, {}); 

        Backend.CustomerOrder.prototype.instance = this;
	},
    
    createNewOrder: function(customerID)
    {
        var $this = this;
        new LiveCart.AjaxRequest(
            Backend.CustomerOrder.Links.createOrder + "?customerID=" + customerID,
            false,
            function(response)
            {
                response = eval("(" + response.responseText + ")");
                
                if('success' == response.status)
                {
                    window.focus();
					
					setTimeout(function()
					{
						Backend.SelectPopup.prototype.popup.close();
						
						Backend.CustomerOrder.prototype.treeBrowser.selectItem(3, true, true);
						Backend.CustomerOrder.prototype.openOrder(response.order.ID);
						if(window.ordersActiveGrid[3])
						{
						    window.ordersActiveGrid[3].reloadGrid();
						}
					}, 20);	
                }
                else
                {
                     new Backend.SelectPopup.prototype.popup.Backend.SaveConfirmationMessage(Backend.SelectPopup.prototype.popup.$("userHasNoAddressError")); // show error in popup
                }
            } 
        );
    },
    
    createUserOrder: function(customerID, feedbackElement, orderUrl)
    {
        var feedbackElement = feedbackElement.up('li').down('.progressIndicator');
        feedbackElement.show();
        
		new LiveCart.AjaxRequest(
            Backend.CustomerOrder.Links.createOrder + "?customerID=" + customerID,
            false,
            function(response)
            {
                response = response.responseData;
                
                if('success' == response.status)
                {
                     window.location.href = orderUrl + '#order_' + response.order.ID;
                }
                else
                {
                     feedbackElement.hide();
                }
            } 
        );		
	},
    
    craftTabUrl: function(url)
    {
        return url.replace(/_id_/, Backend.CustomerOrder.prototype.treeBrowser.getSelectedItemId());
    },

    craftContainerId: function(tabId)
    {
        return tabId + '_' +  Backend.CustomerOrder.prototype.treeBrowser.getSelectedItemId() + 'Content';
    },
	
	insertTreeBranch: function(treeBranch, rootId)
	{
        $A(treeBranch).each(function(node)
		{
            Backend.CustomerOrder.prototype.treeBrowser.insertNewItem(node.rootID, node.ID, node.name, null, 0, 0, 0, '', 1);
            this.treeBrowser.showItemSign(node.ID, 0);
            var group = document.getElementsByClassName("standartTreeRow", $("orderGroupsBrowser")).last().up('tr');
            group.id = 'group_' + node.ID;
            group.onclick = function()
            {
                Backend.CustomerOrder.prototype.treeBrowser.selectItem(node.ID, true);
            }
		}.bind(this));
	},

	activateGroup: function(id)
	{
        Backend.Breadcrumb.display(id);
		        
        if(/*Backend.CustomerOrder.prototype.activeGroup && */Backend.CustomerOrder.prototype.activeGroup != id)
        {           
            // Remove empty shippments
            var productsContainer = $("orderManagerContainer");
            if(productsContainer && productsContainer.style.display != 'none')
            {
                if(!Backend.CustomerOrder.Editor.prototype.getInstance(Backend.CustomerOrder.Editor.prototype.getCurrentId()).removeEmptyShipmentsConfirmation()) 
                {
                    Backend.CustomerOrder.prototype.treeBrowser.selectItem(Backend.CustomerOrder.prototype.activeGroup, false);
                    return;
                }
            }
			
			// Close popups
            if(window.selectPopupWindow) 
            {
                window.selectPopupWindow.close();
            }
            
            Backend.CustomerOrder.prototype.activeGroup = id;
    		Backend.CustomerOrder.prototype.treeBrowser.showFeedback(id);
            
            Backend.ajaxNav.add('group_' + id);
            
			Backend.CustomerOrder.prototype.instance.tabControl.activateTab('tabOrders', function() { 
                Backend.CustomerOrder.prototype.treeBrowser.hideFeedback(id);
            });
            
            Backend.showContainer("orderGroupsManagerContainer");
        }
        
        Backend.CustomerOrder.prototype.activeGroup = id;
        Backend.ajaxNav.add('group_' + Backend.CustomerOrder.prototype.activeGroup + "#tabOrders");
	},
	
	displayCategory: function(response)
	{
		Backend.CustomerOrder.prototype.treeBrowser.hideFeedback();
	},
	
	resetForm: function(e)
	{
		var el = Event.element(e);
		while (el.tagName != 'FORM')
		{
			el = el.parentNode;
		}
		
		el.reset();		
	},
    
	updateHeader: function ( activeGrid, offset ) 
	{
		var liveGrid = activeGrid.ricoGrid;
		
		var totalCount = liveGrid.metaData.getTotalRows();
		var from = offset + 1;
		var to = offset + liveGrid.metaData.getPageSize();
		
		if (to > totalCount)
		{
			to = totalCount;		
		}
		  
       
		var categoryID = activeGrid.tableInstance.id.split('_')[1];		
		var cont =  activeGrid.tableInstance.up('.sectionContainer').down('.orderCount');
		var countElement = document.getElementsByClassName('rangeCount', cont)[0];
		var notFound = document.getElementsByClassName('notFound', cont)[0];
								
		if (totalCount > 0)
		{
			if (!countElement.strTemplate)
			{
				countElement.strTemplate = countElement.innerHTML;	
			}		
			
			var str = countElement.strTemplate;
			str = str.replace(/\$from/, from);
			str = str.replace(/\$to/, to);
			str = str.replace(/\$count/, totalCount);
									
			countElement.innerHTML = str;
			notFound.style.display = 'none';
			countElement.style.display = '';					
		}
		else
		{
			notFound.style.display = '';
			countElement.style.display = 'none';					
		}
    },
    
    openOrder: function(id, e, onComplete) 
    {
        if(e) 
		{
			Event.stop(e);
            $('orderIndicator_' + id).style.visibility = 'visible';
		}		   
        
        Backend.CustomerOrder.Editor.prototype.setCurrentId(id); 
        
    	var tabControl = TabControl.prototype.getInstance(
            'orderManagerContainer',
            Backend.CustomerOrder.Editor.prototype.craftTabUrl, 
            Backend.CustomerOrder.Editor.prototype.craftContentId
        ); 
        
		modifiedOnComplete = tabControl.activateTab(null, 
                                                        function(response)
                                                        { 
                                                            if (onComplete)
                                                            {
                                                                onComplete(response);
                                                            }
															
                                                            Backend.CustomerOrder.prototype.orderLoaded = true; 
													

                                                            if (!window.location.hash.match(/order_(\d+)/)) 
                                                            { 
                                                                Backend.ajaxNav.add("order_" + id);
                                                            }
                                                        }.bind(this) );
        
        if(Backend.CustomerOrder.Editor.prototype.hasInstance(id)) 
    	{
    		Backend.CustomerOrder.Editor.prototype.getInstance(id);			
    	}
	
        Backend.showContainer("orderManagerContainer");
    },      
	
    updateLog: function(orderID)
    {
		var url = $("tabOrderLog").down('a').href.replace(/_id_/, orderID);
		var container = "tabOrderLog_" + orderID + "Content";
		var identificator = url + container;
		
		if($(container))
        {
			$(container).remove();
			TabControl.prototype.getInstance("orderManagerContainer").loadedContents[identificator] = false;
		}
    }
}

Backend.CustomerOrder.Links = {}; 
Backend.CustomerOrder.Messages = {}; 

Backend.CustomerOrder.GridFormatter = Class.create();
Backend.CustomerOrder.GridFormatter.prototype = 
{
    lastUserID: 0,
    
	initialize: function()
	{
		
	},

	getClassName: function(field, value)
	{
		
	},
	
	formatValue: function(field, value, id)
	{
		if ('CustomerOrder.ID2' == field && Backend.CustomerOrder.prototype.ordersMiscPermission)
		{
		    var displayedID = id;
		    
		    while (displayedID.length < 4)
		    {
                displayedID = '0' + displayedID;
            }
            
            value = 
            '<span>' + 
            '    <span class="progressIndicator" id="orderIndicator_' + id + '" style="visibility: hidden;"></span>' + 
            '</span>' + 
            '<a href="' + this.orderUrl + id + '#tabOrderInfo__" id="order_' + id + '" onclick="Backend.CustomerOrder.prototype.openOrder(' + id + ', event); return false;">' + 
                 displayedID + 
            '</a>'
		}
		else if ('CustomerOrder.ID2' == field && Backend.CustomerOrder.prototype.ordersMiscPermission)
		{
		    value = id;
        }
        else if('User.ID' == field)
        {
            Backend.CustomerOrder.GridFormatter.lastUserID = value;
        }
        
        if(value == '-')
        {
            value = "<center>" + value + "</center>";   
        }
		
		return value;
	}
}

if (!Backend.User)
{
	Backend.User = {};
}

Backend.User.OrderGridFormatter = new Backend.CustomerOrder.GridFormatter();
Backend.User.OrderGridFormatter.parentFormatValue = Backend.User.OrderGridFormatter.formatValue;
Backend.User.OrderGridFormatter.formatValue = 
	function(field, value, id)
	{
		if ('CustomerOrder.ID2' == field)
		{
		    var displayedID = id;
		    
		    while (displayedID.length < 4)
		    {
                displayedID = '0' + displayedID;
            }
            
            return '<a href="' + this.orderUrl + id + '#tabOrderInfo__">' + displayedID + '</a>'; 
		}
		else
		{
			return this.parentFormatValue(field, value, id);
		}
	}


Backend.CustomerOrder.Editor = Class.create();
Backend.CustomerOrder.Editor.prototype = 
{
    Links: {},
    Messages: {},
    Instances: {},
    CurrentId: null,
	
    STATUS_NEW: 0,
    STATUS_PROCESSING: 1,
    STATUS_AWAITING: 2,
    STATUS_SHIPPED: 3,
    STATUS_RETURNED: 4,    
    
    getCurrentId: function()
    {
        return Backend.CustomerOrder.Editor.prototype.CurrentId;
    },

    setCurrentId: function(id)
    {
        Backend.CustomerOrder.Editor.prototype.CurrentId = id;
    },

    craftTabUrl: function(url)
    {
        return url.replace(/_id_/, Backend.CustomerOrder.Editor.prototype.getCurrentId());
    },

    craftContentId: function(tabId)
    {
        // Remove empty shippments
        if(tabId != 'tabOrderProducts')
        {
            var productsContainer = $("tabOrderProducts_" + Backend.CustomerOrder.Editor.prototype.getCurrentId() + "Content");
            if(productsContainer && productsContainer.style.display != 'none')
            {
                if(!Backend.CustomerOrder.Editor.prototype.getInstance(Backend.CustomerOrder.Editor.prototype.getCurrentId()).removeEmptyShipmentsConfirmation())
                {
                    TabControl.prototype.getInstance("orderManagerContainer", false).activateTab($("tabOrderProducts"));
                    return false;
                }
            }
			
			// close popups
            if(window.selectPopupWindow) 
            {
                window.selectPopupWindow.close();
            }
        }
        
        return tabId + '_' +  Backend.CustomerOrder.Editor.prototype.getCurrentId() + 'Content'
    },

    getInstance: function(id, doInit, hideShipped, isCancelled, isFinalized)
    {
		if(!Backend.CustomerOrder.Editor.prototype.Instances[id])
        {
            Backend.CustomerOrder.Editor.prototype.Instances[id] = new Backend.CustomerOrder.Editor(id, hideShipped, isCancelled, isFinalized);
        }

        if (Backend.CustomerOrder.Editor.prototype.Instances[id].isCancelled)
        {
            $('orderManagerContainer').addClassName('cancelled');            
        }
        else
        {
            $('orderManagerContainer').removeClassName('cancelled');
        }        

        if (Backend.CustomerOrder.Editor.prototype.Instances[id].isFinalized)
        {
            $('orderManagerContainer').removeClassName('unfinalized');
        }
        else
        {
            $('orderManagerContainer').addClassName('unfinalized');
        }        

        if(doInit !== false) Backend.CustomerOrder.Editor.prototype.Instances[id].init();
        
        return Backend.CustomerOrder.Editor.prototype.Instances[id];
    },
    
    hasInstance: function(id)
    {
        return this.Instances[id] ? true : false;
    },
    
    initialize: function(id, hideShipped, isCancelled, isFinalized)
  	{
        this.id = id ? id : '';
		this.hideShipped = hideShipped;
		this.isCancelled = isCancelled;
		this.isFinalized = isFinalized;
		
        this.findUsedNodes();
        this.bindEvents();
        
		this.toggleStatuses();
		
        Form.State.backup(this.nodes.form);
	},

    toggleStatuses: function()
	{
        var statusValue = parseInt(this.nodes.status.value);
		
		var migrations = {}
		migrations[this.STATUS_NEW]         = [this.STATUS_NEW,this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_SHIPPED]
        migrations[this.STATUS_PROCESSING]  = [this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_SHIPPED]
        migrations[this.STATUS_AWAITING]    = [this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_SHIPPED]
        migrations[this.STATUS_SHIPPED]     = [this.STATUS_SHIPPED,this.STATUS_RETURNED]
        migrations[this.STATUS_RETURNED]    = [this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_RETURNED,this.STATUS_SHIPPED]
        
		$A(this.nodes.status.options).each(function(option) {
			if(migrations[statusValue].include(parseInt(option.value)))
			{
				Element.show(option);
				
		        $$("#tabOrderProducts_" + this.id + "Content .shippableShipments select[name=status]").each(function(select)
		        {
		            $A(select.options).each(function(shipmentOption)
		            {
                        if(shipmentOption.value == option.value && shipmentOption.style.display == 'none')
		                {
                            Element.hide(option);
		                    throw $break;
		                }
		            }.bind(this));
		        }.bind(this));
			}
			else
			{
                Element.hide(option);
			}
			
			
		}.bind(this));
		
		// If one shipment
		if($("orderShipments_list_" + this.id))
		{
	        if($("orderShipments_list_" + this.id).childElements().size() == 1)
	        {
				if(!$$("#orderShipments_list_" + this.id + " .orderShipment_USPS_select")[0].value)
				{
					this.hideShipmentStatus();
				}
	        }
		}
		else if(this.hideShipped)
		{
            Element.hide(this.nodes.status.options[this.STATUS_SHIPPED]);
		}
	},
	
	hideShipmentStatus: function()
	{
        Element.hide(this.nodes.status.options[this.STATUS_SHIPPED]);
    },
	
	findUsedNodes: function()
    {
        this.nodes = {};
        this.nodes.parent = $("tabOrderInfo_" + this.id + "Content");
        this.nodes.form = this.nodes.parent.down("form");
		this.nodes.isCanceled = $("order_" + this.id + "_isCanceled");
		this.nodes.isCanceledIndicator = $("order_" + this.id + "_isCanceledIndicator");
		this.nodes.acceptanceStatusValue = $("order_acceptanceStatusValue_" + this.id);
		this.nodes.status = this.nodes.form.down('select.status');
		this.nodes.orderStatus = this.nodes.parent.down('.order_status');
    },

    bindEvents: function(args)
    {
		Event.observe(this.nodes.isCanceled, 'click', function(e) { Event.stop(e); this.switchCancelled(); }.bind(this));
		Event.observe(this.nodes.status, 'change', function(e) { Event.stop(e); this.submitForm(); }.bind(this));
    },

    switchCancelled: function()
    {
		var message = this.nodes.form.elements.namedItem('isCancelled').value == 'true' ? Backend.CustomerOrder.Editor.prototype.Messages.areYouSureYouWantToActivateThisOrder : Backend.CustomerOrder.Editor.prototype.Messages.areYouSureYouWantToCancelThisOrder;
		if(!confirm(message)) return;
		
        new LiveCart.AjaxRequest(
            Backend.CustomerOrder.Editor.prototype.Links.switchCancelled + '/' + this.id, 
            this.nodes.isCanceledIndicator,
            function(response) {
	            response = response.responseData;
	                            
	            if(response.status == 'success')
	            {	                
                    if (0 != response.isCanceled)
	                {
                        $('orderManagerContainer').addClassName('cancelled');
                    }
                    else
                    {
                        $('orderManagerContainer').removeClassName('cancelled');
                    }
                
                    Backend.CustomerOrder.Editor.prototype.Instances[this.id].isCancelled = response.isCanceled;
                    
                    this.nodes.isCanceled.up('li').className = response.isCanceled ? 'order_accept' : 'order_cancel';
                    this.nodes.isCanceled.update(response.linkValue);
                    this.nodes.acceptanceStatusValue.update(response.value);
                    this.nodes.acceptanceStatusValue.style.color = response.isCanceled ? 'red' : 'green';
					this.nodes.form.elements.namedItem('isCancelled').value = response.isCanceled;
                    Backend.CustomerOrder.prototype.updateLog(this.id);
	            }
	        }.bind(this)
        );
    },
    
    updateStatus: function()
    {
        this.form.submit();
    },

    init: function(args)
    {	
	   Backend.Breadcrumb.setTree(Backend.CustomerOrder.prototype.treeBrowser);
	
		Backend.CustomerOrder.Editor.prototype.setCurrentId(this.id);
        var orderIndicator = $('orderIndicator_' + this.id);
        if(orderIndicator) 
        {
            orderIndicator.style.visibility = 'hidden';
        }
		

        Backend.showContainer("orderManagerContainer");
        this.tabControl = TabControl.prototype.getInstance("orderManagerContainer", false);
		
        this.toggleStatuses();
		this.setPath();
    },
	
	setPath: function() {
		if(Backend.UserGroup.prototype.treeBrowser)
		{
            var currentUser = Backend.User.Editor.prototype.getInstance(Backend.User.Editor.prototype.CurrentId, false)

	        Backend.Breadcrumb.display(
	            Backend.UserGroup.prototype.activeGroup, 
				[
				    [currentUser.nodes.form.elements.namedItem("email").value, function(e) 
					{ 
					   Event.stop(e); 
					   Backend.hideContainer(); 
					   setTimeout(function()
					   { 
                           Backend.Breadcrumb.display(
							   Backend.UserGroup.prototype.activeGroup,
							   this.nodes.form.elements.namedItem('email').value
						   );
					   }.bind(this), 20);
					   
				    }.bind(currentUser)],
					Backend.CustomerOrder.Editor.prototype.Messages.orderNum + this.id
				]
	            
	        );
        }
		else
		{	
            Backend.Breadcrumb.display(
                Backend.Breadcrumb.treeBrowser.getSelectedItemId(), 
                Backend.CustomerOrder.Editor.prototype.Messages.orderNum + this.id
            );	
		}
    },
    
    cancelForm: function()
    {      
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        
        if(!Backend.CustomerOrder.Editor.prototype.getInstance(Backend.CustomerOrder.Editor.prototype.getCurrentId(), false).removeEmptyShipmentsConfirmation()) 
        {
            Backend.CustomerOrder.prototype.treeBrowser.selectItem(Backend.CustomerOrder.prototype.activeGroup, true);
            return;
        }
                
        Backend.hideContainer();
		Form.restore(this.nodes.form);
		
		if(Backend.UserGroup.prototype.treeBrowser)
		{
            var currentUser = Backend.User.Editor.prototype.getInstance(Backend.User.Editor.prototype.CurrentId, true)
		    currentUser.setPath();
		}
		else
		{
            Backend.Breadcrumb.display(Backend.CustomerOrder.prototype.activeGroup);

            Backend.ajaxNav.add('group_' + Backend.CustomerOrder.prototype.activeGroup + "#tabOrders");
		}
		
		// hide popups
        if(window.selectPopupWindow) 
        {
            window.selectPopupWindow.close();
        }
        
        if (!Backend.CustomerOrder.prototype.activeGroup)
        {
			Backend.CustomerOrder.prototype.activeGroup = -1;
			Backend.CustomerOrder.prototype.treeBrowser.selectItem(1, true);
            Backend.CustomerOrder.prototype.instance.activateGroup(1);
        }                 
    },
    
    submitForm: function()
    {
		if(!confirm(Backend.CustomerOrder.Messages.areYouSureYouWantToUpdateOrderStatus)) 
		{
			Form.restore(this.nodes.form);
		    return;	
		}
		
		new LiveCart.AjaxRequest(
            this.nodes.form,
            $("order_" + this.id + "_status_feedback"),
            function(responseJSON) 
            {
				try
				{
				    var responseObject = eval("(" + responseJSON.responseText + ")");
				}
				catch(e)
				{
					var responseObject = {status: 'failure', response : {}};
				}
				
				this.afterSubmitForm(responseObject);
		    }.bind(this)
        );
    },
	
	afterSubmitForm: function(response)
	{	
	    ActiveForm.prototype.resetErrorMessages(this.nodes.form);
	
		if(response.status == 'success')
		{
            Backend.CustomerOrder.prototype.updateLog(this.id);
			
			var shippableShipments = $$("#tabOrderProducts_" + this.id + "Content .shippableShipments .orderShipment");
			var shippedShipments = $$("#tabOrderProducts_" + this.id + "Content .shippedShipments .orderShipment");
			var updateStatuses = function(li)
			{
				var statusValue = this.nodes.status.value;
                var instnace = Backend.Shipment.prototype.getInstance(li);
                instnace.nodes.status.value = statusValue;
                
                instnace.afterChangeStatus({status: "success"});
                this.nodes.status.value = statusValue;
			}.bind(this)
			
			if(this.nodes.status.value != Backend.CustomerOrder.Editor.prototype.STATUS_RETURNED)
		    {
			    shippableShipments.each(function(li){ updateStatuses(li); }.bind(this));
		    }
			else if(shippableShipments.size() == 0)
			{
                shippedShipments.each(function(li) { updateStatuses(li); }.bind(this));
			}
			Form.State.backup(this.nodes.form);
		}
		else
		{
            Form.State.restore(this.nodes.form);
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
            
        this.toggleStatuses();
	},
    
    removeEmptyShipmentsFromHTML: function()
    {
        new LiveCart.AjaxRequest(Backend.Shipment.Links.removeEmptyShipments + "/" + this.id);
        
        $$("#tabOrderProducts_" + this.id + "Content .shippableShipments .orderShipment").each(function(shipemnt)
        {
             if(!shipemnt.down('li'))
             {
                 Element.remove(shipemnt);
             }
        });
		
        var shipments = $$("#tabOrderProducts_" + this.id + "Content .shippableShipments .orderShipment");
        if(shipments.size() == 1)
        {
            var firstItemsList = ActiveList.prototype.getInstance(shipments.first().down('ul'));
            Element.removeClassName(firstItemsList.ul, 'activeList_add_sort');
            firstItemsList.destroySortable();
        } 
		
        Backend.CustomerOrder.Editor.prototype.getInstance(this.id, false).toggleStatuses();
        Backend.Shipment.prototype.updateOrderStatus(this.id);
    },
    
    
    hasEmptyShipments: function()
    {
		//
		if($("order" + this.id + "_shippableShipments").style.display == 'none')
		{
		  return false;
	    }
		
        var hasEmptyShipments = false;
        $$("#tabOrderProducts_" + this.id + "Content .shippableShipments .orderShipment").each(function(itemList)
        {
             if(!itemList.down('li'))
             {
                 hasEmptyShipments = true;
                 return $break;
             }
        })
        
        return hasEmptyShipments;
    },
    
    removeEmptyShipmentsConfirmation: function()
    {
        if(!Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime) Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = 0;
    
        var container = $$("#tabOrderProducts_" + this.id + "Content .orderShipments").first();
           
        if($("orderManagerContainer").style.display == 'none' || !container || (container.style.display == 'none'))
        {
            Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = (new Date()).getTime();
            return true;   
        }
        else
        {
            if(!this.hasEmptyShipments()) 
            {
                Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = (new Date()).getTime();
                this.removeEmptyShipmentsFromHTML();
                return true;
            }  
            
            // Confirm can be called few times in a row. To prevent multiple dialogs
            // rember user last decision
            if((new Date()).getTime() - Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime < 100)
            {
                return Backend.Shipment.removeEmptyShipmentsConfirmationLastUserDecision;
            }
            
            if(confirm(Backend.Shipment.Messages.emptyShipmentsWillBeRemoved))
            {
                Backend.Shipment.removeEmptyShipmentsConfirmationLastUserDecision = true;
                Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = (new Date()).getTime();
                
                this.removeEmptyShipmentsFromHTML();
                
                return true;   
            }   
            else
            {
                Backend.Shipment.removeEmptyShipmentsConfirmationLastUserDecision = false;
                Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = (new Date()).getTime();
                return false;
            }
        }
    },
    
    resetEditors: function()
    {
        Backend.CustomerOrder.Editor.prototype.Instances = {};
        Backend.CustomerOrder.Editor.prototype.CurrentId = null;

        $('orderManagerContainer').down('.sectionContainer').innerHTML = '';

        TabControl.prototype.__instances__ = {};
    }    
}


Backend.CustomerOrder.Address = Class.create();
Backend.CustomerOrder.Address.prototype = 
{
    Links: {},
    Messages: {},
    Instances: {},
    
    getCurrentId: function()
    {
        return Backend.CustomerOrder.Address.prototype.CurrentId;
    },

    setCurrentId: function(id)
    {
        Backend.CustomerOrder.Address.prototype.CurrentId = id;
    },

    getInstance: function(root, doInit)
    {
		if(!Backend.CustomerOrder.Address.prototype.Instances[root.id])
        {
            Backend.CustomerOrder.Address.prototype.Instances[root.id] = new Backend.CustomerOrder.Address(root);
        }

        if(doInit !== false) Backend.CustomerOrder.Address.prototype.Instances[root.id].init();
        
        return Backend.CustomerOrder.Address.prototype.Instances[root.id];
    },

    hasInstance: function(root)
    {
        return this.Instances[root.id] ? true : false;
    },
    
    initialize: function(root, type)
  	{
        this.findUsedNodes(root);
        this.bindEvents();
        this.type = type;

        this.stateSwitcher = this.nodes.form.elements.namedItem('stateID').stateSwitcher;
  
        Form.State.backup(this.nodes.form);
        this.stateID = this.nodes.form.elements.namedItem('stateID').value;
	},
    
	findUsedNodes: function(root)
    {
        this.nodes = {};
        this.nodes.parent = root;
        this.nodes.form = root;
		this.nodes.cancel = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');
        
		this.nodes.cancelEdit = this.nodes.form.down('.order_cancelEditAddress');
        this.nodes.showEdit = this.nodes.form.down('.order_editAddress');
		this.nodes.view = this.nodes.form.down('.orderAddress_view');
		this.nodes.edit = this.nodes.form.down('.orderAddress_edit');
    },

    bindEvents: function(args)
    {
		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancelForm()}.bind(this));
        Event.observe(this.nodes.form.elements.namedItem('existingUserAddress'), 'change', function(e) { this.useExistingAddress()}.bind(this));
        Element.observe(this.nodes.showEdit, 'click', function(e) { Event.stop(e); this.showForm(); }.bind(this));
        Element.observe(this.nodes.cancelEdit, 'click', function(e) { Event.stop(e); this.hideForm(); }.bind(this)); 
    },
    
    showForm: function()
    {
        this.nodes.showEdit.hide(); 
        this.nodes.cancelEdit.show();
        this.nodes.view.hide();
        this.nodes.edit.show();
    },
    
    hideForm: function()
    {
        this.nodes.showEdit.show();
        this.nodes.cancelEdit.hide();
        this.nodes.view.show();
        this.nodes.edit.hide();
    },
    
    useExistingAddress: function()
    {
        if(this.nodes.form.elements.namedItem('existingUserAddress').value)
        {
            var address = Backend.CustomerOrder.Editor.prototype.existingUserAddresses[this.nodes.form.elements.namedItem('existingUserAddress').value];
    
            this.nodes.form.elements.namedItem('firstName').value = address.UserAddress.address1;
            this.nodes.form.elements.namedItem('lastName').value = address.UserAddress.firstName;
            this.nodes.form.elements.namedItem('countryID').value = address.UserAddress.countryID;
            
            if(address.UserAddress.stateID)
            {
                this.nodes.form.elements.namedItem('stateID').value = address.UserAddress.stateID;
            }
            else
            {
                this.nodes.form.elements.namedItem('stateName').value = address.UserAddress.stateName;
            }
            
            this.nodes.form.elements.namedItem('city').value = address.UserAddress.city;
            this.nodes.form.elements.namedItem('address1').value = address.UserAddress.address1;
            this.nodes.form.elements.namedItem('address2').value = address.UserAddress.address2;
            this.nodes.form.elements.namedItem('postalCode').value = address.UserAddress.postalCode;
            this.nodes.form.elements.namedItem('phone').value = address.UserAddress.phone;
            
            this.stateSwitcher.updateStates(null, function(){ this.nodes.form.elements.namedItem('stateID').value = address.UserAddress.stateID; }.bind(this));
        }
    },

    init: function(args)
    {	
		Backend.CustomerOrder.Address.prototype.setCurrentId(this.id);
        var orderIndicator = $('orderIndicator_' + this.id);
        if(orderIndicator) 
        {
            orderIndicator.style.visibility = 'hidden';
        }
        
        this.tabControl = TabControl.prototype.getInstance("orderManagerContainer", false);
    },
    
    cancelForm: function()
    {
        var $this = this;
        
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.State.restore(this.nodes.form, ['existingUserAddress']);
        this.stateSwitcher.updateStates(null, function(){ $this.nodes.form.elements.namedItem('stateID').value = $this.stateID; });
        this.hideForm();
    },

    submitForm: function()
    {
		new LiveCart.AjaxRequest(
            this.nodes.form,
    		false,
            function(responseJSON) 
            {
                ActiveForm.prototype.resetErrorMessages(this.nodes.form);
                var responseObject = eval("(" + responseJSON.responseText + ")");
                this.afterSubmitForm(responseObject);
            }.bind(this)
		);
    },

	afterSubmitForm: function(response)
	{
		if(response.status == 'success')
		{
            this.stateID = this.nodes.form.elements.namedItem('stateID').value;
			Form.State.backup(this.nodes.form);
			
			this.nodes.form.down('.addressFullName').innerHTML = this.nodes.form.elements.namedItem('firstName').value + " " +this.nodes.form.elements.namedItem('lastName').value
            this.nodes.form.down('.addressCountryName').innerHTML = this.nodes.form.elements.namedItem('countryID').options[this.nodes.form.elements.namedItem('countryID').selectedIndex].text
            
			if(this.nodes.form.elements.namedItem('stateID').options.length == 0)
			{
			    this.nodes.form.down('.addressStateName').innerHTML = this.nodes.form.elements.namedItem('stateName').value
			}
			else
			{
				this.nodes.form.down('.addressStateName').innerHTML = this.nodes.form.elements.namedItem('stateID').options[this.nodes.form.elements.namedItem('stateID').selectedIndex].text
			}
			
            this.nodes.form.down('.addressCompanyName').innerHTML = this.nodes.form.elements.namedItem('companyName').value
            this.nodes.form.down('.addressCity').innerHTML = this.nodes.form.elements.namedItem('city').value
            this.nodes.form.down('.addressAddress1').innerHTML = this.nodes.form.elements.namedItem('address1').value
            this.nodes.form.down('.addressAddress2').innerHTML = this.nodes.form.elements.namedItem('address2').value
            this.nodes.form.down('.addressPostalCode').innerHTML = this.nodes.form.elements.namedItem('postalCode').value
            this.nodes.form.down('.addressPhone').innerHTML = this.nodes.form.elements.namedItem('address2').value
			
            this.hideForm();

            Backend.CustomerOrder.prototype.updateLog(this.nodes.form.elements.namedItem('orderID').value);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	}
}
