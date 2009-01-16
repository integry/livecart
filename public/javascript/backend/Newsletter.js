/**
 *	@author Integry Systems
 */

Backend.Newsletter =
{
	formUrl: false,

	addUrl: false,

	statusUrl: false,

	productTabCopy: null,

	formTabCopy: null,

	initialize: function()
	{

	},

	getTabUrl: function(url)
	{
		return url;
	},

	getContentTabId: function(id)
	{
		return id + 'Content';
	},

	showAddForm: function(caller)
	{
		var container = $('addMessageContainer');

		// product form has already been downloaded
		if (this.formTabCopy)
		{
			container.update('');
			container.appendChild(this.formTabCopy);
			this.initAddForm();
		}

		// retrieve product form
		else
		{
			var url = Backend.Newsletter.links.add;
			new LiveCart.AjaxUpdater(url, container, caller.up('.menu').down('.progressIndicator'));
		}
	},

	hideAddForm: function()
	{
		if ($('addMessageContainer'))
		{
			Element.hide($('addMessageContainer'));
		}

		if ($('newsletterTabContainer'))
		{
			Element.show($('newsletterTabContainer'));
		}
	},

	cancelAdd: function(noHide)
	{
		container = $('addMessageContainer');

		if (!noHide)
		{
			Element.hide(container);
			Element.show($('newsletterTabContainer'));
		}

		ActiveForm.prototype.destroyTinyMceFields(container);
		this.formTabCopy = container.down('.productForm');
	},

	resetAddForm: function(form)
	{
		ActiveForm.prototype.resetTinyMceFields(form);
	},

	initAddForm: function()
	{
		container = $('addMessageContainer');

		Element.hide($('newsletterTabContainer'));
		Element.show(container);

		tinyMCE.idCounter = 0;
		ActiveForm.prototype.initTinyMceFields(container);

		this.reInitAddForm();

		ActiveForm.prototype.resetErrorMessages(container.down('form'));
	},

	reInitAddForm: function()
	{
		// focus title field
		$('addMessageContainer').down('form').elements.namedItem('subject').focus();
	},

	saveForm: function(form)
	{
		var saveHandler = new Backend.Newsletter.saveHandler(form);
		this.request = new LiveCart.AjaxRequest(form, null, saveHandler.saveComplete.bind(saveHandler),  {onInteractive: saveHandler.saveComplete.bind(saveHandler) });
	},

	openNewsletter: function(id, e, onComplete)
	{
		if ($('newsletterIndicator_' + id))
		{
			Element.show($('newsletterIndicator_' + id));
		}

		Backend.Newsletter.Editor.prototype.setCurrentId(id);

		var tabControl = TabControl.prototype.getInstance('newsletterManagerContainer', Backend.Newsletter.Editor.prototype.craftUrl, Backend.Newsletter.Editor.prototype.craftId);

		tabControl.activateTab(null, function(response)
		{
			var inst = Backend.Newsletter.Editor.prototype.getInstance(id);

			if(onComplete)
			{
				onComplete(response);
			}

			inst.hideMainContainer();

			//Backend.ajaxNav.add("#product_" + id);
		}.bind(this));

		if(Backend.Newsletter.Editor.prototype.hasInstance(id))
		{
			Backend.Newsletter.Editor.prototype.getInstance(id);
		}

		if (e)
		{
			Event.stop(e);
		}
	 },

	initForm: function(container)
	{
		$A(container.getElementsByTagName('textarea')).each(function(textarea)
		{
			editAreaLoader.init({
				id : textarea.id,		// textarea id
				syntax: "html",			// syntax to be uses for highgliting
				start_highlight: true,		// to display with highlight mode on start-up
				allow_toggle: false,
				allow_resize: true
				}
			);
		});
	},

	setPath: function(path)
	{
		this.categoryPaths = path;
	},

	resetEditors: function()
	{
		Backend.Newsletter.productTabCopy = null;
		Backend.Newsletter.formTabCopy = null;
		Backend.Newsletter.Editor.prototype.__instances__ = {};
		Backend.Newsletter.Editor.prototype.__currentId__ = null;

		$('newsletterManagerContainer').down('.sectionContainer').innerHTML = '';

		TabControl.prototype.__instances__ = {};
	},

	reloadGrid: function()
	{
		var table = $('newsletters_0');

		if (!table && Backend.Newsletter.productTabCopy)
		{
			table = Backend.Newsletter.productTabCopy.getElementsByTagName('table')[0];
		}

		if (table)
		{
			table.gridInstance.reloadGrid();
		}
	},

	updateRecipientCount: function(element)
	{
		var form = element.form;
		var container = form.down('.recipientCount');
		new LiveCart.AjaxRequest(Backend.Newsletter.links.recipientCount, container.down('.progressIndicator'),
			function(originalRequest)
			{
				container.innerHTML = originalRequest.responseText;
			},
			{ parameters: Form.serialize(form) });
	},

	cancel: function(element)
	{
		this.request.request.transport.abort();
		element.up('form').down('.sendProgress').hide();
	}
}

