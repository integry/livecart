/**
 *	@author Integry Systems
 */

Backend.Settings = Class.create();

Backend.Settings.prototype =
{
  	treeBrowser: null,

  	urls: new Array(),

	initialize: function(categories, settings)
	{
		Backend.Settings.prototype.instance = this;

		this.settings = settings;

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

		this.treeBrowser.hideChildren =
			function(itemId)
			{
				this.getAllSubItems(itemId).split(/,/).each(function(sub)
				{
					this.changeItemVisibility(sub, false);
				}.bind(this));

				this.showItemSign(itemId, false);
				this._globalIdStorageFind(itemId).htmlNode.down('img', 2).src = 'image/backend/dhtmlxtree/leaf.gif';
			}

		this.treeBrowser.showChildren =
			function(itemId)
			{
				this.showItemSign(itemId, true);
			}

		this.treeBrowser.changeItemVisibility =
			function(id, state)
			{
				var item = $('tree_' + id);
				if (item)
				{
					var row = item.up('table').up('tr');
					if (state)
					{
						row.removeClassName('hidden');
						return true;
					}
					else
					{
						row.addClassName('hidden');
					}
				}
			},

		this.insertTreeBranch(categories, 0);
		this.treeBrowser.closeAllItems(0);

		window.settings = this;
	},

	insertTreeBranch: function(treeBranch, rootId)
	{
		for (k in treeBranch)
		{
		  	if('function' != typeof treeBranch[k])
		  	{
				this.treeBrowser.insertNewItem(rootId, k, '<span id="tree_' + k + '">' + treeBranch[k].name + '</span>', null, 'leaf.gif', 'leaf.gif', 'leaf.gif', '', 1);
				this.treeBrowser.showItemSign(k, 1);

				if (treeBranch[k].subs)
				{
					this.insertTreeBranch(treeBranch[k].subs, k);
				}
			}
		}
	},

	init: function()
	{
		this.activateCategory('00-store');

		var firstPaymentMethod = this.treeBrowser.getChildItemIdByIndex('05-payment', 0);
		for (k = 1; k <= 3; k++)
		{
			var item = 'payment.OFFLINE' + k;
			this.treeBrowser.moveItem(item, 'item_sibling', firstPaymentMethod);
			this.treeBrowser.setItemText(item, '<span id="tree_payment.OFFLINE' + k + '">' + this.getSetting('OFFLINE_NAME_' + k)) + '</span>';
		}

		this.updatePaymentProcessors();
		this.updateShippingHandlers();

		this.treeBrowser.closeAllItems('05-payment');
		this.treeBrowser.closeAllItems('06-shipping');
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

		var container = $('settingsContent');
		ActiveForm.prototype.destroyTinyMceFields(container);
		container.update(response.responseText);

		var cancel = document.getElementsByClassName('cancel', container)[0];
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
		var input = form.getElementsByTagName('input');
		var hasFiles = false;
		for (var k = 0; k < input.length; k++)
		{
			if ('file' == input[k].type)
			{
				hasFiles = true;
				break;
			}
		}

		if (hasFiles)
		{
			$('upload').onload = this.completeUpload.bind(this);
			form.submit();
		}
		else
		{
			new LiveCart.AjaxRequest(form, null, this.afterSave.bind(this));
		}

		return false;
	},

	completeUpload: function()
	{
		var dataString = $('upload').contentDocument.body.innerHTML;
		if (dataString.substr(0, 5) == '<pre>')
		{
			dataString = dataString.substr(5, dataString.length);
		}
		if (dataString.substr(-6) == '</pre>')
		{
			dataString = dataString.substr(0, dataString.length - 6);
		}

		this.afterSave({responseData: dataString.evalJSON()});
	},

	afterSave: function(result)
	{
		if (!result.channel)
		{
			Backend.SaveConfirmationMessage.prototype.showMessage(result.responseData.message);
		}

		// update image upload settings
		var images = $('settingsContent').getElementsByClassName('settingImage');
		for (k = 0; k < images.length; k++)
		{
			var setting = images[k].up().down('input').id;
			var value = result.responseData[setting];
			if (value)
			{
				images[k].src = value + '?' + (Math.random() * 100000);
			}
		}
	},

	updateSetting: function(key, subKey, value)
	{
		if (subKey != null)
		{
			if (typeof this.settings[key] != 'object')
			{
				this.settings[key] = {};
			}

			this.settings[key][subKey] = value;
		}
		else
		{
			this.settings[key] = value;
		}
	},

	getSetting: function(key)
	{
		return this.settings[key];
	},

	observeValueChange: function(container, id, handler)
	{
		if (container.getElementsByClassName('multi').length)
		{
			$A(container.getElementsByTagName('input')).each(function(cb)
			{
				var subKey = cb.name.match(/\[([a-zA-Z0-9_]*)\]/)[1];
				cb.onchange = function()
				{
					this.updateSetting(id, subKey, cb.checked ? 1 : 0);
					if (handler)
					{
						handler();
					}
				}.bind(this)
			}.bind(this));
		}
		else if (container.getElementsByClassName('checkbox').length)
		{
			var cb = container.getElementsByTagName('input')[0];
			cb.onchange =
				function()
				{
					this.updateSetting(id, null, cb.checked ? 1 : 0);
					if (handler)
					{
						handler();
					}
				}.bind(this)
		}
		else
		{
			var el = container.getElementsByTagName('input')[0] || container.getElementsByTagName('select')[0] || container.getElementsByTagName('textarea')[0];

			el.onchange =
				function()
				{
					this.updateSetting(id, null, el.value);
					if (handler)
					{
						handler();
					}
				}.bind(this)
		}
	},

	updatePaymentProcessors: function()
	{
		var id = '05-payment';

		this.treeBrowser.hideChildren(id);
		var isVisible = this.treeBrowser.changeItemVisibility('payment.' + this.getSetting('CC_HANDLER'), true);

		['OFFLINE_HANDLERS', 'EXPRESS_HANDLERS', 'PAYMENT_HANDLERS'].each(function(type)
		{
			if (this.setHandlerVisibility('payment', type))
			{
				isVisible = true;
			}
		}.bind(this));

		if (isVisible)
		{
			this.treeBrowser.showChildren(id);
		}
	},

	updateShippingHandlers: function()
	{
		var id = '06-shipping';

		this.treeBrowser.hideChildren(id);

		if (this.setHandlerVisibility('shipping', 'SHIPPING_HANDLERS'))
		{
			this.treeBrowser.showChildren(id);
		}
	},

	setHandlerVisibility: function(prefix, id)
	{
		isVisible = false;

		$H(this.getSetting(id)).each(function(val)
		{
			if (this.treeBrowser.changeItemVisibility(prefix + '.' + val[0], val[1] > 0))
			{
				isVisible = true;
			}
		}.bind(this));

		return isVisible;
	}
}

