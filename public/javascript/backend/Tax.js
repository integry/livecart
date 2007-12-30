/**
 *	@author Integry Systems
 */
 
Backend.Tax = Class.create();
Backend.Tax.prototype = 
{
	Messages: {},
	
	Links: {},
	
	Instances: {},
	
	Callbacks: {
		beforeDelete: function(li) 
		{
			if(confirm(Backend.Tax.prototype.Messages.confirmRemove))
			{
				return Backend.Tax.prototype.Links.remove + "/" + this.getRecordId(li);
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
				return Backend.Tax.prototype.Links.edit + '/' + this.getRecordId(li)
			}
			else 
			{
				var newTaxForm = $("tax_new_form").down('form');
				if(newTaxForm.up().style.display == 'block')
				{
					Backend.Tax.prototype.getInstance(newTaxForm).hideNewForm();
				}

				this.toggleContainer(li, 'edit');
			}
			
			var title = li.down(".tax_viewMode");
			var titleDisplay = title.style.display;
			document.getElementsByClassName("tax_viewMode", $("tabManageContent")).each(function(title) {
				title.style.display = 'inline';
			});
			
			title.style.display = (titleDisplay != 'none') ? 'none' : 'inline';
		},
	   
		afterEdit:	  function(li, response)
		{
			var newTaxForm = $("tax_new_form").down('form');
			if(newTaxForm.up().style.display == 'block')
			{
				Backend.Tax.prototype.getInstance(newTaxForm).hideNewForm();
			}
	
			var title = li.down(".tax_viewMode");
			var titleDisplay = title.style.display;
			document.getElementsByClassName("tax_viewMode", $("tabManageContent")).each(function(title) {
				title.style.display = 'inline';
			});
			
			title.style.display = (titleDisplay != 'none') ? 'none' : 'inline';
			
			this.getContainer(li, 'edit').update(response);
			this.toggleContainer(li, 'edit');
		},
		 
		beforeSort:	 function(li, order)
		{
			return Backend.Tax.prototype.Links.sort + '?target=tax_taxesList&' + order
		},
	
		afterSort:	  function(li, response) { }
	},
	
	initialize: function(root)
	{
		this.findUsedNodes(root);
		this.taxActiveList = ActiveList.prototype.getInstance(this.nodes.taxList);
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
			this.nodes.menu = $("tax_new_menu");
			this.nodes.menuCancelLink = $("tax_new_cancel");
			this.nodes.menuShowLink = $("tax_new_show");
			this.nodes.menuForm = $("tax_new_form");
		}
		
		this.nodes.taxList = $("tax_taxesList");
		
		this.nodes.controls = this.nodes.root.down('.tax_controls');
		this.nodes.save = this.nodes.controls.down('.tax_save');
		this.nodes.cancel = this.nodes.controls.down('.tax_cancel');
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
		if(!Backend.Tax.prototype.Instances[$(root).id])
		{
			Backend.Tax.prototype.Instances[$(root).id] = new Backend.Tax(root);
		}
		
		return Backend.Tax.prototype.Instances[$(root).id];
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
			? Backend.Tax.prototype.Links.update
			: Backend.Tax.prototype.Links.create;
			
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
				var span = '<span class="tax_viewMode error">' + 
								 this.nodes.form.elements.namedItem('name').value + 
							"</span>";
				 this.taxActiveList.addRecord(response.tax.ID, span, true);
				 
				 this.hideNewForm();
			}
			else
			{
				var title = this.nodes.root.up('li').down(".tax_viewMode");
				title.update(this.nodes.form.elements.namedItem('name').value)
				title.style.display = (title.style.display != 'none') ? 'none' : 'inline';
				
				this.taxActiveList.toggleContainer(this.nodes.root.up('li'), 'edit', 'yellow');
				
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
			this.taxActiveList.toggleContainerOff(this.taxActiveList.getContainer(this.nodes.root.up('li'), 'edit' ));
			Form.State.restore(this.nodes.form);
			
			var title = this.nodes.root.up('li').down(".tax_viewMode");
			title.style.display = (title.style.display != 'none') ? 'none' : 'inline';
		}
	},
	
	showNewForm: function()
	{
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.show("addTax", this.nodes.menuForm);
		
		document.getElementsByClassName("tax_viewMode", $("tabManageContent")).each(function(title) {
			title.style.display = 'inline';
		});
	},
	
	hideNewForm: function()
	{
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.hide("addTax", this.nodes.menuForm);
		
		Form.State.restore(this.nodes.form);
	}
}