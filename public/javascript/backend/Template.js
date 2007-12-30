/**
 *  Template editor
 *	@author Integry Systems
 */
Backend.Template = Class.create();
Backend.Template.prototype =
{
  	treeBrowser: null,

  	urls: new Array(),

	initialize: function(categories)
	{
		this.treeBrowser = new dhtmlXTreeObject("templateBrowser","","", false);
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
			var upd = new LiveCart.AjaxUpdater(url, 'templateContent');
			upd.onComplete = this.displayTemplate.bind(this);
			if ($('code'))
			{
				editAreaLoader.delete_instance("code");
			}

			if ($('body'))
			{
				editAreaLoader.delete_instance("body");

				if ($('templateContent').down('.languageFormContent'))
				{
					var langs = $('templateContent').down('.languageFormContent').getElementsByTagName('textarea');
					for (k = 0; k < langs.length; k++)
					{
						if ($('frame_' + langs[k].id))
						{
							editAreaLoader.delete_instance(langs[k].id);
						}
					}
				}
			}
		}
	},

	displayTemplate: function(response)
	{
		this.treeBrowser.hideFeedback();
		Event.observe($('cancel'), 'click', this.cancel.bindAsEventListener(this));

		if ($('code'))
		{
			new Backend.TemplateHandler($('templateForm'));
		}
		else
		{
			new Backend.EmailTemplateHandler($('templateForm'));
		}
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

		editAreaLoader.init({
			id : "code",		// textarea id
			syntax: "html",			// syntax to be uses for highgliting
			start_highlight: true,		// to display with highlight mode on start-up
			allow_toggle: false,
			allow_resize: true
			}
		);

		// set cursor at the first line
		editAreaLoader.setSelectionRange('code', 0, 0);
	},

	submit: function()
	{
		$('code').value = editAreaLoader.getValue('code');
		new LiveCart.AjaxRequest(this.form, null, this.saveComplete.bind(this));
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

/**
 *  E-mail template editor form handler
 */
Backend.EmailTemplateHandler = Class.create();
Backend.EmailTemplateHandler.prototype =
{
	form: null,

	initialize: function(form)
	{
		this.form = form;
		this.form.onsubmit = this.submit.bindAsEventListener(this);

		editAreaLoader.init({
			id : "body",		// textarea id
			syntax: "html",			// syntax to be uses for highgliting
			start_highlight: true,		// to display with highlight mode on start-up
			allow_toggle: false,
			allow_resize: true
			}
		);

		// set cursor at the first line
		editAreaLoader.setSelectionRange('body', 0, 0);

		// initialize editors for other languages
		if ($('templateContent').down('.languageFormContent'))
		{
			var langs = $('templateContent').down('.languageFormTabs').getElementsByTagName('li');
			for (k = 0; k < langs.length; k++)
			{
				Event.observe(langs[k], 'click',
					function(e)
					{
						var lang = this.className.match(/Tabs_([a-z]{2})/)[1];
						var textarea = $('templateContent').down('.languageFormContent').down('.languageFormContainer_' + lang).down('textarea');

						editAreaLoader.init({
							id : textarea.id,		// textarea id
							syntax: "html",			// syntax to be uses for highgliting
							start_highlight: true,		// to display with highlight mode on start-up
							allow_toggle: false,
							allow_resize: true
							}
						);

					}
				);
			}
		}
	},

	submit: function()
	{
		$('body').value = editAreaLoader.getValue('body');

		if ($('templateContent').down('.languageFormContent'))
		{
			var langs = $('templateContent').down('.languageFormContent').getElementsByTagName('textarea');
			for (k = 0; k < langs.length; k++)
			{
				langs[k].value = editAreaLoader.getValue(langs[k].id);
			}
		}

		new LiveCart.AjaxRequest(this.form, null, this.saveComplete.bind(this));
		return false;
	},

	saveComplete: function(originalRequest)
	{
	}
}