/**
 *  CSS file editor
 *	@author Integry Systems
 */
Backend.CssEditor = Class.create();
Backend.CssEditor.prototype =
{
	treeBrowser: null,
	urls: new Array(),
	translations: new Array(),

	openedFiles: $A([]), // contains list of pairs <data entry id> - <tab id>.

	initialize: function(categories)
	{
		this.treeBrowser = new dhtmlXTreeObject("templateBrowser","","", 0);
		Backend.Breadcrumb.setTree(this.treeBrowser);

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
				var title = treeBranch[k].title ? treeBranch[k].title : Backend.getTranslation('_common_css');
				this.treeBrowser.insertNewItem(rootId, treeBranch[k].id, title , null, 0, 0, 0, '');

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
		
			// var upd = new LiveCart.AjaxUpdater(url, 'templateContent');
			// upd.onComplete = this.displayTemplate.bind(this);
			this.openInTab(id, id.replace(/\.css$/,''), url);

			/*if ($('code'))
			{
				editAreaLoader.delete_instance("code");
			}
			*/

		}
	},

	displayTemplate: function(tabid, response)
	{
		this.treeBrowser.hideFeedback();
		Event.observe($('cancel_'+tabid), 'click', this.cancel.bindAsEventListener(this, tabid));
		new Backend.CssEditorHandler($('templateForm_'+tabid), this, tabid);
	},

	cancel: function(event, tabid)
	{
		if (event)
		{
			Event.stop(event);
		}
		this._removeTab(tabid);
		this.openedFiles.splice(i,1);
	},

	getTabUrl: function(url)
	{
		return url;
	},

	getContentTabId: function(id)
	{
		return id + 'Content';
	},

	openInTab: function(contentDataId, title, url)
	{
		var isOpened = this.openedFiles.find(
			function(item)
			{
				return item[0] == contentDataId;
			}
		);
		if(isOpened)
		{
			this.treeBrowser.hideFeedback(isOpened[0]);
			this.tabControl.activateTab(isOpened[1]);
		}
		else
		{
			var tabid = this.tabControl.addNewTab(title);
			url = url.replace('_tabid_',tabid).replace('_id_', contentDataId);
			this.openedFiles.push([contentDataId, tabid]);
			var upd = new LiveCart.AjaxUpdater(url, this.tabControl.getContentTabId(tabid));
			upd.onComplete = this.displayTemplate.bind(this, tabid);
			if ($('code_'+tabid))
			{
				editAreaLoader.delete_instance("code_"+tabid);
			}
		}
	},

	tabAfterClickCallback: function()
	{
		var tabid = this.tabControl.getActiveTab().id;
		var item = this.openedFiles.find(
			function(item)
			{
				return item[1] == tabid;
			}
		);
		if (item)
		{
			this.treeBrowser.selectItem(item[0]);
		}
	},
	
	_removeTab: function(tabid)
	{
		var
			activeTab = this.tabControl.removeTab(tabid),
			removeIdx = null;

		for (i=0; i<this.openedFiles.length; i++)
		{
			if (this.openedFiles[i][1] == tabid)
			{
				removeIdx = i;
			}

			if (activeTab && this.openedFiles[i][1] == activeTab.id)
			{
				this.treeBrowser.selectItem(this.openedFiles[i][0]);
			}
		}

		if (removeIdx)
		{
			this.openedFiles.splice(removeIdx,1);
		}

		if (activeTab == null)
		{
			this.treeBrowser.clearSelection();
		}
	}
}

/**
 *  Template editor form handler
 */
Backend.CssEditorHandler = Class.create();
Backend.CssEditorHandler.prototype =
{
	form: null,
	tabid: null,

	initialize: function(form, owner, tabid)
	{
		this.form = form;
		this.owner = owner;
		this.tabid = tabid;
		Event.observe(this.form, 'submit', this.submit.bindAsEventListener(this));

		editAreaLoader.init({
			id : "code_"+this.tabid, // textarea id
			syntax: "css",			 // syntax to be uses for highgliting
			start_highlight: true,	 // to display with highlight mode on start-up
			allow_toggle: false,
			allow_resize: true,
			change_callback: "Backend.Theme.prototype.cssTabChanged"
			}
		);

		// set cursor at the first line
		editAreaLoader.setSelectionRange('code_'+this.tabid, 0, 0);
	},

	submit: function(e)
	{
		$('code_'+this.tabid).value = editAreaLoader.getValue('code_'+this.tabid);
		new LiveCart.AjaxRequest(this.form, null, this.saveComplete.bind(this));
		Event.stop(e);
		return false;
	},

	saveComplete: function(originalRequest)
	{
		Backend.Theme.prototype.cssTabNotChanged();
		TabControl.prototype.getInstance("tabContainer").reloadTabContent($("tabColors"));

		if (opener)
		{
			opener.location.reload();
		}
	}
}