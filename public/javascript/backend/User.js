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
        
		this.tabControl = TabControl.prototype.getInstance('userGroupsManagerContainer', this.craftTabUrl, this.craftContainerId, {}); 

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
        if(!Backend.ajaxNav.getHash().match(/group=/))
        {
            Backend.UserGroup.prototype.treeBrowser.selectItem(-1, true);
        }
        
        this.bindEvents();
	},

    bindEvents: function()
    {
        var self = this;
        Event.observe($("userGroups_delete"), 'click', function(e) 
        {
            Event.stop(e);
            self.deleteGroup();
        });
    },
    
    deleteGroup: function()
    {
        var $this = this;
        
        if(confirm(Backend.UserGroup.prototype.Messages.confirmGroupDelete)) 
        {
		    new Ajax.Request(
    			Backend.UserGroup.prototype.Links.remove + '/' + Backend.UserGroup.prototype.activeGroup,
    			{
				    onComplete: function(response) { 
                        response = eval("(" + response.responseText + ")");
                        if('success' == response.status)
                        {
                            Backend.UserGroup.prototype.treeBrowser.deleteItem(Backend.UserGroup.prototype.activeGroup, true);
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
    
	addNewGroup: function()
	{
        var self = this;
        
		new Ajax.Request(
			Backend.UserGroup.prototype.Links.save,
			{
				method: 'post',
				parameters: 'name=' + $("newGroupInput").value,
				onComplete: function(response) { self.afterNewGroupAdded(eval("(" + response.responseText + ")")); }
			});
	},

	afterNewGroupAdded: function(response)
	{
        Backend.UserGroup.prototype.treeBrowser.insertNewItem(0, response.ID, $("newGroupInput").value, 0, 0, 0, 0, 'SELECT');
        $("newGroupInput").value = '';
        this.activateGroup(response.ID);
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
            Backend.UserGroup.prototype.treeBrowser.insertNewItem(rootId, node.ID, node.name, null, 0, 0, 0, '', 1);
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
        if(id == -1)
        {
            if(Backend.ajaxNav.getHash().match(/tabUserGroup/))
            {
                Backend.ajaxNav.ignoreNextAdd = false;
                Backend.ajaxNav.add('group_' + id + '#tabUsers');
                Backend.ajaxNav.ignoreNextAdd = true;
            }
            
            var activateTab = $('tabUsers');
            $("tabUserGroup").hide();
            
        }
        else
        {
            var activateTab = $('tabUserGroup');
            $("tabUserGroup").show();
        }
        
        if(Backend.UserGroup.prototype.activeGroup && Backend.UserGroup.prototype.activeGroup != id)
        {
            Backend.UserGroup.prototype.activeGroup = id;
    		Backend.UserGroup.prototype.treeBrowser.showFeedback(id);
            
            Backend.ajaxNav.add('group_' + id);
            
            this.tabControl.activateTab(activateTab, function() { 
                Backend.UserGroup.prototype.treeBrowser.hideFeedback(id);
            });
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

