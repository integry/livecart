/**
 *	@author Integry Systems
 */

Backend.Settings = Class.create();

Backend.Settings.prototype =
{
  	treeBrowser: null,

  	urls: new Array(),

	initialize: function(categories)
	{
		this.treeBrowser = new dhtmlXTreeObject("settingsBrowser","","", 0);
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

				if (!this.iconUrls[itemId])
				{
					this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
					var img = this._globalIdStorageFind(itemId).htmlNode.down('img', 2);
					img.originalSrc = img.src;
					img.src = 'image/indicator.gif';
				}
			}

		this.treeBrowser.hideFeedback =
			function(itemId)
			{
				if (null != this.iconUrls[itemId])
				{
					this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
					var img = this._globalIdStorageFind(itemId).htmlNode.down('img', 2);
					img.src = img.originalSrc;
					this.iconUrls[itemId] = null;
				}
			}

		this.insertTreeBranch(categories, 0);
		this.treeBrowser.closeAllItems(0);
	},

	insertTreeBranch: function(treeBranch, rootId)
	{
		for (k in treeBranch)
		{
		  	if('function' != typeof treeBranch[k])
		  	{
				this.treeBrowser.insertNewItem(rootId, k, treeBranch[k].name, null, 0, 0, 0, '', 1);
				this.treeBrowser.showItemSign(k, 1);

				if (treeBranch[k].subs)
				{
					this.insertTreeBranch(treeBranch[k].subs, k);
				}
			}
		}
	},

	activateCategory: function(id)
	{
		Backend.Breadcrumb.display(id);
		this.treeBrowser.showFeedback(id);
		var url = this.urls['edit'].replace('_id_', id);
		var upd = new LiveCart.AjaxRequest(url, 'settingsIndicator', function(response) { this.displayCategory(response, id); }.bind(this));
	},

	displayCategory: function(response, id)
	{
		this.treeBrowser.hideFeedback(id);

		if (!response.responseText)
		{
			return false;
		}

		$('settingsContent').update(response.responseText);

		var cancel = document.getElementsByClassName('cancel', $('settingsContent'))[0];
		Event.observe(cancel, 'click', this.resetForm.bindAsEventListener(this));
	},

	resetForm: function(e)
	{
		var el = Event.element(e);
		while (el.tagName != 'FORM')
		{
			el = el.parentNode;
		}

		el.reset();
	},

	save: function(form)
	{
		new LiveCart.AjaxRequest(form, null, this.displaySaveConfirmation.bind(this));
	},

	displaySaveConfirmation: function()
	{
		new Backend.SaveConfirmationMessage(document.getElementsByClassName('yellowMessage')[0]);
	}
}

Backend.Settings.Editor = Class.create();
Backend.Settings.Editor.prototype =
{
	handlers:
	{
		'ENABLED_COUNTRIES':
			function()
			{
				var cont = $('setting_ENABLED_COUNTRIES');
				var menu = cont.insertBefore($('handler_ENABLED_COUNTRIES').cloneNode(true), cont.firstChild);

				var select =
					function(e)
					{
						Event.stop(e);

						var state = Event.element(e).hasClassName('countrySelect');

						checkboxes = $('setting_ENABLED_COUNTRIES').getElementsByTagName('input');

						for (k = 0; k < checkboxes.length; k++)
						{
						  	checkboxes[k].checked = state;
						}
					}

				Event.observe(menu.down('.countrySelect'), 'click', select);
				Event.observe(menu.down('.countryDeselect'), 'click', select);
			},

		'ALLOWED_SORT_ORDER':
			function()
			{
				var values = $('SORT_ORDER').getElementsBySelector('option');
				var change =
					function(e)
					{
						var el = Event.element(e);

						if (!el)
						{
							el = e;
						}

						if (el.checked)
						{
							Element.show(el.param);
						}
						else
						{
							// at least one option must always be selected
							if (this.values)
							{
								var isSelected = false;
								for (k = 0; k < this.values.length; k++)
								{
									if ($('ALLOWED_SORT_ORDER[' + this.values[k].value + ']').checked)
									{
										isSelected = true;
										break;
									}
								}

								if (!isSelected)
								{
									el.checked = true;
									return;
								}
							}

							Element.hide(el.param);
						}
					}

				this.values = values;

				for (k = 0; k < values.length; k++)
				{
					var el = $('ALLOWED_SORT_ORDER[' + values[k].value + ']');
					el.param = values[k];

					Event.observe(el, 'change', change.bind(this));
					change(el);
				}
			},

		'EMAIL_METHOD':
			function()
			{
				var change =
					function()
					{
						var display = ($('EMAIL_METHOD').value == 'SMTP');
						[$('setting_SMTP_SERVER'), $('setting_SMTP_PORT'), $('setting_SMTP_USERNAME'), $('setting_SMTP_PASSWORD')].each(function(element) { if (display) { element.show(); } else {element.hide();} });
					}

				change();

				$('SMTP_PASSWORD').type = 'password';
				Event.observe($('EMAIL_METHOD'), 'change', change);
			},

		'THEME':
			function()
			{
				new Backend.ThemePreview($('setting_THEME'), $('THEME'));
			},

		'ENABLE_SITEMAPS':
			function()
			{
				var cont = $('setting_ENABLE_SITEMAPS');
				var menu = cont.insertBefore($('handler_ENABLE_SITEMAPS').cloneNode(true), cont.lastChild);

				var siteMapSubmit =
					function(e)
					{
						Event.stop(e);

						new LiveCart.AjaxUpdater(Event.element(e).href, $('siteMapSubmissionResult'), $('siteMapFeedback'));
					}

				Event.observe($('siteMapPing'), 'click', siteMapSubmit);
			},

		'IMG_P_W_1':
			function()
			{
				// move all sections to one row
				$('settings').addClassName('imageSettings');

				var wCapt = Backend.getTranslation('IMG_P_W_1');
				var hCapt = Backend.getTranslation('IMG_P_H_1');
				var sizeCapt = wCapt + ' x ' + hCapt + ':';
				var qualityCapt = Backend.getTranslation('IMG_P_Q_1')  + ':';

				var prefixes = ['P', 'C', 'M'];
				for (var k = 0; k < prefixes.length; k++)
				{
					var prefix = prefixes[k];
					for (var size = 1; size <= 4; size++)
					{
						var width = $('setting_IMG_' + prefix + '_W_' + size);
						var height = $('setting_IMG_' + prefix + '_H_' + size);
						var quality = $('setting_IMG_' + prefix + '_Q_' + size);

						// move field
						var widthInput = width.down('input');
						var x = document.createElement('span');
						x.innerHTML = ' x ';
						widthInput.parentNode.insertBefore(height.down('input'), widthInput.nextSibling);
						widthInput.parentNode.insertBefore(x, widthInput.nextSibling);

						// set label text
						width.down('label').innerHTML = sizeCapt;
						quality.down('label').innerHTML = qualityCapt;

						height.parentNode.removeChild(height);
					}
				}
			}
	},

	initialize: function(container)
	{
		var settings = container.getElementsBySelector('div.setting');
		for (k = 0; k < settings.length; k++)
		{
			var id = settings[k].id.substr(8);
			if (this.handlers[id])
			{
				this.handlers[id]();
			}
		}
	}
}

Event.observe(window, 'load',
	function()
	{
		window.loadingImage = 'image/loading.gif';
		window.closeButton = 'image/silk/gif/cross.gif';
		initLightbox();
	}
);