Backend.StaticPage = Class.create();

Backend.StaticPage.prototype = 
{
  	treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(pages)
	{
		this.treeBrowser = new dhtmlXTreeObject("pageBrowser","","", false);
//        Backend.Breadcrumb.setTree(this.treeBrowser);
		
		this.treeBrowser.def_img_x = 'auto';
		this.treeBrowser.def_img_y = 'auto';
				
		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setOnClickHandler(this.activateCategory.bind(this));

		this.treeBrowser.showFeedback = 
			function(itemId) 
			{
				if (!this.iconUrls)
				{
					this.iconUrls = new Object();	
				}
				
				this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
				this.setItemImage(itemId, '../../../image/indicator.gif');
			}
		
		this.treeBrowser.hideFeedback = 
			function()
			{
				for (var itemId in this.iconUrls)
				{
					this.setItemImage(itemId, this.iconUrls[itemId]);	
				}				
			}
		
    	this.insertTreeBranch(pages, 0);    
    	
    	this.showControls();
	},
	
	showAddForm: function()
	{
		this.treeBrowser.clearSelection();
		this.showControls();
        new LiveCart.AjaxUpdater(this.urls['add'], $('pageContent'), $('settingsIndicator'), null, this.displayPage.bind(this));			
	},
	
	initForm: function()
	{
		tinyMCE.idCounter = 0;
        ActiveForm.prototype.initTinyMceFields($('editContainer'));
	},
	
	insertTreeBranch: function(treeBranch, rootId)
	{
		this.treeBrowser.showItemSign(rootId, 0);
		for (k in treeBranch)
		{
		  	if('function' != typeof treeBranch[k])
		  	{
				this.treeBrowser.insertNewItem(rootId, k, treeBranch[k], null, 0, 0, 0, '', 1);
				this.treeBrowser.showItemSign(k, 0);
			}
		}  	
	},
	
	save: function(form)
	{
		form.action = form.id.value 
            ? pageHandler.urls.update
            : pageHandler.urls.create;
            
        new LiveCart.AjaxRequest(form, $('saveIndicator'), this.saveCompleted.bind(this));	
	},
	
	saveCompleted: function(originalRequest)
	{
		var item = eval('(' + originalRequest.responseText + ')');
		
		if (!this.treeBrowser.getItemText(item.id))
		{
			this.treeBrowser.insertNewItem(0, item.id, item.title, null, 0, 0, 0, '', 1);
			this.treeBrowser.selectItem(item.id, true);
		}
		else
		{
			this.treeBrowser.setItemText(item.id, item.title);
		}
	},	
	
	activateCategory: function(id)
	{
//        Backend.Breadcrumb.display(id);
		
		if (!this.treeBrowser.hasChildren(id))
		{
			this.treeBrowser.showFeedback(id);
			var url = this.urls['edit'].replace('_id_', id);
			var upd = new LiveCart.AjaxUpdater(url, 'pageContent', 'settingsIndicator');
			upd.onComplete = this.displayPage.bind(this);
            
            this.showControls()
		}
	},

	displayPage: function(response)
	{
		this.treeBrowser.hideFeedback();
		this.initForm();	
		Event.observe($('cancel'), 'click', this.cancel.bindAsEventListener(this));
	},
	
	deleteSelected: function()
	{
		if (!confirm($('pageDelConf').innerHTML))
		{
			return false;		
		}
	
		var id = this.treeBrowser.getSelectedItemId();
		var url = this.urls['delete'].replace('_id_', id);
		new LiveCart.AjaxRequest(url, null, this.deleteCompleted.bind(this));	
		this.treeBrowser.showFeedback(id);		
	},
	
	deleteCompleted: function(originalRequest)
	{
		var response = eval('(' + originalRequest.responseText + ')');
		
		if (response.id != 0)
		{
            
            parentId = this.treeBrowser.getParentId(response.id)
            categoryIndex = this.treeBrowser.getIndexById(response.id)
			if(parseInt(categoryIndex) - 1 > 0) { 
                secondId = this.treeBrowser.getChildItemIdByIndex(parentId, parseInt(categoryIndex) - 1)
			} else {
				secondId = this.treeBrowser.getChildItemIdByIndex(parentId, parseInt(categoryIndex) + 1)
			}
			
			this.treeBrowser.deleteItem(response.id, true);
			new LiveCart.AjaxUpdater(this.urls['empty'], 'pageContent', 'settingsIndicator');
			
			try 
			{
                this.treeBrowser.selectItem(secondId, true);
			}
			catch(e)
			{
				
			}
		}
	},
	
	moveUp: function()
	{
		var id = this.treeBrowser.getSelectedItemId();
		var url = this.urls['moveup'].replace('_id_', id);
		new LiveCart.AjaxRequest(url, null, this.moveCompleted.bind(this));
		this.treeBrowser.showFeedback(id);
	},

	moveDown: function()
	{
		var id = this.treeBrowser.getSelectedItemId();
		var url = this.urls['movedown'].replace('_id_', id);
		new LiveCart.AjaxRequest(url, null, this.moveCompleted.bind(this));
		this.treeBrowser.showFeedback(id);
	},
		
	moveCompleted: function(originalRequest)
	{
		this.treeBrowser.hideFeedback();	
		var result = eval('(' + originalRequest.responseText + ')');
		
		if (result.status == 'success')
		{
			var direction = ('up' == result.order) ? 'up_strict' : 'down_strict';
			this.treeBrowser.moveItem(result.id, direction);				
		}
		
		this.showControls();
	},
	
	showTemplateCode: function()
	{
        if ($('templateCode'))
        {
            Element.show($('templateCode'));  
            Element.hide($('staticPageMenu'));
        }
    },

    showControls: function()
    {
        var categoryId = this.treeBrowser.getSelectedItemId();
        
        parentId = this.treeBrowser.getParentId(categoryId)
        categoryIndex = this.treeBrowser.getIndexById(categoryId)
        
        nextCategoryId = categoryId ? this.treeBrowser.getChildItemIdByIndex(parentId, parseInt(categoryIndex) + 1) : 0;

        if(nextCategoryId && categoryId) 
        {
            $("moveDownMenu").show();            
        }
        else 
        {
            $("moveDownMenu").hide();
        }
        
        if(categoryId && categoryIndex > 0) 
        {
            $("moveUpMenu").show();
        }
        else
        {
            $("moveUpMenu").hide();
        }

        if(categoryId) 
        {
            $("removeMenu").show();
        }
        else
        {
            $("removeMenu").hide();
        }

    },
    
    cancel: function()
    {
		new LiveCart.AjaxUpdater(this.urls['empty'], 'pageContent', 'settingsIndicator');        
    }
}
