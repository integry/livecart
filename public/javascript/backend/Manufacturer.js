/**
 *	@author Integry Systems
 */

if (!Backend.Manufacturer)
{
	Backend.Manufacturer = {}
}

Backend.Manufacturer.GridFormatter =
{
	url: '',

	formatValue: function(field, value, id)
	{
		if ('Manufacturer.name' == field)
		{
			value = '<span><span class="progressIndicator manufacturerIndicator" id="manufacturerIndicator_' + id + '" style="display: none;"></span></span>' +
				'<a href="' + this.url + '#manufacturer_' + id + '" id="manufacturer_' + id + '" onclick="Backend.Manufacturer.Editor.prototype.open(' + id + ', event); return false;">' +
					 value +
				'</a>';
		}

		return value;
	}
}

Backend.Manufacturer.Editor = Class.create();
Backend.Manufacturer.Editor.prototype =
{
	Links: {},
	Messages: {},
	Instances: {},
	CurrentId: null,

	getCurrentId: function()
	{
		return Backend.Manufacturer.Editor.prototype.CurrentId;
	},

	setCurrentId: function(id)
	{
		Backend.Manufacturer.Editor.prototype.CurrentId = id;
	},

	craftTabUrl: function(url)
	{
		return url.replace(/_id_/, Backend.Manufacturer.Editor.prototype.getCurrentId());
	},

	craftContentId: function(tabId)
	{
		return tabId + '_' +  Backend.Manufacturer.Editor.prototype.getCurrentId() + 'Content'
	},

	getInstance: function(id, doInit)
	{
		if(!Backend.Manufacturer.Editor.prototype.Instances[id])
		{
			Backend.Manufacturer.Editor.prototype.Instances[id] = new Backend.Manufacturer.Editor(id);
		}

		if(doInit !== false) Backend.Manufacturer.Editor.prototype.Instances[id].init();

		Backend.Manufacturer.Editor.prototype.setCurrentId(id);

		return Backend.Manufacturer.Editor.prototype.Instances[id];
	},

	hasInstance: function(id)
	{
		return this.Instances[id] ? true : false;
	},

	initialize: function(id)
  	{
		this.id = id ? id : '';

		this.findUsedNodes();
		this.bindEvents();

		Form.State.backup(this.nodes.form, false, false);
	},

	findUsedNodes: function()
	{
		this.nodes = {};
		this.nodes.parent = $("tabUserInfo_" + this.id + "Content");
		this.nodes.form = this.nodes.parent.down("form");
		this.nodes.cancel = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');
	},

	bindEvents: function(args)
	{
		var self = this;
		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancelForm()});
   },

	init: function(args)
	{
		Backend.Manufacturer.Editor.prototype.setCurrentId(this.id);
		var userIndicator = $('manufacturerIndicator_' + this.id);

		if(userIndicator)
		{
			Element.hide(userIndicator);
		}

		Backend.showContainer('manufacturerManagerContainer');
		$('manufacturerGrid').hide();

		this.tabControl = TabControl.prototype.getInstance("manufacturerManagerContainer", false);

		//this.setPath();
	},

	setPath: function() {
		Backend.Breadcrumb.display(
			Backend.UserGroup.prototype.treeBrowser.getSelectedItemId(),
			this.nodes.form.elements.namedItem('email').value
		);
	},

	cancelForm: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form, false, false);

		Backend.hideContainer('manufacturerManagerContainer');
		$('manufacturerGrid').show();

		Backend.Manufacturer.Editor.prototype.setCurrentId(0);
	},

	submitForm: function()
	{
		new LiveCart.AjaxRequest(
			this.nodes.form,
			false,
			function(responseJSON) {
				ActiveForm.prototype.resetErrorMessages(this.nodes.form);
				var responseObject = eval("(" + responseJSON.responseText + ")");
				this.afterSubmitForm(responseObject);
		   }.bind(this)
		);
	},

	afterSubmitForm: function(response)
	{
		if(response.status == 'success')
		{
			window.activeGrids["manufacturer_0"].reloadGrid();
			Form.State.backup(this.nodes.form, false, false);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	},

	resetEditors: function()
	{
		Backend.Manufacturer.Editor.prototype.Instances = {};
		Backend.Manufacturer.Editor.prototype.CurrentId = null;

		$('manufacturerManagerContainer').down('.sectionContainer').innerHTML = '';

		TabControl.prototype.__instances__ = {};
	},

	open: function(id, e, onComplete)
	{
		if (e)
		{
			Event.stop(e);

			if(!e.target)
			{
				e.target = e.srcElement
			}

			Element.show(e.target.up('td').down('.progressIndicator'));
		}

		Backend.Manufacturer.Editor.prototype.setCurrentId(id);

		var tabControl = TabControl.prototype.getInstance(
			'manufacturerManagerContainer',
			Backend.Manufacturer.Editor.prototype.craftTabUrl,
			Backend.Manufacturer.Editor.prototype.craftContentId
		);

		onComplete = function()
		{
			Backend.Manufacturer.Editor.prototype.getInstance(id);
		}

		tabControl.activateTab(null,
								   function(response)
								   {
										if (onComplete)
										{
											onComplete(response);
										}

										Backend.ajaxNav.add("#manufacturer_" + id);
								   });

		if(Backend.Manufacturer.Editor.prototype.hasInstance(id))
		{
			Backend.Manufacturer.Editor.prototype.getInstance(id);
		}
	}
}