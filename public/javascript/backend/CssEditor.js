/**
 *  CSS file editor
 *	@author Integry Systems
 */
Backend.CssEditor = Class.create();
Backend.CssEditor.prototype =
{
  	treeBrowser: null,

  	urls: new Array(),

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
			var upd = new LiveCart.AjaxUpdater(url, 'templateContent');
			upd.onComplete = this.displayTemplate.bind(this);
			if ($('code'))
			{
				editAreaLoader.delete_instance("code");
			}
		}
	},

	displayTemplate: function(response)
	{
		this.treeBrowser.hideFeedback();
		Event.observe($('cancel'), 'click', this.cancel.bindAsEventListener(this));

		new Backend.CssEditorHandler($('templateForm'), this);
	},

	cancel: function()
	{
		new LiveCart.AjaxUpdater(this.urls['empty'], 'templateContent', 'settingsIndicator');
	}
}

/**
 *  Template editor form handler
 */
Backend.CssEditorHandler = Class.create();
Backend.CssEditorHandler.prototype =
{
	form: null,

	initialize: function(form, owner)
	{
		this.form = form;
		this.owner = owner;
		Event.observe(this.form, 'submit', this.submit.bindAsEventListener(this));

		editAreaLoader.init({
			id : "code",		// textarea id
			syntax: "css",			// syntax to be uses for highgliting
			start_highlight: true,		// to display with highlight mode on start-up
			allow_toggle: false,
			allow_resize: true
			}
		);

		// set cursor at the first line
		editAreaLoader.setSelectionRange('code', 0, 0);
	},

	submit: function(e)
	{
		$('code').value = editAreaLoader.getValue('code');
		new LiveCart.AjaxRequest(this.form, null, this.saveComplete.bind(this));
		Event.stop(e);
		return false;
	},

	saveComplete: function(originalRequest)
	{
		if (opener)
		{
			opener.location.reload();
		}
	}
}