/**
 *	@author Integry Systems
 */

Backend.Settings = Class.create();

Backend.Settings.prototype =
{
  	treeBrowser: null,

	urls: new Array(),
	// urls: {},

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
		var match = Backend.getHash().match(/section_(.+?)__/), sectionId = '00-store';
		if(match)
		{
			sectionId = match.pop();
			this.treeBrowser.openItem(sectionId);
			this.treeBrowser.selectItem(sectionId);
		}
		this.activateCategory(sectionId);
		var firstPaymentMethod = this.treeBrowser.getChildItemIdByIndex('05-payment', 0);
		for (k = 1; k <= 6; k++)
		{
			var item = 'payment.OFFLINE' + k;
			this.treeBrowser.moveItem(item, 'item_sibling', firstPaymentMethod);
			this.treeBrowser.setItemText(item, '<span id="tree_payment.OFFLINE' + k + '">' + this.getSetting('OFFLINE_NAME_' + k)) + '</span>';
		}
		this.updatePaymentProcessors();
		this.updateShippingHandlers();

		this.treeBrowser.closeAllItems('05-payment');
		this.treeBrowser.closeAllItems('06-shipping');

		if(match)
		{
			this.treeBrowser.openItem(sectionId);
			this.treeBrowser.selectItem(sectionId);
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
				var subKey = cb.name.match(/\[([-a-zA-Z0-9_]*)\]/)[1];
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

			if (el)
			{
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

		'EMAIL_STATUS_UPDATE':
			function()
			{
				var change = function() {
					var checked = $('EMAIL_STATUS_UPDATE').checked;
					$("setting_EMAIL_STATUS_UPDATE_STATUSES")[checked ? 'hide' : 'show']();
					if(checked)
					{
						$A($("setting_EMAIL_STATUS_UPDATE_STATUSES").getElementsByClassName("checkbox")).each(
							function(element) {
								element.checked = true;
							}
						);
					}
					return change; // !
				};
				Event.observe($('EMAIL_STATUS_UPDATE'), 'change', change());
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

		'ENABLED_FEEDS':
			function()
			{
				var cont = $('setting_ENABLED_FEEDS').down('.multi');
				var tpl = $('handler_ENABLED_FEEDS').down('a');
				var accessKey = $('FEED_KEY').value;
				$H(cont.getElementsByTagName('p')).each(function(feed)
				{
					var link = tpl.cloneNode(true);

					if (feed[1].parentNode)
					{
						var module = feed[1].down('input').name.match(/ENABLED_FEEDS\[([-_a-zA-Z0-9]+)\]/)[1];
						link.href = link.href.replace('module', module);
						link.href = link.href.replace('accessKey', accessKey);
						feed[1].appendChild(link);
					}
				});
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

				var prefixes = ['P', 'C', 'M', 'O'];
				for (var k = 0; k < prefixes.length; k++)
				{
					var prefix = prefixes[k];

					if (prefix != 'O')
					{
						var leg = $('setting_IMG_' + prefix + '_W_1').up('fieldset').up('fieldset').down('legend');
						var menu = document.createElement('div');
						menu.innerHTML = '<a href="#">' + Backend.getTranslation('_resize_images') + '</a>';
						leg.appendChild(menu);

						var a = menu.down('a');
						a.onclick = function(k, a)
						{
							return function(e)
							{
								var prefix = prefixes[k];
								new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.' + {P: 'product', C: 'category', M: 'manufacturer'}[prefix] + 'Image', 'resizeImages', {id: prefix}), a, function(oReq) { this.resizeImages(oReq, a); }.bind(this));
								Event.stop(e);
							}.bind(this);
						}.bind(this)(k, a);
					}

					for (var size = 1; size <= 4; size++)
					{
						var width = $('setting_IMG_' + prefix + '_W_' + size);

						if (!width)
						{
							break;
						}

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

		'UPDATE_COPY_METHOD':
			function()
			{
				// method testing
				var cont = $('setting_UPDATE_COPY_METHOD');
				var menu = cont.appendChild($('handler_UPDATE_COPY_METHOD').cloneNode(true));
				var a = menu.down('a');
				Event.observe(a, 'click', function(e)
				{
					Event.stop(e);
					new LiveCart.AjaxRequest(a.href, a.parentNode.down('.progressIndicator'), function(oR)
					{
					});
				});

				// ftp container toggle
				var field = $('UPDATE_COPY_METHOD');
				var change = function()
				{
					var ftpContainer = $('setting_UPDATE_FTP_SERVER').up('.settings');
					if (field.value == 'UPDATE_FTP')
					{
						ftpContainer.show();
					}
					else
					{
						ftpContainer.hide();
					}
				}

				Event.observe(field, 'change', change);
				change();
			},

		'DEF_COUNTRY':
			function()
			{
				var switcher = new Backend.User.StateSwitcher($('DEF_COUNTRY'), $('DEF_STATE'), document.createElement('input'), Router.createUrl('backend.user', 'states'));
				switcher.updateStates(null,
					function()
					{
						$('DEF_STATE').value = $('DEF_STATE').getAttribute('initialValue');
					});
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

		'UPDATE_REPO_1':
			function()
			{
				var previousContainer = null;
				$A($('setting_UPDATE_REPO_1').up('.settings').getElementsByTagName('label')).each(function(label)
				{
					label.innerHTML = Backend.getTranslation('UPDATE_REPO_1') + ':';

					var settingContainer = label.up('.setting');
					settingContainer.field = settingContainer.down('input.text');
					settingContainer.label = label;

					if (previousContainer)
					{
						settingContainer.previousContainer = previousContainer;
						settingContainer.previousContainer.nextContainer = settingContainer;
					}

					var field = settingContainer.field;
					var change = function(e)
					{
						// check if repo url is unique
						container = $('setting_UPDATE_REPO_1');
						while (container.nextContainer)
						{
							if ((container.field.value == field.value) && (container != settingContainer))
							{
								field.value = '';
							}

							container = container.nextContainer;
						}

						if (!field.value.length)
						{
							settingContainer.hide();

							if (settingContainer.nextContainer && settingContainer.nextContainer.field.value.length)
							{
								var value = settingContainer.nextContainer.field.value;
								settingContainer.field.value = value;
								settingContainer.nextContainer.field.value = '';
								settingContainer.change();
								settingContainer.nextContainer.change();
							}
						}
						else
						{
							settingContainer.show();
							label.innerHTML = Backend.getTranslation('UPDATE_REPO_1') + ':';
							label.removeClassName('newRepo');

							// sort fields, so that entered values are always in the first fields
							var lastContainer = settingContainer;
							while (lastContainer.previousContainer && !lastContainer.previousContainer.field.value.length)
							{
								lastContainer = lastContainer.previousContainer;
							}

							var value = settingContainer.field.value;
							settingContainer.field.value = '';
							lastContainer.field.value = value;

							var className = settingContainer.label.className;
							settingContainer.label.className = '';
							lastContainer.label.className = className;

							if (settingContainer != lastContainer)
							{
								settingContainer.change();
								lastContainer.change();
							}

							if (e)
							{
								new LiveCart.AjaxRequest(Router.createUrl('backend.module', 'repoStatus', {repo: lastContainer.field.value}), lastContainer.label, function(oR)
								{
									label.removeClassName('repoUp');
									label.removeClassName('repoDown');
									label.addClassName(('OK' == oR.responseText) ? 'repoUp' : 'repoDown');

									if ('OK' == oR.responseText)
									{
										new LiveCart.AjaxRequest(Router.createUrl('backend.module', 'repoDescription', {repo: value}), label, function(descrReq)
										{
											var descr = document.createElement('div');
											descr.className = 'repoDescription';
											field.parentNode.appendChild(descr);
											descr.innerHTML = descrReq.responseText;
										});
									}
								});
							}
						}

						lastContainer = $('setting_UPDATE_REPO_20');
						while (lastContainer.previousContainer && !lastContainer.previousContainer.field.value.length)
						{
							lastContainer = lastContainer.previousContainer;
							lastContainer.hide();
						}

						if (lastContainer.label)
						{
							lastContainer.label.innerHTML = Backend.getTranslation('UPDATE_REPO_2') + ':';
							lastContainer.show();
							lastContainer.label.removeClassName('repoUp');
							lastContainer.label.removeClassName('repoDown');
							lastContainer.label.addClassName('newRepo');
						}
					}

					settingContainer.change = change;
					Event.observe(field, 'change', change);
					change(true);

					previousContainer = settingContainer;
				});
			},

		'OFFLINE_NAME_2': function() { this.handlers.OFFLINE_NAME_1(); },
		'OFFLINE_NAME_3': function() { this.handlers.OFFLINE_NAME_1(); },
		'OFFLINE_NAME_4': function() { this.handlers.OFFLINE_NAME_1(); },
		'OFFLINE_NAME_5': function() { this.handlers.OFFLINE_NAME_1(); },
		'OFFLINE_NAME_6': function() { this.handlers.OFFLINE_NAME_1(); }
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
	},

	resizeImages: function(oReq, a)
	{
		Backend.SaveConfirmationMessage.prototype.showMessage(Backend.getTranslation('_image_resize_success'));
	}
}