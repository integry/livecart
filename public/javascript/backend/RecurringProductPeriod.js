Backend.RecurringProductPeriod = Class.create();
Backend.RecurringProductPeriod.prototype =
{
	Instances: {},

	nodes : {},

	ActiveListCallbacks: {
			beforeDelete: function(li)
			{
				li = $(li);
				if(confirm(Backend.RecurringProductPeriod.prototype.properties.message_confirm_remove.replace("[_1]", li.down("span",1).innerHTML)))
				{
					return Backend.RecurringProductPeriod.prototype.properties.link_remove.replace('_id_', this.getRecordId(li));
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

			beforeEdit: function(li)
			{
				$A(li.up("ul").getElementsByClassName("hiddenActiveListTitle")).each(function(li){
					$(li).removeClassName("hiddenActiveListTitle");
				});
				if(this.isContainerEmpty(li, 'edit'))
				{
					return Backend.RecurringProductPeriod.prototype.properties.link_edit.replace('_id_', this.getRecordId(li));
				}
				else
				{
					this.toggleContainer(li, 'edit');
					Backend.RecurringProductPeriod.prototype.getInstance($(li).down("form")).hideNewForm();
					if (li.down(".activeList_editContainer").style.display == "none")
					{
						li.down("span", 1).addClassName("hiddenActiveListTitle");
					}
				}
			},

			afterEdit: function(li, response)
			{
				li = $(li);
				this.getContainer(li, 'edit').update(response);
				this.toggleContainer(li, 'edit');
				li.down("span", 1).addClassName("hiddenActiveListTitle");
				Backend.RecurringProductPeriod.prototype.getInstance(li.down("form")).hideNewForm();
			},

			beforeSort:	 function(li, order) { },

			afterSort:	  function(li, response) { }
	},
	
	initialize: function(root)
	{
		this.findUsedNodes(root);
		this.rppActiveList = ActiveList.prototype.getInstance(this.nodes.rppList);
		this.bindEvents();
	},

	findUsedNodes: function(root)
	{
		this.nodes = {};
		this.nodes.root = $(root);
		this.nodes.tabContent = root.up(".tabRecurringContent");
		this.nodes.rppList = this.nodes.tabContent.down(".activeList");
		this.nodes.menu = this.nodes.tabContent.down(".rpp_new_menu");
		this.nodes.menuCancelLink = this.nodes.tabContent.down(".rpp_new_cancel");
		this.nodes.menuForm = this.nodes.tabContent.down(".rpp_new_form");
		this.nodes.form = this.nodes.root.nodeName == 'FORM' ? this.nodes.root : this.nodes.root.down('form');
		this.nodes.controls = this.nodes.root.down('.rpp_controls');
		this.nodes.save = this.nodes.controls.down('.rpp_save');
		this.nodes.cancel = this.nodes.controls.down('.rpp_cancel');
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

	cancel: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		if(!this.nodes.form.elements.namedItem('ID').value)
		{
			this.hideNewForm();
		}
		else
		{
			this.rppActiveList.toggleContainerOff(this.rppActiveList.getContainer(this.nodes.root.up('li'), 'edit' ));
			Form.State.restore(this.nodes.form);
		}
	},

	getInstance: function(root)
	{
		root = $(root);
		if(!Backend.RecurringProductPeriod.prototype.Instances[root.id])
		{
			Backend.RecurringProductPeriod.prototype.Instances[root.id] = new Backend.RecurringProductPeriod(root);
		}
		return Backend.RecurringProductPeriod.prototype.Instances[root.id];
	},

	showNewForm: function()
	{
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.show("addRpp", this.nodes.menuForm);

		$A($(this.nodes.menuForm.up(".tabRecurringContent")).getElementsByClassName("hiddenActiveListTitle")).each(function(li) {
			$(li).removeClassName("hiddenActiveListTitle");
		});
	},

	hideNewForm: function()
	{
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.hide("addRpp", this.nodes.menuForm);

		Form.State.restore(this.nodes.form);
	},

	save: function()
	{
		try {
			ActiveForm.prototype.setErrorMessages(this.nodes.form);
			new LiveCart.AjaxRequest(this.nodes.form, null, this.saveCompleted.bind(this));
		} catch(e) {};
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
				var span = '<span class="error">' +
								 this.nodes.form.elements.namedItem('name').value +
							"</span>";
				 this.rppActiveList.addRecord(response.rpp.ID, span, true);
				 this.hideNewForm();
			}
			else
			{
				var title = this.nodes.root.up('li').down("span", 1);
				title.update(this.nodes.form.elements.namedItem('name').value)
				title.removeClassName("hiddenActiveListTitle");
				this.rppActiveList.toggleContainer(this.nodes.root.up('li'), 'edit', 'yellow');
				Form.State.backup(this.nodes.form);
			}
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
		}
	}
}
