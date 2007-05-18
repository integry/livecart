Backend.Roles = Class.create();
Backend.Roles.prototype = 
{
    Messages: {},
    
    Links: {},
    
    Instances: {},

    initialize: function(root, roles, activeRoles)
    {
        this.findUsedNodes(root);
        this.bindEvents();
        this.initTree(roles, activeRoles);
    },
    
    initTree: function(roles, activeRoles)
    {
        var self = this;
        
		this.rolesTree = new dhtmlXTreeObject(this.nodes.rolesTree.id, "", "", false);
		this.rolesTree.def_img_x = 'auto';
		this.rolesTree.def_img_y = 'auto';	
		this.rolesTree.setImagePath("image/backend/dhtmlxtree/");
        this.rolesTree.enableCheckBoxes(true);
        this.rolesTree.enableThreeStateCheckboxes(true);
        
        $A(roles).each(function(node)
		{
            self.rolesTree.insertNewItem(node.parent, node.ID, node.name, null, 0, 0, 0, '', 1);
            self.rolesTree.showItemSign(node.ID, 0);
            
            
            if($A(activeRoles).indexOf(node.ID) >= 0)
            {
                self.rolesTree.setCheck(node.ID, true);
            }
		});
    },
    
    
    findUsedNodes: function(root)
    {
        this.nodes = {};
        
        this.nodes.root = $(root);
        this.nodes.form = this.nodes.root.nodeName == 'FORM' ? this.nodes.root : this.nodes.root.down('form'); 
        
        this.nodes.rolesTree = $("userGroupsRolesTree");
        
        this.nodes.controls = this.nodes.root.down('.roles_controls');
        this.nodes.save = this.nodes.controls.down('.roles_save');
        this.nodes.cancel = this.nodes.controls.down('.roles_cancel');
    },
    
    bindEvents: function()
    {
        var self = this;
        
        Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancel(); });
    },
    
    getInstance: function(root, roles, activeRoles)
    {
        if(!Backend.Roles.prototype.Instances[$(root).id])
        {
            Backend.Roles.prototype.Instances[$(root).id] = new Backend.Roles(root, roles, activeRoles);
        }
        
        return Backend.Roles.prototype.Instances[$(root).id];
    },
    
	getTabUrl: function(url)
	{
		return url;
	},
	
	getContentTabId: function(id)
	{		
		return id + 'Content';
	},
    
    save: function(event)
    {
        Event.stop(event);
        
        var self = this;
        new Ajax.Request(this.nodes.form.action, {
           method: 'post',
           parameters: 'checked=' + this.rolesTree.getAllChecked() + '&unchecked=' + this.rolesTree.getAllUnchecked(),
           onSuccess: function(response)
           {
               response = eval("(" + response.responseText + ")");
               self.afterSave(response);
           }
        });
    },
    
    afterSave: function(response)
    {
        if(response.status == 'success')
        {
            new Backend.SaveConfirmationMessage(this.nodes.root.up().down('.yellowMessage'));	
        }
        else
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
        }
    },
 
    cancel: function()
    {
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        if(!this.nodes.form.elements.namedItem('ID').value)
        {
            this.hideNewForm();
        }
        else
        {
            this.taxActiveList.toggleContainerOff(this.taxActiveList.getContainer(this.nodes.root.up('li'), 'edit' ));
            Form.State.restore(this.nodes.form);
            
            var title = this.nodes.root.up('li').down(".tax_viewMode");
            title.style.display = (title.style.display != 'none') ? 'none' : 'inline';
        }
    }
}