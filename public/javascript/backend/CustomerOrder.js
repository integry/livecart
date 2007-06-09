Backend.CustomerOrder = Class.create();
Backend.CustomerOrder.prototype = 
{
  	Links: {},
    Messages: {},
    
    treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(groups)
	{
        var self = this;      
        
		Backend.CustomerOrder.prototype.treeBrowser = new dhtmlXTreeObject("orderGroupsBrowser","","", false);
		Backend.CustomerOrder.prototype.treeBrowser.setOnClickHandler(this.activateGroup);
		
		Backend.CustomerOrder.prototype.treeBrowser.def_img_x = 'auto';
		Backend.CustomerOrder.prototype.treeBrowser.def_img_y = 'auto';
				
		Backend.CustomerOrder.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		Backend.CustomerOrder.prototype.treeBrowser.setOnClickHandler(this.activateGroup.bind(this));
       
		Backend.CustomerOrder.prototype.treeBrowser.showFeedback = 
			function(itemId) 
			{
				if (!this.iconUrls)
				{
					this.iconUrls = new Object();	
				}
				
				this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
				this.setItemImage(itemId, '../../../image/indicator.gif');
			}
		
		Backend.CustomerOrder.prototype.treeBrowser.hideFeedback = 
			function()
			{
				for (var itemId in this.iconUrls)
				{
					this.setItemImage(itemId, this.iconUrls[itemId]);	
				}				
			}
		
    	this.insertTreeBranch(groups, 0); 
        
        if(!Backend.ajaxNav.getHash().match(/group_\d+#\w+/)) window.location.hash = '#group_1#tabOrders__';
	    self.tabControl = TabControl.prototype.getInstance('orderGroupsManagerContainer', self.craftTabUrl, self.craftContainerId, {}); 
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
		var self = this;
        
            
        $A(treeBranch).each(function(node)
		{
            Backend.CustomerOrder.prototype.treeBrowser.insertNewItem(node.rootID, node.ID, node.name, null, 0, 0, 0, '', 1);
            self.treeBrowser.showItemSign(node.ID, 0);
            var group = document.getElementsByClassName("standartTreeRow", $("orderGroupsBrowser")).last();
            group.id = 'group_' + node.ID;
            group.onclick = function()
            {
                Backend.CustomerOrder.prototype.treeBrowser.selectItem(node.ID, true);
            }
		});
	},

	activateGroup: function(id)
	{
       
        if(Backend.CustomerOrder.prototype.activeGroup && Backend.CustomerOrder.prototype.activeGroup != id)
        {
            Backend.CustomerOrder.prototype.activeGroup = id;
    		Backend.CustomerOrder.prototype.treeBrowser.showFeedback(id);
            
            Backend.ajaxNav.add('group_' + id);
            
            this.tabControl.activateTab('tabOrders', function() { 
                Backend.CustomerOrder.prototype.treeBrowser.hideFeedback(id);
            });
            
            Backend.showContainer("orderGroupsManagerContainer");
        }
        
        Backend.CustomerOrder.prototype.activeGroup = id;
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
	
	save: function(form)
	{
		var indicator = document.getElementsByClassName('progressIndicator', form)[0];
		new LiveCart.AjaxRequest(form, indicator, this.displaySaveConfirmation.bind(this));	
	},
	
	displaySaveConfirmation: function()
	{
		new Backend.SaveConfirmationMessage(document.getElementsByClassName('yellowMessage')[0]);			
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
			str = str.replace(/%from/, from);
			str = str.replace(/%to/, to);
			str = str.replace(/%count/, totalCount);
									
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
    
    openOrder: function(id, e) 
    {
        Event.stop(e);
        
        Backend.CustomerOrder.Editor.prototype.setCurrentId(id); 
        $('orderIndicator_' + id).style.display = '';
        
    	var tabControl = TabControl.prototype.getInstance(
            'orderManagerContainer',
            Backend.CustomerOrder.Editor.prototype.craftTabUrl, 
            Backend.CustomerOrder.Editor.prototype.craftContentId
        ); 
        
        tabControl.activateTab();
        
        if(Backend.CustomerOrder.Editor.prototype.hasInstance(id)) 
    	{
    		Backend.CustomerOrder.Editor.prototype.getInstance(id);			
    	}	
    }
}




Backend.CustomerOrder.GridFormatter = 
{
    lastUserID: 0,
    
	getClassName: function(field, value)
	{
		
	},
	
	formatValue: function(field, value, id)
	{
		if ('User.email' == field && Backend.CustomerOrder.prototype.usersMiscPermission)
		{
		    value = 
            '<span>' + 
            '    <span class="progressIndicator" id="userIndicator_' + Backend.CustomerOrder.GridFormatter.lastUserID + '" style="display: none;"></span>' + 
            '</span>' + 
            '<a href="#edit" id="user_' + Backend.CustomerOrder.GridFormatter.lastUserID + '" onclick="Backend.UserGroup.prototype.openUser(' + Backend.CustomerOrder.GridFormatter.lastUserID + ', event); } catch(e) { console.info(e) }  return false;">' + 
                 value + 
            '</a>';	
		}
		else if ('CustomerOrder.viewOrder' == field && Backend.CustomerOrder.prototype.ordersMiscPermission)
		{
		    value = 
            '<span>' + 
            '    <span class="progressIndicator" id="orderIndicator_' + id + '" style="display: none;"></span>' + 
            '</span>' + 
            '<a href="#edit" id="order_' + id + '" onclick="try { Backend.CustomerOrder.prototype.openOrder(' + id + ', event); } catch(e) { console.info(e) }  return false;">' + 
                 'view order' + 
            '</a>'
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



Backend.CustomerOrder.massActionHandler = Class.create();
Backend.CustomerOrder.massActionHandler.prototype = 
{
    handlerMenu: null,    
    actionSelector: null,
    valueEntryContainer: null,
    form: null,
        
    grid: null,
    
    initialize: function(handlerMenu, activeGrid)
    {
        this.handlerMenu = handlerMenu;     
        this.actionSelector = handlerMenu.getElementsByTagName('select')[0];
        this.valueEntryContainer = handlerMenu.down('.bulkValues');
        this.form = this.actionSelector.form;

        this.actionSelector.onchange = this.actionSelectorChange.bind(this);
        Event.observe(this.actionSelector.form, 'submit', this.submit.bind(this));
            
        this.grid = activeGrid;
    },
    
    actionSelectorChange: function()
    {
		for (k = 0; k < this.valueEntryContainer.childNodes.length; k++)
        {
            if (this.valueEntryContainer.childNodes[k].style)
            {
                Element.hide(this.valueEntryContainer.childNodes[k]);
            }
        }
        
        Element.show(this.valueEntryContainer);
        
        if (this.actionSelector.form.elements.namedItem(this.actionSelector.value))
        {
            Element.show(this.form.elements.namedItem(this.actionSelector.value));
            this.form.elements.namedItem(this.actionSelector.value).focus();
        }    
        else if (this.handlerMenu.down('.' + this.actionSelector.value))
        {
			var el = document.getElementsByClassName(this.actionSelector.value, this.handlerMenu)[0];
			Element.show(el);
		}
    },
    
    submit: function()
    {
        if ('delete' == this.actionSelector.value)
        {
			if (!confirm(this.deleteConfirmMessage))
			{
				return false;
			}
		}
		
		this.form.elements.namedItem('filters').value = Object.toJSON(this.grid.getFilters());
        var selectedIDs = Object.toJSON(this.grid.getSelectedIDs());
        this.form.elements.namedItem('selectedIDs').value = selectedIDs ? selectedIDs : '';
        this.form.elements.namedItem('isInverse').value = this.grid.isInverseSelection() ? 1 : 0;
        console.info(this.form.elements.namedItem('filters'))
        new LiveCart.AjaxRequest(this.form, document.getElementsByClassName('progressIndicator', this.handlerMenu)[0], this.submitCompleted.bind(this));

        this.grid.resetSelection();   
    },
    
    submitCompleted: function()
    {
        this.grid.reloadGrid();   
    }
}


Backend.CustomerOrder.Editor = Class.create();
Backend.CustomerOrder.Editor.prototype = 
{
    Links: {},
    Messages: {},
    Instances: {},
    CurrentId: null,
    
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
        return tabId + '_' +  Backend.CustomerOrder.Editor.prototype.getCurrentId() + 'Content'
    },

    getInstance: function(id, doInit)
    {
		if(!Backend.CustomerOrder.Editor.prototype.Instances[id])
        {
            Backend.CustomerOrder.Editor.prototype.Instances[id] = new Backend.CustomerOrder.Editor(id);
        }

        if(doInit !== false) Backend.CustomerOrder.Editor.prototype.Instances[id].init();
        
        return Backend.CustomerOrder.Editor.prototype.Instances[id];
    },
    
    
    
    hasInstance: function(id)
    {
        return this.Instances[id] ? true : false;
    },
    
    initialize: function(id)
  	{
        try
        {
            this.id = id ? id : '';
    
            this.findUsedNodes();
            this.bindEvents();
            
            Form.State.backup(this.nodes.form);
            
            var self = this;
        }
        catch(e)
        {
            console.info(e);
        }

	},

	findUsedNodes: function()
    {
        this.nodes = {};
        this.nodes.parent = $("tabOrderInfo_" + this.id + "Content");
        this.nodes.form = this.nodes.parent.down("form");
		this.nodes.isCanceled = this.nodes.form.down('a.isCanceled');
		this.nodes.status = this.nodes.form.down('select.status');
    },

    bindEvents: function(args)
    {
		var self = this;
		Event.observe(this.nodes.isCanceled, 'click', function(e) { Event.stop(e); self.switchCancelled(); });
		Event.observe(this.nodes.status, 'change', function(e) { Event.stop(e); self.submitForm(); });
    },

    switchCancelled: function()
    {
        var self = this;
        
        new Ajax.Request(Backend.CustomerOrder.Editor.prototype.Links.switchCancelled + '/' + this.id, {
           onSuccess: function(response) {
               response = eval("(" + response.responseText + ")");
               
               if(response.status == 'success')
               {
                   self.nodes.isCanceled.update(response.value);
               }
           }    
        });
    },
    
    updateStatus: function()
    {
        this.form.submit();
    },

    init: function(args)
    {	
		Backend.CustomerOrder.Editor.prototype.setCurrentId(this.id);
        var orderIndicator = $('orderIndicator_' + this.id);
        if(orderIndicator) 
        {
            orderIndicator.style.display = 'none';
        }
        Backend.showContainer("orderManagerContainer");

        this.tabControl = TabControl.prototype.getInstance("orderManagerContainer", false);
    },
    
    cancelForm: function()
    {      
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form);
    },
    
    submitForm: function()
    {
		var self = this;
		new Ajax.Request(Backend.CustomerOrder.Editor.prototype.Links.update + "/" + this.id,
		{
           method: 'post',
           parameters: Form.serialize(self.nodes.form),
           onSuccess: function(responseJSON) {
				ActiveForm.prototype.resetErrorMessages(self.nodes.form);
				var responseObject = eval("(" + responseJSON.responseText + ")");
				self.afterSubmitForm(responseObject);
		   }
		});
    },
	
	afterSubmitForm: function(response)
	{
		if(response.status == 'success')
		{
			Form.State.backup(this.nodes.form);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
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
    
    initialize: function(root)
  	{
        try
        {
            this.findUsedNodes(root);
            this.bindEvents();
            this.showOtherState();
            new Backend.User.StateSwitcher(
                this.nodes.form.elements.namedItem('countryID'), 
                this.nodes.form.elements.namedItem('stateID'), 
                this.nodes.form.elements.namedItem('stateName'),
                Backend.CustomerOrder.Editor.prototype.Links.states
            );
      
            Form.State.backup(this.nodes.form);
        }
        catch(e)
        {
            console.info(e);
        }

	},
    
    showOtherState: function()
    {

                            
     return;
        if(!this.nodes.form.elements.namedItem('stateID').value)
        {
            new Ajax.Request(
                Backend.CustomerOrder.Editor.prototype.Links.states + 'country=' + this.nodes.form.elements.namedItem('countryID').value,
                {
                    onSuccess: function(response)
                    {
                        response = eval("(" + response + ")");
                        console.info(response)
                    }    
                }
            );
            
            this.nodes.form.elements.namedItem('stateName').up('fieldset').show();
        }   
        else
        {
            this.nodes.form.elements.namedItem('stateName').up('fieldset').hide();
        } 
        
    },

	findUsedNodes: function(root)
    {
        this.nodes = {};
        this.nodes.parent = root;
        this.nodes.form = root;
		this.nodes.cancel = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');
    },

    bindEvents: function(args)
    {
		var self = this;
		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancelForm()});
        Event.observe(this.nodes.form.elements.namedItem('stateID'), 'change', function(e) { self.showOtherState()});
    },

    init: function(args)
    {	
		Backend.CustomerOrder.Address.prototype.setCurrentId(this.id);
        var orderIndicator = $('orderIndicator_' + this.id);
        if(orderIndicator) 
        {
            orderIndicator.style.display = 'none';
        }
        this.tabControl = TabControl.prototype.getInstance("orderManagerContainer", false);
    },
    
    cancelForm: function()
    {      
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form);
    },
    
    submitForm: function()
    {
		var self = this;
		new Ajax.Request(this.nodes.form.action + "/" + this.nodes.form.elements.namedItem('ID').value,
		{
           method: 'post',
           parameters: Form.serialize(self.nodes.form),
           onSuccess: function(responseJSON) {
				ActiveForm.prototype.resetErrorMessages(self.nodes.form);
				var responseObject = eval("(" + responseJSON.responseText + ")");
				self.afterSubmitForm(responseObject);
		   }
		});
    },
	
	afterSubmitForm: function(response)
	{
		if(response.status == 'success')
		{
			new Backend.SaveConfirmationMessage($('orderConfirmation'));
			Form.State.backup(this.nodes.form);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	}
}
