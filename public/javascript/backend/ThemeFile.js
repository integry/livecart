/**
 *	@author Integry Systems
 */

Backend.ThemeFile = Class.create();
Backend.ThemeFile.prototype =
{
	initialize: function(fileList, container, template)
	{
		var theme = container.id.replace("filesList_", "");
		if ($("uploadMenu_"+theme))
		{
			Element.observe("uploadNewFile_"+theme+"_upload", "click", function(e)
			{
				Event.stop(e);
				Backend.ThemeFile.prototype.showAddForm(container, theme);
			});
			Element.observe("uploadNewFile_"+theme+"_cancel", "click", function(e)
			{
				Event.stop(e);
				Backend.ThemeFile.prototype.hideAddForm(container, theme);
			});
		}

		ActiveList.prototype.getInstance(container.id, {
			beforeEdit: function(li)
			{
				if (!this.isContainerEmpty(li, 'edit'))
				{
					li.handler.cancelEditForm();
					return;
				}
				li.handler.showEditForm(container);
				return false;
			},
			beforeDelete: function(li)
			{
				if (confirm($('confirmDelete').innerHTML))
				{
					var url = $('deleteUrl').innerHTML;
					url = url.replace('__FILE__', escape( $(li).down(".file").value ));
					url = url.replace('__THEME__', escape( $(li).down(".theme").value ));
					return url;
				}
			 },

			 beforeSort: function(li, order)
			 {
			 },
			 afterEdit:	  function(li, response) { li.handler.update(response);},
			 afterSort:	  function(li, response) {  },
			 afterDelete:	function(li, response)
			 {
				 try
				 {
					 response = eval('(' + response + ')');
				 }
				 catch(e)
				 {
					 return false;
				 }
			 }
		 }, []);

		fileList.each(function(el)
		{
			new Backend.ThemeFile.FileEntry(container, template, el, false);
		});
		ActiveList.prototype.getInstance(container.id).touch(true);
	},

	showAddForm: function(container, theme)
	{
		var
			form,
			menu;
		this.cancelOpened(container, theme);
		form = $("themeFileForm_"+theme);
		menu = new ActiveForm.Slide("uploadMenu_"+theme);
		menu.show("fileUpload", form);
		form.down("form").reset();
		form.down('a.cancel').onclick = function(e)
			{
				Event.stop(e);
				this.hideAddForm(container, theme);
			}.bindAsEventListener(this);
	},

	hideAddForm: function(container, theme)
	{
		var
			form = $("themeFileForm_"+theme),
			menu = new ActiveForm.Slide("uploadMenu_"+theme);
		menu.hide("fileUpload", form);
	},
	
	cancelOpened: function(container, theme)
	{
		$H($(container).getElementsByTagName('li')).each(function(li)
		{
			if (li && li[1] && li[1].handler)
			{
				li[1].handler.cancelEditForm();
			}
		});
		this.hideAddForm(container, theme);
	}
}

Backend.ThemeFile.FileEntry = Class.create();
Backend.ThemeFile.FileEntry.prototype =
{
	data: null,
	node: null,
	list: null,

	initialize: function(container, template, data, highlight, isNew)
	{
		this.data = data;
		this.list = ActiveList.prototype.getInstance(container.id);
		this.node = this.list.addRecord(data.ID, template.innerHTML, highlight);
		if (isNew)
		{
			this.node.parentNode.insertBefore(this.node, this.node.parentNode.firstChild);
		}
		this.updateHtml();
		this.node.handler = this;
		Element.show(this.node);
	},

	showEditForm: function(container)
	{
		var
			theme = container.id.replace('filesList_',''),
			nodes,
			form;

		Backend.ThemeFile.prototype.hideAddForm(container, theme);
		nodes = this.node.parentNode.getElementsByTagName('li');
		$H(nodes).each(function(li)
		{
			if (li && li[1] && li[1].handler && li != this.node)
			{
				li[1].handler.cancelEditForm();
			}
		});
		form = $('themeFileForm_'+theme).cloneNode(true);
		this.node.down('div.formContainer').appendChild(form);
		form.down("form").reset();
		var fn = form.up("li").down(".file").value;
		form.down('.orginalFileName').value = fn;
		form.down('.changeFileName').value = fn;
		$(form).show();
		form.down('a.cancel').onclick = this.cancelEditForm.bindAsEventListener(this);
		this.list.toggleContainerOn(this.list.getContainer(this.node, 'edit'));
	},

	cancelEditForm: function(e)
	{
		if (!this.list.isContainerEmpty(this.node, 'edit'))
		{
			this.list.toggleContainerOff(this.list.getContainer(this.node, 'edit'));
		}

		var formContainer = this.node.down('div.formContainer');
		if (!formContainer.firstChild)
		{
			return;
		}
		formContainer.innerHTML = '';
		if (e)
		{
			Event.stop(e);
		}
	},

	update: function(originalRequest)
	{
		this.data = originalRequest.responseData;
		this.updateHtml();
		this.cancelEditForm();
		Element.show(this.node.down('.checkbox'));
		ActiveList.prototype.highlight(this.node, 'yellow');
	},

	updateHtml: function()
	{
		var
			tnContainer = this.node.down('.thumbnailContainer');
			cssHintContainer = this.node.down('.cssHintContainer');
		if(this.data.hasThumbnail)
		{
			tnContainer.down("img").src="upload/themes/"+this.data.theme+"/thumbs/"+this.data.fn;
			tnContainer.down("a").href="upload/themes/"+this.data.theme+"/"+this.data.fn;
			cssHintContainer.down('.cssTheme').innerHTML = this.data.theme;
			cssHintContainer.down('.cssFile').innerHTML = this.data.fn;
		}
		else
		{
			cssHintContainer.hide();
			tnContainer.hide();
		}
		this.node.down('.fileName').innerHTML = this.data.fn;

		this.node.down('.file').value = this.data.fn;
		this.node.down('.theme').value = this.data.theme;
	}
}

