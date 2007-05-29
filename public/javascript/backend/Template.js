/**
 *  Template editor
 */
Backend.Template = Class.create();
Backend.Template.prototype = 
{
  	treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(categories)
	{
		this.treeBrowser = new dhtmlXTreeObject("templateBrowser","","", false);
		
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
		
    	this.insertTreeBranch(categories, 0);    
    	this.treeBrowser.closeAllItems();
	},
	
	insertTreeBranch: function(treeBranch, rootId)
	{
		for (k in treeBranch)
		{
		  	if('function' != typeof treeBranch[k])
		  	{
				this.treeBrowser.insertNewItem(rootId, treeBranch[k].id, k, null, 0, 0, 0, '');
				
				if (treeBranch[k].subs)
				{
					this.insertTreeBranch(treeBranch[k].subs, treeBranch[k].id);
				}
			}
		}  	
	},    
	
	activateCategory: function(id)
	{
        if (!this.treeBrowser.hasChildren(id))
		{
			this.treeBrowser.showFeedback(id);
			var url = this.urls['edit'].replace('_id_', id);
			var upd = new LiveCart.AjaxUpdater(url, 'templateContent', 'templateIndicator');
			upd.onComplete = this.displayTemplate.bind(this);
		}
	},	
	
	displayTemplate: function(response)
	{
		this.treeBrowser.hideFeedback();
		Event.observe($('cancel'), 'click', this.cancel.bindAsEventListener(this));
		new Backend.TemplateHandler($('templateForm'));
	},

    cancel: function()
    {
		new LiveCart.AjaxUpdater(this.urls['empty'], 'templateContent', 'settingsIndicator');        
    }	
}

/**
 *  Template editor form handler
 */
Backend.TemplateHandler = Class.create();
Backend.TemplateHandler.prototype = 
{
	form: null,
	
	initialize: function(form)
	{
		this.form = form;
		this.form.onsubmit = this.submit.bindAsEventListener(this);
	},
	
	submit: function()
	{
		var indicator = document.getElementsByClassName('progressIndicator', this.form)[0];
		new LiveCart.AjaxRequest(this.form, indicator, this.saveComplete.bind(this));
		return false;
	},
	
	saveComplete: function(originalRequest)
	{
		var msgClass = originalRequest.responseText ? 'yellowMessage' : 'redMessage';			 
		var msg = new Backend.SaveConfirmationMessage(document.getElementsByClassName(msgClass)[0]);
		 
		msg.show();
		 
		if (opener)
		{
            opener.location.reload();	            
        }
	}
}