Backend.Settings.Editor = Class.create();
Backend.Settings.Editor.prototype =
{
	owner: null,

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

		'SOFT_NAME':
			function()
			{
				var cont = $('setting_SOFT_NAME').up('form');
				var menu = cont.insertBefore($('handler_SOFT_NAME').cloneNode(true), cont.firstChild);

				var disablePrivateLabel =
					function(e)
					{
						Event.stop(e);

						var link = Event.element(e);
						new LiveCart.AjaxRequest(link.href, link.parentNode.down('.progressIndicator'), function()
						{
							this.owner.activateCategory('00-store');
							this.owner.treeBrowser.deleteItem('49-private-label', true);
						}.bind(this));
					}.bind(this);

				Event.observe(menu.down('a'), 'click', disablePrivateLabel);
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
			},

		'RATING_SCALE':
			function()
			{
				var input = $('setting_RATING_SCALE').down('input');
				var span = document.createElement('span');
				span.innerHTML = '1 - ';
				input.parentNode.insertBefore(span, input);
			},

		'OFFLINE_HANDLERS':
			function()
			{
				$A($('setting_OFFLINE_HANDLERS').getElementsBySelector('label.checkbox')).each(function(label)
				{
					var key = label.getAttribute('for').match(/\[([a-zA-Z0-9_]*)\]/)[1];
					if (key)
					{
						label.innerHTML = this.owner.getSetting(key.substr(0, key.length - 1) + '_NAME_' + key.substr(-1));
					}
				}.bind(this));
			},

		'OFFLINE_NAME_1':
			function()
			{
				$A($('settings').getElementsByTagName('label')).each(function(label)
				{
					var key = label.getAttribute('for');
					var key = key.substr(0, key.length - 2);
					label.innerHTML = Backend.getTranslation(key) + ':';
				});
			},

		'OFFLINE_NAME_2': function() { this.handlers.OFFLINE_NAME_1(); },
		'OFFLINE_NAME_3': function() { this.handlers.OFFLINE_NAME_1(); }
	},

	valueHandlers:
	{
		'CC_HANDLER': function() {this.owner.updatePaymentProcessors()},
		'EXPRESS_HANDLERS': function() {this.owner.updatePaymentProcessors()},
		'PAYMENT_HANDLERS': function() {this.owner.updatePaymentProcessors()},
		'OFFLINE_HANDLERS': function() {this.owner.updatePaymentProcessors()},
		'SHIPPING_HANDLERS': function() {this.owner.updateShippingHandlers()}
	},

	initialize: function(container, handler)
	{
		this.owner = handler;
		var settings = container.getElementsBySelector('div.setting');
		for (k = 0; k < settings.length; k++)
		{
			var id = settings[k].id.substr(8);
			if (this.handlers[id])
			{
				this.handlers[id].bind(this)();
			}

			this.owner.observeValueChange(settings[k], id, this.valueHandlers[id] ? this.valueHandlers[id].bind(this) : null);
		}

		ActiveForm.prototype.initTinyMceFields(container);
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