/**
 *	@author Integry Systems
 */

Backend.Theme = Class.create();

Backend.Theme.prototype =
{
  	treeBrowser: null,

  	urls: new Array(),

	initialize: function(pages)
	{
		this.treeBrowser = new dhtmlXTreeObject("pageBrowser","","", 0);
//		Backend.Breadcrumb.setTree(this.treeBrowser);

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

				if (!this.iconUrls[itemId])
				{
					this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
				}

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

		this.tabControl = TabControl.prototype.getInstance('tabContainer', Backend.Theme.prototype.craftTabUrl, Backend.Theme.prototype.craftContainerId);

		Backend.Theme.prototype.treeBrowser = this.treeBrowser;

		this.treeBrowser.selectItem('barebone', true);
	},

	showAddForm: function()
	{
		$('addForm').show();
		$('addForm').down('input.text').focus();
	},

	hideAddForm: function()
	{
		var form = $('addForm').down('form');
		form.reset();
		ActiveForm.prototype.resetErrorMessages(form)
		$('addForm').hide();
	},

	addTheme: function()
	{
		new LiveCart.AjaxRequest($('addForm').down('form'), null, this.completeAdd.bind(this));
	},

	completeAdd: function(originalRequest)
	{
		var data = originalRequest.responseData;
		if (data && data.errors)
		{
			ActiveForm.prototype.setErrorMessages($('addForm').down('form'), data.errors)
		}
		else
		{
			var name = data.name;
			var ins = {};
			ins[name] = name;
			this.insertTreeBranch(ins, 0);
			this.treeBrowser.selectItem(name, true);
			this.hideAddForm();
		}
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
		this.tabControl.activateTab('tabSettings', function() {
			this.treeBrowser.hideFeedback(id);
		}.bind(this));

		this.showControls();
	},

	deleteSelected: function()
	{
		if (!Backend.getTranslation('_confirm_theme_del'))
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
		var response = originalRequest.responseData;
		this.treeBrowser.hideFeedback(response.name);
		if ('success' == response.status)
		{
			this.treeBrowser.deleteItem(response.name, true);
			this.treeBrowser.selectItem('barebone', true);
		}
	},

	showControls: function()
	{
		if ('barebone' != this.treeBrowser.getSelectedItemId())
		{
			$("removeMenu").show();
		}
		else
		{
			$("removeMenu").hide();
		}

	},

	craftTabUrl: function(url)
	{
		return url.replace(/_id_/, Backend.Theme.prototype.treeBrowser.getSelectedItemId());
	},

	craftContainerId: function(tabId)
	{
		return tabId + '_' +  Backend.Theme.prototype.treeBrowser.getSelectedItemId() + 'Content';
	},
}
