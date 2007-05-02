Backend.Settings = Class.create();

Backend.Settings.prototype = 
{
  	treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(categories)
	{
		this.treeBrowser = new dhtmlXTreeObject("settingsBrowser","","", false);
		
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
	},
	
	insertTreeBranch: function(treeBranch, rootId)
	{
		for (k in treeBranch)
		{
		  	if('function' != typeof treeBranch[k])
		  	{
				this.treeBrowser.insertNewItem(rootId, k, treeBranch[k].name, null, 0, 0, 0, '', 1);
				this.treeBrowser.showItemSign(k, 0);
				
				if (treeBranch[k].subs)
				{
					this.insertTreeBranch(treeBranch[k].subs, k);
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
			var upd = new LiveCart.AjaxUpdater(url, 'settingsContent', 'settingsIndicator');
			upd.onComplete = this.displayCategory.bind(this);
		}
	},
	
	displayCategory: function(response)
	{
		this.treeBrowser.hideFeedback();	
		var cancel = document.getElementsByClassName('cancel', $('settingsContent'))[0];
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