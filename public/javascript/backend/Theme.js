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
	
	showImportForm: function()
	{
		$('importForm').show();
	},

	hideImportForm: function()
	{
		var form = $('importForm').down('form');
		form.reset();
		ActiveForm.prototype.resetErrorMessages(form)
		$('importForm').hide();
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

	importTheme: function()
	{
		this.showImportForm();
	},

	exportSelected: function()
	{
		var
			id = this.treeBrowser.getSelectedItemId(),
			url = this.urls['export'].replace('_id_', id);

		window.location.href = url;

		// this.treeBrowser.showFeedback(id);
	},

	showControls: function()
	{
		var id = this.treeBrowser.getSelectedItemId();
		if (id)
		{
			$("exportMenu").show();
		}
		else
		{
			$("exportMenu").hide();
		}

		if ('barebone' != id)
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


Backend.ThemeColor = function(theme)
{
	if (!theme)
	{
		theme = 'barebone';
	}

	this.iframe = $('iframe_' + theme);
	this.form = $('colors_' + theme);
	this.styleSheet = this.iframe.contentDocument.styleSheets[0];

	this.form.onsubmit = this.save.bind(this);

	this.initProperties();
}

Backend.ThemeColor.prototype =
{
	initProperties: function()
	{
		$A(this.form.getElementsBySelector('fieldset.entry')).each(function(p)
		{
			var rel = p.getAttribute('rel');
			if (rel.length > 0)
			{
				var prop = rel.split(/\//);
				new Backend.ThemeColorProperty(this, p, prop[0], prop[1], prop[2]);
			}
		}.bind(this));
	},

	getStyleSheet: function()
	{
		return this.styleSheet;
	},

	getIframe: function()
	{
		return this.iframe;
	},

	save: function()
	{
		this.form.elements.namedItem('css').value = CssCustomize.prototype.getStyleSheetText(this.getStyleSheet());
		return true;
	}
}

Backend.ThemeColorProperty = function(manager, element, selector, property, type)
{
	this.manager = manager;
	this.element = element;
	this.selector = selector;
	this.property = property;
	this.type = type;
	this.nodes = {};

	this.selectorVar = (this.selector.match(/\#([-_a-zA-Z0-9]+)-var/) || []).shift();
	if (this.selectorVar)
	{
		var ss = this.manager.getIframe().contentDocument.styleSheets[1];
		for (var k = 0; k < ss.cssRules.length; k++)
		{
			if (ss.cssRules[k].selectorText.indexOf(this.selectorVar) > -1)
			{
				this.selector = ss.cssRules[k].selectorText;
				break;
			}
		}
	}

	this.findUsedNodes();
	this.bindEvents();
	this.displayCurrentValue();

	if ('upload' == this.type)
	{
		this.repeat = new Backend.ThemeColorProperty(manager, this.nodes.repeat, selector, 'background-repeat', '');
		this.position = new Backend.ThemeColorProperty(manager, this.nodes.position, selector, 'background-position', '');
	}
}

Backend.ThemeColorProperty.prototype =
{
	findUsedNodes: function()
	{
		if ('size' == this.type)
		{
			this.nodes.number = this.element.down('input');
			this.nodes.unit = this.element.down('select');
		}
		else if ('border' == this.type)
		{
			this.nodes.number = this.element.down('input');
			this.nodes.style = this.element.down('select');
			this.nodes.color = this.element.down('input', 1);
		}
		else if ('upload' == this.type)
		{
			this.nodes.upload = this.element.down('input.file');
			this.nodes.url = this.element.down('input.text');
			this.nodes.repeat = this.element.down('div.repeat');
			this.nodes.position = this.element.down('div.position');
		}
		else
		{
			this.nodes.input = this.element.down('input') || this.element.down('select');
		}
	},

	bindEvents: function()
	{
		if ('upload' == this.type)
		{
			this.nodes.upload.onchange = function()
			{
				if (this.nodes.upload.value.length > 0)
				{
					this.nodes.url.value = '';
				}
			}.bind(this);

			this.nodes.url.onchange = function()
			{
				if (this.nodes.url.value.length > 0)
				{
					this.nodes.upload.value = '';
				}
			}.bind(this);
		}

		$H(this.nodes).each(function(node)
		{
			Event.observe(node[1], 'change', this.updateCSSValue.bind(this));
		}.bind(this));
	},

	updateCSSValue: function()
	{
		var rule = this.getRule();
		if (!rule)
		{
			this.getStyleSheet().insertRule(this.selector + '{}', 0);
			rule = this.getRule();
		}

		var properties = CssCustomize.prototype.getPropertiesFromText(rule.cssText);
		properties[this.property] = this.getEnteredValue();

		var text = '';
		$H(properties).each(function(prop)
		{
			text += prop[0] + ': ' + prop[1] + '; '
		});

		rule.style.cssText = text;

		if (('upload' == this.type) && properties[this.property])
		{
			this.repeat.updateCSSValue();
			this.position.updateCSSValue();
		}
	},

	displayCurrentValue: function()
	{
		var value = this.getCurrentValueFromCSS();
		if (undefined == value)
		{
			return;
		}

		if ('size' == this.type)
		{
			var num = value.match(/[0-9]+/);
			var unit = value.toLowerCase().match(/[\%a-z]+/);

			this.nodes.number.value = num[0];
			this.nodes.unit.value = unit[0];
		}
		else if ('border' == this.type)
		{
			var num = value.match(/[0-9]+/);
			[foo, style] = value.toLowerCase().split(/ /, 2);

			colorParts = value.toLowerCase().split(/ /);
			colorParts.shift();
			colorParts.shift();
			var color = colorParts.join(' ');

			this.nodes.number.value = num[0];
			this.nodes.style.value = style;
			this.nodes.color.value = this.getHexColor(color);

			this.nodes.color.color.fromString(this.nodes.color.value);
		}
		else if ('upload' == this.type)
		{
			this.nodes.upload.value = '';
			this.nodes.url.value = value;
		}
		else
		{
			this.nodes.input.value = value;

			if ('color' == this.type)
			{
				this.nodes.input.color.fromString(this.getHexColor(this.nodes.input.value));
			}
		}
	},

	getEnteredValue: function()
	{
		if ('size' == this.type)
		{
			var num = this.nodes.number.value;
			var unit = this.nodes.unit.value;

			if ('auto' == unit)
			{
				return unit;
			}
			else
			{
				return num + unit;
			}
		}
		else if ('upload' == this.type)
		{
			if (this.nodes.upload.value.length)
			{
				return "url('" + this.nodes.upload.name + "')";
			}
			else
			{
				if (this.nodes.url.value.length && (this.nodes.url.value.substring(0, 4) != 'url('))
				{
					this.nodes.url.value = 'url(\'' + this.nodes.url.value + '\')';
				}

				return this.nodes.url.value;
			}
		}
		else if ('border' == this.type)
		{
			return this.nodes.number.value + 'px ' + this.nodes.style.value + ' ' + this.getHexColor(this.nodes.color.value);
		}
		else
		{
			return this.nodes.input.value;
		}
	},

	getCurrentValueFromCSS: function()
	{
		var rule = this.getRule();
		if (!rule)
		{
			return;
		}

		return CssCustomize.prototype.getPropertiesFromText(rule.cssText)[this.property];
	},

	getRule: function()
	{
		var ss = this.getStyleSheet();

		for (var k = 0; k < ss.cssRules.length; k++)
		{
			if (this.selector == ss.cssRules[k].selectorText || (this.selectorVar && (ss.cssRules[k].selectorText.indexOf(this.selectorVar) > -1)))
			{
				return ss.cssRules[k];
			}
		}
	},

	getStyleSheet: function()
	{
		return this.manager.getStyleSheet();
	},

	getHexColor: function(value)
	{
		// replace rgb(1,2,3) to #hex
		if (value.match(/rgb\(/))
		{
			var hex = [];
			var rgb = value.match(/rgb\([ ,0-9]+\)/)[0];
			rgb.match(/[0-9]+/g).each(function(val)
			{
				var h = parseInt(val).toString(16);
				hex.push((1 == h.length) ? '0' + h : h);
			});

			value = value.replace(rgb, '#' + hex.join(''));
		}

		return value;
	}
}