Backend.Newsletter.saveHandler = Class.create();
Backend.Newsletter.saveHandler.prototype =
{
	formerLength: 0,

  	initialize: function(form)
  	{
		this.form = form;
	},

	saveComplete: function(originalRequest)
	{
	  	ActiveForm.prototype.resetErrorMessages(this.form);

		if (originalRequest.responseData)
		{
			var response = originalRequest.responseData;
		}

		if (response && response.errors)
		{
			ActiveForm.prototype.setErrorMessages(this.form, response.errors);
		}
		else if (response && response.ID)
		{
			Backend.Newsletter.reloadGrid();

			if (!this.form.elements.namedItem('id'))
			{
				// continue to edit the newly added message
				Element.show($('loadingNewsletter'));
				Backend.Newsletter.openNewsletter(response.ID,
											null,
											function()
											{
												Element.hide($('loadingNewsletter'));
												Backend.Newsletter.cancelAdd();
												this.form.reset();
												new Backend.SaveConfirmationMessage($('tabMessageInfo_' + response.ID + 'Content').down('.confirmations').down('.messageSaved'));
											}.bind(this)
											);
			}
			else
			{
				// show save confirmation
				new Backend.SaveConfirmationMessage(this.form.up('.tabPageContainer').down('.confirmations').down('.messageSaved'));
			}
 		}
		else
		{
			if (!this.progressBar)
			{
				var cont = this.form.down('.sendProgress');
				cont.show();
				this.progressBar = new Backend.ProgressBar(cont);

				this.sentCount = this.form.down('.sentCount');
				this.initialSentCount = parseFloat(this.sentCount.innerHTML);

				this.form.down('.statusString').innerHTML = this.form.down('.statusPartial').innerHTML;
			}

			var response = originalRequest.responseText.substr(this.formerLength + 1);
			this.formerLength = originalRequest.responseText.length;

			var portions = response.split('|');

			for (var k = 0; k < portions.length; k++)
			{
				response = eval('(' + decode64(portions[k]) + ')');

				// progress
				if (response.progress != undefined)
				{
					this.setProgress(response);
				}
			}
		}
	},

	setProgress: function(response)
	{
		if (response.progress > 0)
		{
			this.progressBar.update(response.progress, response.total);
			this.sentCount.innerHTML = this.initialSentCount + response.progress;
		}
		else
		{
			this.sentCount.innerHTML = this.initialSentCount + response.total;
			this.form.down('.statusString').innerHTML = this.form.down('.statusSent').innerHTML;
			this.form.down('.sendProgress').hide();
/*
			li.removeClassName('inProgress');
			li.addClassName('completed');
			li.down('.progressCount').update(response.total);
			li.down('.progressBarIndicator').hide();
			li.down('.cancel').hide();
*/
			new Backend.SaveConfirmationMessage(this.form.down('.messageComplete'));
			Backend.Newsletter.reloadGrid();
		}
	},
}

