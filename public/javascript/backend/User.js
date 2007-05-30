Backend.User = {};

Backend.UserGroup = Class.create();
Backend.UserGroup.prototype = 
{
  	Links: {},
    Messages: {},
    
    treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(groups)
	{
        var self = this;      
        
		Backend.UserGroup.prototype.treeBrowser = new dhtmlXTreeObject("userGroupsBrowser","","", false);
		Backend.UserGroup.prototype.treeBrowser.setOnClickHandler(this.activateGroup);
		
		Backend.UserGroup.prototype.treeBrowser.def_img_x = 'auto';
		Backend.UserGroup.prototype.treeBrowser.def_img_y = 'auto';
				
		Backend.UserGroup.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		Backend.UserGroup.prototype.treeBrowser.setOnClickHandler(this.activateGroup.bind(this));
       
		Backend.UserGroup.prototype.treeBrowser.showFeedback = 
			function(itemId) 
			{
				if (!this.iconUrls)
				{
					this.iconUrls = new Object();	
				}
				
				this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
				this.setItemImage(itemId, '../../../image/indicator.gif');
			}
		
		Backend.UserGroup.prototype.treeBrowser.hideFeedback = 
			function()
			{
				for (var itemId in this.iconUrls)
				{
					this.setItemImage(itemId, this.iconUrls[itemId]);	
				}				
			}
		
    	this.insertTreeBranch(groups, 0); 
        
        if(!Backend.ajaxNav.getHash().match(/group_-?\d+#\w+/)) window.location.hash = '#group_-2#tabUsers__';
	    self.tabControl = TabControl.prototype.getInstance('userGroupsManagerContainer', self.craftTabUrl, self.craftContainerId, {}); 
        
        this.bindEvents();
	},

    bindEvents: function()
    {        
        var self = this;
    
        Event.observe($("userGroups_add"), "click", function(e) {
            Event.stop(e);
            self.createNewGroup(); 
        });
        
        Event.observe($("userGroups_delete"), "click", function(e) {
            Event.stop(e);
            self.deleteGroup();
        });
    },
    
    deleteGroup: function()
    {
        var $this = this;
        
        if(Backend.UserGroup.prototype.activeGroup < 0)
        {
             return alert(Backend.UserGroup.prototype.Messages.youCanntoDeleteThisGroup)
        }
        
        if (confirm(Backend.UserGroup.prototype.Messages.confirmUserGroupRemove)) 
        {
		    new Ajax.Request(
    			Backend.User.Group.prototype.Links.removeNewGroup + '/' + Backend.UserGroup.prototype.activeGroup,
    			{
				    onComplete: function(response) { 
                        response = eval("(" + response.responseText + ")");
                        if('success' == response.status)
                        {
                            Backend.UserGroup.prototype.treeBrowser.deleteItem(response.userGroup.ID, true);
                            var firstId = false;
                            if(firstId = parseInt(Backend.UserGroup.prototype.treeBrowser._globalIdStorage[1]))
                            {
                                Backend.UserGroup.prototype.treeBrowser.selectItem(firstId, true);
                            }
                        }
                    }
			    }
            );
        }
    },

	createNewGroup: function()
	{
        var self = this;
        
		new Ajax.Request(
            Backend.User.Group.prototype.Links.createNewUserGroup,
			{
				method: 'post',
				parameters: '',
				onComplete: function(response) { 
                    self.afterGroupAdded(response, self)
                 }
			});
	},
    
	afterGroupAdded: function(response, self)
	{
        var newGroup = eval('(' + response.responseText + ')');
        self.treeBrowser.insertNewItem(-2, newGroup.ID, newGroup.name, 0, 0, 0, 0, 'SELECT');

        self.activateGroup(newGroup.ID);
        Backend.ajaxNav.add('group_' + newGroup.ID + '#tabUserGroup');
	},
    craftTabUrl: function(url)
    {
        return url.replace(/_id_/, Backend.UserGroup.prototype.treeBrowser.getSelectedItemId());
    },

    craftContainerId: function(tabId)
    {
        return tabId + '_' +  Backend.UserGroup.prototype.treeBrowser.getSelectedItemId() + 'Content';
    },
	
	insertTreeBranch: function(treeBranch, rootId)
	{
		var self = this;
        
            
        $A(treeBranch).each(function(node)
		{
            Backend.UserGroup.prototype.treeBrowser.insertNewItem(node.rootID, node.ID, node.name, null, 0, 0, 0, '', 1);
            self.treeBrowser.showItemSign(node.ID, 0);
            var group = document.getElementsByClassName("standartTreeRow", $("userGroupsBrowser")).last();
            group.id = 'group_' + node.ID;
            group.onclick = function()
            {
                Backend.UserGroup.prototype.treeBrowser.selectItem(node.ID, true);
            }
		});
	},
	
	activateGroup: function(id)
	{
        if(id < 0)
        {
            if(Backend.ajaxNav.getHash().match(/tabUserGroup/))
            {
                Backend.ajaxNav.ignoreNextAdd = false;
                Backend.ajaxNav.add('group_' + id + '#tabUsers');
                Backend.ajaxNav.ignoreNextAdd = true;
            }
            
            var activateTab = $('tabUsers');
            $("tabUserGroup").hide();
            $("tabRoles").hide();
        }
        else
        {
            var activateTab = $('tabUserGroup');
            $("tabUserGroup").show();
            $("tabRoles").show();
        }
        
        if(Backend.UserGroup.prototype.activeGroup && Backend.UserGroup.prototype.activeGroup != id)
        {
            Backend.UserGroup.prototype.activeGroup = id;
    		Backend.UserGroup.prototype.treeBrowser.showFeedback(id);
            
            Backend.ajaxNav.add('group_' + id);
            
            this.tabControl.activateTab(activateTab, function() { 
                Backend.UserGroup.prototype.treeBrowser.hideFeedback(id);
            });
            
            Backend.User.Editor.prototype.showGroupsContainer();
        }
        
        
        
        Backend.UserGroup.prototype.activeGroup = id;
	},
	
	displayCategory: function(response)
	{
		Backend.UserGroup.prototype.treeBrowser.hideFeedback();	
		var cancel = document.getElementsByClassName('cancel', $('userGroupsContent'))[0];
		Event.observe(cancel, 'click', this.resetForm.bindAsEventListener(this));
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
    
    
    openUser: function(id, e) 
    {
        Event.stop(e);
        
        Backend.User.Editor.prototype.setCurrentId(id); 
        $('userIndicator_' + id).style.display = '';
        
    	var tabControl = TabControl.prototype.getInstance(
            'userManagerContainer',
            Backend.User.Editor.prototype.craftTabUrl, 
            Backend.User.Editor.prototype.craftContentId
        ); 
        
        tabControl.activateTab();
        
        if(Backend.User.Editor.prototype.hasInstance(id)) 
    	{
    		Backend.User.Editor.prototype.getInstance(id);			
    	}	
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
		var cont = $('productCount_' + categoryID);
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
    }
}




Backend.UserGroup.GridFormatter = 
{
	getClassName: function(field, value)
	{
		
	},
	
	formatValue: function(field, value, id)
	{
		if ('User.email' == field && Backend.UserGroup.prototype.usersMiscPermision)
		{
		    value = '<span>' + 
                    '<span class="progressIndicator" id="userIndicator_' + id + '" style="display: none;">' + 
                    '</span>' + 
                '</span>' + 
                '<a href="#edit" id="user_' + id + '" onclick="Backend.UserGroup.prototype.openUser(' + id + ', event); return false;">' + 
                     value + 
                '</a>';	
		}
		
		return value;
	}
}



Backend.UserGroup.massActionHandler = Class.create();
Backend.UserGroup.massActionHandler.prototype = 
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
		
		this.form.elements.namedItem('filters').value = this.grid.getFilters().toJSONString();
        this.form.elements.namedItem('selectedIDs').value = this.grid.getSelectedIDs().toJSONString();
        this.form.elements.namedItem('isInverse').value = this.grid.isInverseSelection() ? 1 : 0;
        new LiveCart.AjaxRequest(this.form, document.getElementsByClassName('progressIndicator', this.handlerMenu)[0], this.submitCompleted.bind(this));

        this.grid.resetSelection();   
    },
    
    submitCompleted: function()
    {
        this.grid.reloadGrid();   
    }
}


Backend.User.Group = Class.create();
Backend.User.Group.prototype = 
{
    Links: {},
    Messages: {},
    Instances: {},
    
    initialize: function(root) 
    {
        this.findNodes(root);
        this.bindEvents();
    },
    
    getInstance: function(root) 
    {
        if(!Backend.User.Group.prototype.Instances[$(root).id])
        {
            Backend.User.Group.prototype.Instances[$(root).id] = new Backend.User.Group(root);
        }
        
        return Backend.User.Group.prototype.Instances[$(root).id];
    },
    
    findNodes: function(root)
    {
        this.nodes = {};
        this.nodes.root = $(root);
        this.nodes.form = this.nodes.root.tagName == 'FORM' ? this.nodes.root : this.nodes.root.down('form');


        this.nodes.name = $(this.nodes.form).elements.namedItem('name');
        this.nodes.description = $(this.nodes.form).elements.namedItem('description');
        this.nodes.ID = $(this.nodes.form).elements.namedItem('ID');
    },
        
    bindEvents: function()
    {
        var self = this;
        
    },
    
    save: function() {
        
        var self = this;
        new Ajax.Request(Backend.User.Group.prototype.Links.save + "/" + this.nodes.ID.value,
        {
           method: 'post',
           parameters: Form.serialize(self.nodes.form),
           onSuccess: function(response)
           {
               response = eval("(" + response.responseText + ")");
               self.afterSave(response);
           }
        });
    
        this.saving = false;
    },
    
    afterSave: function(response)
    {
        if(response.status == 'success')
        {
            new Backend.SaveConfirmationMessage($('userGroup_' + this.nodes.ID.value), { message: Backend.User.Group.prototype.Messages.savedMessage, type: 'yellow' });
            Backend.UserGroup.prototype.treeBrowser.setItemText(Backend.UserGroup.prototype.activeGroup, this.nodes.name.value);
        }
        else
        {
            ActiveForm.prototype.serErrorMessages(this.nodes.form, response.errors);
        }
    }
}



Backend.User.Editor = Class.create();
Backend.User.Editor.prototype = 
{
    Links: {},
    Messages: {},
    Instances: {},
    CurrentId: null,
    
    getCurrentId: function()
    {
        return Backend.User.Editor.prototype.CurrentId;
    },

    setCurrentId: function(id)
    {
        Backend.User.Editor.prototype.CurrentId = id;
    },

    craftTabUrl: function(url)
    {
        return url.replace(/_id_/, Backend.User.Editor.prototype.getCurrentId());
    },

    craftContentId: function(tabId)
    {
        return tabId + '_' +  Backend.User.Editor.prototype.getCurrentId() + 'Content'
    },

    getInstance: function(id, doInit)
    {
		if(!Backend.User.Editor.prototype.Instances[id])
        {
            Backend.User.Editor.prototype.Instances[id] = new Backend.User.Editor(id);
        }

        if(doInit !== false) Backend.User.Editor.prototype.Instances[id].init();
        
        return Backend.User.Editor.prototype.Instances[id];
    },
   
	showAddForm: function(groupID)
	{
        console.info('add form');
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
        this.nodes.parent = $("tabUserInfo_" + this.id + "Content");
        this.nodes.form = this.nodes.parent.down("form");
		this.nodes.cancel = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');
    },

    bindEvents: function(args)
    {
		var self = this;
		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancelForm()});
    },

    init: function(args)
    {	
		Backend.User.Editor.prototype.setCurrentId(this.id);
        var userIndicator = $('userIndicator_' + this.id);
        if(userIndicator) 
        {
            userIndicator.style.display = 'none';
        }
        this.showUserForm();

        this.tabControl = TabControl.prototype.getInstance("userManagerContainer", false);
        
		new SectionExpander(this.nodes.parent);
    },

    showUserForm: function(args)
    {
		this.hideGroupsContainer();
    },

    hideGroupsContainer: function(args)
    {
        Element.hide($("userGroupsManagerContainer"));
        Element.show($("userManagerContainer"));
    },

    showGroupsContainer: function(args)
    {       
        if($("userManagerContainer")) Element.hide($("userManagerContainer"));
        if($("userGroupsManagerContainer")) Element.show($("userGroupsManagerContainer"));
    },
    

    cancelForm: function()
    {      
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form);
    },
    
    submitForm: function()
    {
		var self = this;
		new Ajax.Request(Backend.User.Editor.prototype.Links.update + "/" + this.id,
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
			new Backend.SaveConfirmationMessage(this.nodes.form.down('.userInfoSaveConf'));
			Form.State.backup(this.nodes.form);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	}
}





