/**
 *	@author Integry Systems
 */

Backend.TaxClass = Class.create();
Backend.TaxClass.prototype =
{
	Messages: {},

	Links: {},

	Instances: {},

	Callbacks: {
		beforeDelete: function(li)
		{
			if(confirm(Backend.TaxClass.prototype.Messages.confirmRemove))
			{
				return Backend.TaxClass.prototype.Links.remove + "/" + this.getRecordId(li);
			}
		},

		afterDelete: function(li, response)
		{
			 try
			 {
				 response = eval('(' + response + ')');
			 }
			 catch(e)
			 {
				 return false;
			 }
		},

		beforeEdit:	 function(li)
		{
			if(this.isContainerEmpty(li, 'edit'))
			{
				return Backend.TaxClass.prototype.Links.edit + '/' + this.getRecordId(li)
			}
			else
			{
				var newClassForm = $("class_new_form").down('form');
				if(newClassForm.up().style.display == 'block')
				{
					Backend.TaxClass.prototype.getInstance(newClassForm).hideNewForm();
				}

				this.toggleContainer(li, 'edit');
			}

			var title = li.down(".class_viewMode");
			var titleDisplay = title.style.display;
			document.getElementsByClassName("class_viewMode", $("tabManageContent")).each(function(title) {
				title.style.display = 'inline';
			});

			title.style.display = (titleDisplay != 'none') ? 'none' : 'inline';
		},

		afterEdit:	  function(li, response)
		{
			var newClassForm = $("class_new_form").down('form');
			if(newClassForm.up().style.display == 'block')
			{
				Backend.TaxClass.prototype.getInstance(newClassForm).hideNewForm();
			}

			var title = li.down(".class_viewMode");
			var titleDisplay = title.style.display;
			document.getElementsByClassName("class_viewMode", $("tabManageContent")).each(function(title) {
				title.style.display = 'inline';
			});

			title.style.display = (titleDisplay != 'none') ? 'none' : 'inline';

			this.getContainer(li, 'edit').update(response);
			this.toggleContainer(li, 'edit');
		},

		beforeSort:	 function(li, order)
		{
			return Backend.TaxClass.prototype.Links.sort + '?target=class_classesList&' + order
		},

		afterSort:	  function(li, response) { }
	},

	initialize: function(root)
	{
		this.findUsedNodes(root);
		this.classActiveList = ActiveList.prototype.getInstance(this.nodes.classList);
		Form.State.backup(this.nodes.form);
		this.bindEvents();
	},

	findUsedNodes: function(root)
	{
		this.nodes = {};

		this.nodes.root = $(root);
		this.nodes.form = this.nodes.root.nodeName == 'FORM' ? this.nodes.root : this.nodes.root.down('form');

		if(! this.nodes.form.elements.namedItem('ID').value)
		{
			this.nodes.menu = $("class_new_menu");
			this.nodes.menuCancelLink = $("class_new_cancel");
			this.nodes.menuShowLink = $("class_new_show");
			this.nodes.menuForm = $("class_new_form");
		}

		this.nodes.classList = $("class_classesList");

		this.nodes.controls = this.nodes.root.down('.class_controls');
		this.nodes.save = this.nodes.controls.down('.class_save');
		this.nodes.cancel = this.nodes.controls.down('.class_cancel');
	},

	bindEvents: function()
	{
		var self = this;

		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancel(); });
		if(!this.nodes.form.elements.namedItem('ID').value)
		{
			Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); self.cancel(); });
		}
	},

	getInstance: function(root)
	{
		if(!Backend.TaxClass.prototype.Instances[$(root).id])
		{
			Backend.TaxClass.prototype.Instances[$(root).id] = new Backend.TaxClass(root);
		}

		return Backend.TaxClass.prototype.Instances[$(root).id];
	},

	getTabUrl: function(url)
	{
		return url;
	},

	getContentTabId: function(id)
	{
		return id + 'Content';
	},

	save: function()
	{
		ActiveForm.prototype.setErrorMessages(this.nodes.form);

		var action = this.nodes.form.elements.namedItem('ID').value
			? Backend.TaxClass.prototype.Links.update
			: Backend.TaxClass.prototype.Links.create;

		new LiveCart.AjaxRequest(this.nodes.form, null, this.saveCompleted.bind(this));
	},

	saveCompleted: function(response)
	{
	   response = eval("(" + response.responseText + ")");
	   this.afterSave(response);
	},

	afterSave: function(response)
	{
		if(response.status == 'success')
		{
			if(!this.nodes.form.elements.namedItem('ID').value)
			{
				var span = '<span class="class_viewMode error">' +
								 this.nodes.form.elements.namedItem('name').value +
							"</span>";
				 this.classActiveList.addRecord(response.class.ID, span, true);

				 this.hideNewForm();
			}
			else
			{
				var title = this.nodes.root.up('li').down(".class_viewMode");
				title.update(this.nodes.form.elements.namedItem('name').value)
				title.style.display = (title.style.display != 'none') ? 'none' : 'inline';

				this.classActiveList.toggleContainer(this.nodes.root.up('li'), 'edit', 'yellow');

				Form.State.backup(this.nodes.form);
			}
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
		}
	},

	cancel: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		if(!this.nodes.form.elements.namedItem('ID').value)
		{
			this.hideNewForm();
		}
		else
		{
			this.classActiveList.toggleContainerOff(this.classActiveList.getContainer(this.nodes.root.up('li'), 'edit' ));
			Form.State.restore(this.nodes.form);

			var title = this.nodes.root.up('li').down(".class_viewMode");
			title.style.display = (title.style.display != 'none') ? 'none' : 'inline';
		}
	},

	showNewForm: function()
	{
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.show("addClass", this.nodes.menuForm);

		document.getElementsByClassName("class_viewMode", $("tabManageContent")).each(function(title) {
			title.style.display = 'inline';
		});
	},

	hideNewForm: function()
	{
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.hide("addClass", this.nodes.menuForm);

		Form.State.restore(this.nodes.form);
	}
}