Backend.Newsletter.Editor = Class.create();
Backend.Newsletter.Editor.prototype =
{
	__currentId__: null,
	__instances__: {},

	initialize: function(id, path)
  	{
		this.id = id;
		this.path = path;

		this.__nodes__();
		this.__bind__();

		Form.State.backup(this.nodes.form);

		var self = this;
	},

	__nodes__: function()
	{
		this.nodes = {};
		this.nodes.parent = $("tabMessageInfo_" + this.id + "Content");
		this.nodes.form = this.nodes.parent.down("form");
		this.nodes.cancel = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');
	},

	__bind__: function(args)
	{
		var self = this;
		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancelForm()});
	},

	__init__: function(tabs)
	{
		Backend.Newsletter.Editor.prototype.setCurrentId(this.id);

		if ($('newsletterIndicator_' + this.id))
		{
			Element.hide($('newsletterIndicator_' + this.id));
		}

		this.showProductForm();
		this.tabControl = TabControl.prototype.getInstance("newsletterManagerContainer", false);

		this.setPath();

		this.addTinyMce();
	},

	setPath: function() {
/*
		Backend.Breadcrumb.display(
			this.path,
			this.nodes.form.elements.namedItem("name").value
		);
*/
	},

	craftUrl: function(url)
	{
		return url.replace(/_id_/, Backend.Newsletter.Editor.prototype.getCurrentId());
	},

	craftId: function(tabId)
	{
		return tabId + '_' +  Backend.Newsletter.Editor.prototype.getCurrentId() + 'Content'
	},

	getCurrentId: function()
	{
		return Backend.Newsletter.Editor.prototype.__currentId__;
	},

	setCurrentId: function(id)
	{
		Backend.Newsletter.Editor.prototype.__currentId__ = id;
	},

	getInstance: function(id, doInit, path, tabs)
	{
		if(!Backend.Newsletter.Editor.prototype.__instances__[id])
		{
			Backend.Newsletter.Editor.prototype.__instances__[id] = new Backend.Newsletter.Editor(id, path);
		}

		if(doInit !== false)
		{
			Backend.Newsletter.Editor.prototype.__instances__[id].__init__(tabs);
		}

		return Backend.Newsletter.Editor.prototype.__instances__[id];
	},

	hasInstance: function(id)
	{
		return this.__instances__[id] ? true : false;
	},

	showProductForm: function(args)
	{
		this.hideMainContainer();
	},

	cancelForm: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form);
		ActiveForm.prototype.resetTinyMceFields(this.nodes.form);
		this.showMainContainer();
	},

	submitForm: function()
	{
		new LiveCart.AjaxRequest(this.nodes.form, null, this.formSaved.bind(this));
	},

	formSaved: function(responseJSON)
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		var responseObject = eval("(" + responseJSON.responseText + ")");
		this.afterSubmitForm(responseObject);
	},

	afterSubmitForm: function(response)
	{
		if(!response.errors || 0 == response.errors.length)
		{
			//Form.State.backup(this.nodes.form);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	},

	hideMainContainer: function(args)
	{
		Element.hide($("newsletterTabContainer"));
		Element.show($("newsletterManagerContainer"));
	},

	showMainContainer: function(args)
	{
		Element.hide($("newsletterManagerContainer"));
		Element.show($("newsletterTabContainer"));

		// container element height may not be reduced automatically when closing a longer product form,
		// so sometimes extra whitespace remains below the product list
		Backend.LayoutManager.prototype.collapseAll($('pageContentInnerContainer'));
	},

	removeTinyMce: function()
	{
		ActiveForm.prototype.destroyTinyMceFields(this.nodes.parent);
	},

	addTinyMce: function()
	{
		ActiveForm.prototype.initTinyMceFields(this.nodes.parent);
	}
}

Backend.Newsletter.GridFormatter =
{
	url: '',

	getClassName: function(field, value)
	{

	},

	formatValue: function(field, value, id)
	{
		if ('NewsletterMessage.subject' == field)
		{
			value = '<span>' +
						'<span class="progressIndicator" id="newsletterIndicator_' + id + '" style="display: none;"></span>' +
					'</span>' +
					'<a href="#newsletter_' + id + '" id="newsletter_' + id + '" onclick="Backend.Newsletter.openNewsletter(' + id + ', event); return false;">' +
						value +
					'</a>';
		}

		if ('NewsletterMessage.status' == field)
		{
			value = Backend.getTranslation('_status_' + value);
		}

		return value;
	}
}