Backend.User.Add = Class.create();
Backend.User.Add.prototype = 
{
    Instances: {},
    
    getInstance: function(groupID, grid)
    {
		if(!Backend.User.Add.prototype.Instances[groupID])
        {
            Backend.User.Add.prototype.Instances[groupID] = new Backend.User.Add(groupID, grid);
        }
        
        return Backend.User.Add.prototype.Instances[groupID];
    },
    
    initialize: function(groupID, grid)
  	{
        try
        {
            this.groupID = groupID;
            
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
        this.nodes.parent = $("newUserForm_" + this.groupID);
        this.nodes.form = this.nodes.parent.down("form");
		this.nodes.cancel = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');
        
        this.nodes.menuShowLink = $("userGroup_" + this.groupID + "_addUser");
        this.nodes.menu = $("userGroup_" + this.groupID + "_addUser_menu");
        this.nodes.menuCancelLink = $("userGroup_" + this.groupID + "_addUserCancel");
        this.nodes.menuForm = this.nodes.parent;
    },
   
	showAddForm: function()
	{
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuCancelLink]);
        ActiveForm.prototype.showNewItemForm(this.nodes.menuShowLink, this.nodes.menuForm); 
	},
   
	hideAddForm: function()
	{
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuShowLink]);
        ActiveForm.prototype.hideNewItemForm(this.nodes.menuShowLink, this.nodes.menuForm); 
	},

    bindEvents: function(args)
    {
		var self = this;
		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancelForm()});
		Event.observe(this.nodes.submit, 'click', function(e) { Event.stop(e); self.submitForm()});
        Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); self.cancelForm();});
    },

    cancelForm: function()
    {      
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form);
        this.hideAddForm();
    },
    
    submitForm: function()
    {
        if (!validateForm(this.nodes.form)) 
        { 
            return false; 
        } 
        
		var self = this;
		new Ajax.Request(Backend.User.Editor.prototype.Links.create,
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
            window.usersActiveGrid[this.groupID].reloadGrid();
            Form.State.restore(this.nodes.form);
            this.hideAddForm();
        }
        else
        {
        	ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
        }
	}
}