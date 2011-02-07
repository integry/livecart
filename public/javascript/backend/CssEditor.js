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

		Event.observe($("minimenu_"+tabid), 'change', this.miniMenuControlChanged.bindAsEventListener(this, tabid));
		this.updateMiniMenuVisibility(tabid);
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
	},

	refillDropdown: function(prefix, tabid, data, value)
	{
		var select = $(prefix+'_'+tabid);
		value = typeof value == "undefined" || value === null ? select.value : value;
		select.innerHTML = "";
		$H(data).each(
			function(select, item)
			{
				var option = document.createElement("option");
				select.appendChild(option);
				option.value=item[0];
				option.innerHTML = item[1];
			}.bind(this, select)
		);
		select.value = value;
	},

	updateMiniMenuVisibility: function(tabid)
	{
		$A($("minimenu_"+tabid).getElementsByTagName("select")).each(
			function(item)
			{
				if (item.getElementsByTagName("option").length <= 1)
				{
					$(item).addClassName("hidden");
				}
				else
				{
					$(item).removeClassName("hidden");
				}
			}
		);
	},

	miniMenuControlChanged: function(event, tabid)
	{
		if (event)
		{
			Event.stop(event);
		}
		var
			element = Event.element(event),
			info = this.openedFiles.find(function(item) {return item[1] == tabid;}),
			indicator = element.up(".minimenu").down("span"),
			url,
			version;
		indicator.addClassName("progressIndicator");
		if (element.hasClassName("version"))
		{
			version = element.value;
		}
		else if (element.hasClassName("othertheme"))
		{
			$("theme_" + tabid).value = element.value;
		}
		url = this.templateDataUrl(tabid, info[0], version);
		new LiveCart.AjaxRequest(url, indicator, this.miniMenuControlChangedResponse.bind(this, tabid));
	},

	miniMenuControlChangedResponse: function(tabid, transport)
	{
		editAreaLoader.setValue('code_'+tabid, transport.responseData.code);
		this.refillDropdown('version',tabid, transport.responseData.backups);
		this.updateMiniMenuVisibility(tabid);
	},

	templateDataUrl: function(tabid, id, version)
	{
		if (typeof version == "undefined")
		{
			version = "";
		}
		return this.urls['templateData'].replace('_tabid_', tabid).replace('_id_', id).replace('_version_', version);
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
			change_callback: "Backend.CssEditorHandler.prototype.editAreaChangeCallback"
		});

		// set cursor at the first line
		/* editAreaLoader.setSelectionRange('code_'+this.tabid, 0, 0); */
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
		try {
			Backend.Theme.prototype.cssTabNotChanged(this.tabid);
			TabControl.prototype.getInstance("tabContainer").reloadTabContent($("tabColors"));
			Backend.Theme.prototype.styleTabNotChanged(this.tabid);

			if (opener)
			{
				opener.location.reload();
			}
		} catch(e) {}

		this.owner.refillDropdown('version',this.tabid, originalRequest.responseData.css.backups);
		this.owner.updateMiniMenuVisibility(this.tabid);
	},

	editAreaChangeCallback: function(id)
	{
		Backend.Theme.prototype.cssTabChanged(id.replace("code_",""));
	}
}