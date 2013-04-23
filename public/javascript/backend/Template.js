/**
 *  Template editor
 *	@author Integry Systems
 */
Backend.Template = Class.create();
Backend.Template.prototype =
{
	treeBrowser: null,
	tabControl: null,
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
				var id = arguments.length <= 1 ? arguments[0] : null;
				for (var itemId in this.iconUrls)
				{
					if(id == null ||  id == itemId)
					{
						this.setItemImage(itemId, this.iconUrls[itemId]);
					}
				}
			}


		//this.treeBrowser.enableDragAndDrop(1);
		this.insertTreeBranch(categories, 0);
		this.treeBrowser.closeAllItems();
		//this.treeBrowser.setDragHandler(this.moveTemplate);

		if ($('createTemplate'))
		{
			var createTemplate = $('createTemplate').down('a');
			createTemplate.editUrl = createTemplate.href;
			createTemplate.href = '#create';
			Event.observe(createTemplate, 'click', function(e)
				{
					var el = Event.element(e);
					e.preventDefault();
					this.openInTab('_new', this.translations['_tab_title_new'], el.editUrl);
				}.bindAsEventListener(this));
			var deleteTemplate = $('deleteTemplate').down('a');
			deleteTemplate.url = deleteTemplate.href;
			deleteTemplate.href = '#delete';
			Event.observe(deleteTemplate, 'click', function(e)
				{
					e.preventDefault();
					if (!confirm(Backend.getTranslation('_confirm_template_delete')))
					{
						return false;
					}
					var nodeIdToRemove = this.treeBrowser.getSelectedItemId();
					var item = this.openedFiles.find(
						function(item)
						{
							return item[0] == nodeIdToRemove;
						}
					);
					var upd = new LiveCart.AjaxRequest(Event.element(e).url.replace('_id_', this.treeBrowser.getSelectedItemId()));
					upd.onComplete =
						function(tabid)
						{
							// new LiveCart.AjaxUpdater(this.urls['empty'], 'templateContent', 'settingsIndicator');
							this._removeTab(tabid)
							this.treeBrowser.deleteItem(nodeIdToRemove, true);
						}.bind(this, item[1]);

					if ($('code_'+item[1]))
					{
						editAreaLoader.delete_instance("code_"+item[1]);
					}
				}.bindAsEventListener(this));
		}
	},

	insertTreeBranch: function(treeBranch, rootId)
	{
		for (k in treeBranch)
		{
		  	if('function' != typeof treeBranch[k])
		  	{
				//this.treeBrowser.enableDragAndDrop('temporary_disabled');
				this.treeBrowser.enableDragAndDrop(0);

				if(!treeBranch[k].isCustom && !treeBranch[k].subs)
				{
					this.treeBrowser.enableDragAndDrop(0);
				}
				else
				{
					//this.treeBrowser.enableDragAndDrop('temporary_disabled');
				}

				this.treeBrowser.insertNewItem(rootId, treeBranch[k].id, k, null, 0, 0, 0, '');
				this.treeBrowser.setUserData(treeBranch[k].id, 'isCustom', treeBranch[k].isCustom);
				//this.treeBrowser.lockItem(treeBranch[k].id);

				if (treeBranch[k].subs)
				{
					this.insertTreeBranch(treeBranch[k].subs, treeBranch[k].id);
				}
			}
		}
	},

	activateCategory: function(id)
	{
		var isCustom = this.treeBrowser.getUserData(id, 'isCustom');
		//this.treeBrowser.enableDragAndDrop(isCustom ? 1 : 0);

		if ($('deleteTemplate'))
		{
			if (isCustom)
			{
				$('deleteTemplate').show();
			}
			else
			{
				$('deleteTemplate').hide();
			}
		}

		if (!this.treeBrowser.hasChildren(id))
		{
			this.treeBrowser.showFeedback(id);
			this.openInTab(id,this.tabTitleFromTemplateId(id), this.urls['edit']);

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

	tabTitleFromTemplateId: function(id)
	{
		return id.split("/").pop();
	},

	displayTemplate: function(tabid, response)
	{
		this.treeBrowser.hideFeedback();
		Event.observe($('cancel_'+tabid), 'click', this.cancel.bindAsEventListener(this, tabid));
		if ($("minimenu_"+tabid))
		{
			Event.observe($("minimenu_"+tabid), 'change', this.miniMenuControlChanged.bindAsEventListener(this, tabid));
			this.updateMiniMenuVisibility(tabid);
		}
		if ($('code_'+tabid))
		{
			new Backend.TemplateHandler($("templateForm_"+tabid), this, tabid);
		}
		else
		{
			new Backend.EmailTemplateHandler($("templateForm_"+tabid), this, tabid);
		}
	},

	moveTemplate: function(targetId, parentId, siblingNodeId)
	{
		if (!parentId)
		{
			return false;
		}
		new LiveCart.AjaxRequest(Backend.Category.getUrlForNodeReorder(targetId, parentId, Backend.Category.treeBrowser._reorderDirection));
		return true;
	},

	cancel: function(event, tabid)
	{
		if (event)
		{
			event.preventDefault();
		}
		this._removeTab(tabid);
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
			event.preventDefault();
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
		url = this.templateDataUrl(tabid, info[0], $('othertheme_'+tabid).value, version);
		new LiveCart.AjaxRequest(url, indicator, this.miniMenuControlChangedResponse.bind(this, tabid));
	},

	miniMenuControlChangedResponse: function(tabid, transport)
	{
		editAreaLoader.setValue('code_'+tabid, transport.responseData.code);
		this.refillDropdown('othertheme',tabid, transport.responseData.otherThemes, transport.responseData.theme);
		this.refillDropdown('version',tabid, transport.responseData.backups);
		this.updateMiniMenuVisibility(tabid);
	},

	templateDataUrl: function(tabid, id, theme, version)
	{
		if (typeof version == "undefined")
		{
			version = "";
		}
		return this.urls['templateData'].replace('_tabid_', tabid).replace('_id_', id).replace('_theme_', theme).replace('_version_', version);
	},

	refillVersionDropdown: function(tabid, data)
	{
		var select = $('version_'+tabid);
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

		if (removeIdx !== null)
		{
			this.openedFiles.splice(removeIdx,1);
		}

		if (activeTab == null)
		{
			this.treeBrowser.clearSelection();
		}
	},

	getTabUrl: function(url)
	{
		return url;
	},

	getContentTabId: function(id)
	{
		return id + 'Content';
	},

	setTabControlInstance:function(instance)
	{
		this.tabControl = instance
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
	}
}

/**
 *  Template editor form handler
 */
Backend.TemplateHandler = Class.create();
Backend.TemplateHandler.prototype =
{
	form: null,
	tabid: null,
	initialize: function(form, owner, tabid)
	{
		this.form = form;
		this.owner = owner;
		this.tabid = tabid;
		Event.observe(this.form, 'submit', this.submit.bindAsEventListener(this));
		try {
			editAreaLoader.init({
				id : "code_"+this.tabid,		// textarea id
				syntax: "html",			// syntax to be uses for highgliting
				start_highlight: true,		// to display with highlight mode on start-up
				allow_toggle: false,
				allow_resize: true
				}
			);
		} catch(e) {}
	},

	submit: function(e)
	{
		$('code_'+this.tabid).value = editAreaLoader.getValue('code_'+this.tabid);

		new LiveCart.AjaxRequest(this.form, null, this.saveComplete.bind(this));

		e.preventDefault();
		return false;
	},

	saveComplete: function(originalRequest)
	{
		if (opener)
		{
			opener.location.reload();
		}
		this.owner.refillDropdown('othertheme',this.tabid, originalRequest.responseData.template.otherThemes, originalRequest.responseData.template.theme);
		this.owner.refillDropdown('version',this.tabid, originalRequest.responseData.template.backups);
		this.owner.updateMiniMenuVisibility(this.tabid);

		var tpl = originalRequest.responseData.template;

		if (tpl.isCustomFile && ("true" == originalRequest.responseData.isNew))
		{
			var branch = {};
			tpl.id = tpl.file;
			branch[tpl.file] = tpl;
			this.owner.insertTreeBranch(branch, '');
			this.owner.treeBrowser.selectItem(tpl.file, false, false);

			this.owner.cancel(null, this.tabid);
			this.owner.openInTab(tpl.file, this.owner.tabTitleFromTemplateId(tpl.file), this.owner.urls['edit']);
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

	initialize: function(form, owner, tabid)
	{
		this.owner = owner;
		this.form = form;
		this.tabid = tabid
		this.form.onsubmit = this.submit.bindAsEventListener(this);

		['body', 'html'].each(function(name)
		{
			if ($(name+"_"+this.tabid))
			{
				editAreaLoader.init({
					id : name+"_"+this.tabid,		// textarea id
					syntax: "html",			// syntax to be uses for highgliting
					start_highlight: true,		// to display with highlight mode on start-up
					allow_toggle: false,
					allow_resize: true
					}
				);
			}
		}.bind(this));

		// set cursor at the first line
		editAreaLoader.setSelectionRange('body', 0, 0);

		// initialize editors for other languages

		if ($("templateForm_" + this.tabid).down('.languageFormContent'))
		{
			var langs = $("templateForm_" + this.tabid).down('.languageFormTabs').getElementsByTagName('li');
			for (k = 0; k < langs.length; k++)
			{
				Event.observe(langs[k], 'click',
					function(e)
					{
						var element = Event.element(e);
						if(element.tagName.toLowerCase() != "li")
						{
							element = $(element).up("li");
						}
						var lang = element.className.match(/Tabs_([a-z]{2})/)[1];
						var textareas = $("templateForm_" + this.tabid).down('.languageFormContent').down('.languageFormContainer_' + lang).getElementsByTagName('textarea');
						$A(textareas).each(
							function(textarea) {
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
					}.bindAsEventListener(this)
				);
			}
		}
	},

	submit: function()
	{
		try {
			var
				body = $("body_"+this.tabid),
				html = $("html_"+this.tabid);

			body.value = editAreaLoader.getValue('body_'+this.tabid);

			if (html)
			{
				html.value = editAreaLoader.getValue('html_'+this.tabid);
			}

			var langs = $$('#'+this.tabid+'Content .languageFormContent textarea');
			if (langs)
			{
				for (k = 0; k < langs.length; k++)
				{
					langs[k].value = editAreaLoader.getValue(langs[k].id);
				}
			}
			new LiveCart.AjaxRequest(this.form, null, this.saveComplete.bind(this));
		} catch(e) {console.log(e);}
		return false;
	},

	saveComplete: function(originalRequest)
	{

	}
}

function isIE(){
	if(navigator.appName.indexOf("Microsoft")!=-1)
        if (navigator.userAgent.indexOf('Opera') == -1)
    		return true;
	return false;
}