/**
 *	@author Integry Systems
 */

var TranslationMenuEvent = Class.create();
TranslationMenuEvent.prototype =
{
	element: false,

	translationHandler: false,

	initialize: function(element, translationHandler)
	{
		this.element = element;
		this.translationHandler = translationHandler;
		this.eventMouseMove = this.move.bindAsEventListener(this);
		Event.observe(this.element, 'mousemove', this.eventMouseMove);
	},

	move: function(e)
	{
		this.translationHandler.showTranslationMenu(this.element, e);
	}
}

Customize = Class.create();
Customize.prototype = {

	actionUrl: false,

	currentElement: false,

	initialValue: null,

	currentId: false,

	initialize: function()
	{

	},

	setActionUrl: function(url)
	{
		this.actionUrl = url;
	},

	initLang: function()
	{
		elements = document.getElementsByClassName('transMode');
		for (k in elements)
		{
		  	new TranslationMenuEvent(elements[k], this);
		}
	},

	showTranslationMenu: function(element, e)
	{
		if (element == this.currentElement)
		{
			return false;
		}

/*
		xPos = Event.pointerX(e) - 5;
		yPos = Event.pointerY(e) - 5;
*/
		var pos = Position.cumulativeOffset(element);

		xPos = pos[0];
		yPos = pos[1] - 23;

		var dialog = $('transDialogMenu');

		// make sure the dialog is not being displayed outside window boundaries
		mh = new PopupMenuHandler(xPos, yPos, 100, 30);
		dialog.style.left = mh.x + 'px';
		dialog.style.top = mh.y + 'px';
		Element.show(dialog);
		this.currentElement = element;
		Event.observe(document, 'click', this.hideTranslationMenu.bindAsEventListener(this), false);
	},

	hideTranslationMenu: function()
	{
		Element.hide($('transDialogMenu'));

		this.currentElement = null;
	},

	translationMenuClick: function(e)
	{
		e.stopPropagation();
		this.showTranslationDialog(this.currentElement, e);
		this.hideTranslationMenu();
		Event.stop(e);
	},

	showTranslationDialog: function(element, e)
	{
		this.initialValue = null;

		id = element.className.split(' ')[1];
		id = id.substr(8, id.length);

		this.currentId = id;

		file = element.className.split(' ')[2];
		file = file.substr(6, file.length);

		url = this.actionUrl;
		url = Backend.Router.setUrlQueryParam(url, 'id', id);
		url = Backend.Router.setUrlQueryParam(url, 'file', file);
//		url = this.actionUrl + '?id=' + id + '&file=' + file;

		dialog = document.getElementById('transDialogBox');

		xPos = Event.pointerX(e);
		yPos = Event.pointerY(e);

		// make sure the dialog is not being displayed outside window boundaries
		mh = new PopupMenuHandler(xPos, yPos, 300, 77);

		dialog.style.left = mh.x + 'px';
		dialog.style.top = mh.y + 'px';
		dialog.style.display = 'block';

		document.getElementById('transDialogContent').style.display = 'none';
		document.getElementById('transDialogIndicator').style.display = 'block';

		new LiveCart.AjaxRequest(url, null, this.displayDialogContent.bind(this));

		this.bfx = this.cancelTransDialog.bind(this);

		Event.observe(document, 'mousedown', this.bfx, false);
	},

	handleTransFieldClick: function(e)
	{
	 	e.stopPropagation();
	},

	displayDialogContent: function(originalRequest)
	{
		window.req = originalRequest;

		$('transDialogIndicator').hide();

		if (originalRequest.getResponseHeader('NeedLogin'))
		{
			$('transDialogContent').update('');
			$('transDialogContent').hide();
			return false;
		}

		$('transDialogContent').update(originalRequest.responseText);
		$('transDialogContent').show();
		Event.observe($('trans'), 'mousedown', this.handleTransFieldClick.bindAsEventListener(this), true);
	},

	saveTranslationDialog: function(form)
	{
		form.elements.namedItem('translation').value = document.getElementById('trans').value;
		this.showTranslationSaveIndicator();
		this.updateDocumentTranslations(form.elements.namedItem('id').value, form.elements.namedItem('translation').value);

		new LiveCart.AjaxUpdater(form, 'translationDialog', 'transSaveIndicator');
		Event.stopObserving(document, 'mousedown', this.bfx, false);
	},

	showTranslationSaveIndicator: function()
	{
		indicator = document.getElementById('transSaveIndicator');
		button = document.getElementById('transDialogSave');
		button.parentNode.replaceChild(indicator, button);
	},

	/**
	 * @todo disable for IE (too slow)
	 **/
	previewTranslations: function(transKey, translation)
	{
		elements = document.getElementsByClassName('__trans_' + transKey);
		for (k = 0; k < elements.length; k++)
	  	{
			if (!this.initialValue)
			{
			  	this.initialValue = elements[k].innerHTML;
			}
			elements[k].innerHTML = translation;
		}
	},

	updateDocumentTranslations: function(transKey, translation)
	{
	  	elements = document.getElementsByClassName('__trans_' + transKey);
		for (k = 0; k < elements.length; k++)
	  	{
			elements[k].innerHTML = translation;
			new Effect.Highlight(elements[k], {startcolor:'#FBFF85', endcolor:'#FFFFFF'})
		}
	},

	cancelTransDialog: function()
	{
	  	if (null != this.initialValue)
	  	{
			this.previewTranslations(this.currentId, this.initialValue);
		}

		if ($('translationDialog'))
		{
			$('translationDialog').hide();
		}

		return false;
	},

	stopTransCancel: function(e)
	{
		Event.stop(e);
	}
}

CssCustomize = function(theme)
{
	this.theme = theme;

	this.initStylesheets();
	this.findUsedNodes();
	this.bindEvents();
}

CssCustomize.prototype =
{
	theme: null,

	initStylesheets: function()
	{
		this.styleSheets = $A(document.styleSheets);
		this.styleSheets.each(function(stylesheet)
		{
			try
			{
				stylesheet.originalRules = $A(stylesheet.cssRules);
			}
			catch (e)
			{
				return;
			}

			$A(stylesheet.cssRules).each(function(rule)
			{
				rule.originalText = rule.cssText;
			});
		});
	},

	findUsedNodes: function()
	{
		this.mainMenu = $('customizeMenu');
		this.msgContainer = $('customizeMsg').down('div');
		this.saveButton = this.mainMenu.down('#cssSave');
		this.newRuleButton = this.mainMenu.down('#cssNewRule');
		this.newRuleSave = this.mainMenu.down('#cssNewRuleSave');
		this.newRuleCancel = this.mainMenu.down('#cssNewRuleCancel');
		this.newRuleForm = this.mainMenu.down('#newRuleMenu');
		this.newRuleName = this.mainMenu.down('#cssNewRuleName');
		this.newRuleText = this.mainMenu.down('#cssNewRuleText');
	},

	bindEvents: function()
	{
		this.saveButton.onclick = this.saveChanges.bind(this);
		this.newRuleButton.onclick = function() { this.newRuleForm.show(); }.bind(this);
		this.newRuleCancel.onclick = function(e) { Event.stop(e); this.newRuleForm.hide(); }.bind(this);
		this.newRuleSave.onclick = this.addRule.bind(this);
	},

	addRule: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.newRuleForm.down('form'));

		var val = [[this.newRuleName, cust.errSelectorMsg], [this.newRuleText, cust.errTextMsg]];
		for (var k = 0; k < val.length; k++)
		{
			if (!IsNotEmptyCheck(val[k][0]))
			{
				ActiveForm.prototype.setErrorMessage(val[k][0], val[k][1], true);
				return;
			}
		};

		var sheet = this.getCurrentStyleSheet(document.styleSheets[1]);
		sheet.insertRule(this.newRuleName.value + '{' + this.newRuleText.value + '}', 0);

		this.newRuleForm.hide();
		this.newRuleName.value = '';
		this.newRuleText.value = '';

		this.showMessage(this.ruleAddedMsg);
	},

	saveChanges: function()
	{
		var changes = this.getChangedRules();
		var propertyChanges = this.getPropertyChanges(changes);

		var deleted = {};
		changes.deleted.each(function(rule)
		{
			var file = rule.parentStyleSheet.href;
			deleted[file] = deleted[file] || [];
			deleted[file].push(rule.selectorText);
		});

		var result = {deletedRules: deleted, deletedProperties: propertyChanges.deleted, theme: this.theme, css: this.getCustomCss(changes, propertyChanges)};

		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.customize', 'saveCss'), $('cssSaveIndicator'), this.saveComplete.bind(this), {parameters: 'result=' + escape(Object.toJSON(result))});
	},

	saveComplete: function(originalRequest)
	{
		this.showMessage(this.savedMsg);
	},

	getCustomCss: function(changes, propertyChanges)
	{
		var sheet = this.getCustomStyleSheet();

		$H(propertyChanges.changed).each(function(rule)
		{
			var selector = rule[0];
			var cssRule = sheet.getRule(selector, true) || sheet.insertRule(selector + '{}', sheet.cssRules.length);
			if (!cssRule)
			{
				cssRule = sheet.getRule(selector);
			}
			$H(rule[1]).each(function(pair)
			{
				cssRule.setProperty(pair[0], pair[1], 'getcustom');
			});
		});

		changes.added.each(function(rule)
		{
			sheet.createRule(rule);
		});

		return this.getStyleSheetText(sheet);
	},

	getStyleSheetText: function(sheet)
	{
		var rules = [];
		$A(sheet.cssRules).each(function(rule)
		{
			rules.push(rule.cssText.replace(/\{/, "\n\{\n").replace(/\}/, "\n\}").replace(/;/g, ";\n").replace(/\n /g, "\n\t").replace(/[\s]{2,}\n/g, "\n").replace(/[\n]{2,}/g, "\n").replace(/\s$/, ""));
		});

		return rules.join("\n\n");
	},

	getCustomStyleSheet: function()
	{
		var sheet = this.createStyleSheet(this.theme + '.css');
		var current = this.getCurrentStyleSheet(this.theme, true);

		// copy existing custom stylesheet
		if (current)
		{
			$A(current.cssRules).each(function(rule)
			{
				sheet.createRule(rule);
			});
		}

		return sheet;
	},

	createStyleSheet: function(name)
	{
		return new MockedStyleSheet();
	},

	getPropertyChanges: function(changes)
	{
		var deletedProps = {};
		var changedProps = {};
		changes.changed.each(function(rule)
		{
			var currentProps = this.getPropertiesFromText(rule.cssText);
			var originalProps = this.getPropertiesFromText(rule.originalRule.originalText);
			var file = rule.parentStyleSheet.href;
			var selector = rule.selectorText;

			$H(originalProps).each(function(prop)
			{
				if (!currentProps[prop[0]])
				{
					deletedProps[file] = deletedProps[file] || {};
					deletedProps[file][selector] = deletedProps[file][selector] || {};
					deletedProps[file][selector][prop[0]] = prop[1];
				}
			});

			$H(currentProps).each(function(prop)
			{
				if (originalProps[prop[0]] != prop[1])
				{
					changedProps[selector] = changedProps[selector] || {};
					changedProps[selector][prop[0]] = prop[1];
				}
			});
		}.bind(this));

		return {changed: changedProps, deleted: deletedProps};
	},

	getPropertiesFromText: function(cssText)
	{
		var props = {};
		var ruleBody = cssText.match(/\{(.*)\}/);
		if (!ruleBody)
		{
			return props;
		}

		ruleBody[1].split(/;/).each(function(pair)
		{
			var spl = pair.replace(/^\s+|\s+$/g, "").split(/: /);
			if (spl[1])
			{
				props[spl[0]] = spl[1];
			}
		});

		return props;
	},

	getChangedRules: function()
	{
		var newRules = [];
		var deletedRules = [];
		var changedRules = [];

		this.styleSheets.each(function(stylesheet)
		{
			var currentSheet = this.getCurrentStyleSheet(stylesheet);

			$A(stylesheet.originalRules).each(function(rule)
			{
				var currentRule = currentSheet.getRule(rule.selectorText);

				if (currentRule && currentRule.parentStyleSheet.disabled)
				{
					currentRule = null;
				}

				if (!currentRule)
				{
					deletedRules.push(rule);
				}
				else
				{
					currentRule.originalRule = rule;
					if (rule.originalText != currentRule.cssText)
					{
						changedRules.push(currentRule);
					}
				}
			});

			try
			{
				$A(currentSheet.cssRules).each(function(rule)
				{
					if (!rule.originalRule && !rule.originalText)
					{
						newRules.push(rule);
					}
				});
			}
			catch (e)
			{
				return;
			}

		}.bind(this));

		return {changed: changedRules, added: newRules, deleted: deletedRules};
	},

	getCurrentStyleSheet: function(stylesheet, isTheme)
	{
		if (stylesheet.length == 0)
		{
			stylesheet = 'barebone';
		}

		for (var k = 0; k < document.styleSheets.length; k++)
		{
			if (document.styleSheets[k].href)
			{
				if (((!isTheme && (document.styleSheets[k].href == stylesheet.href)) || (isTheme && document.styleSheets[k].href.match(new RegExp("css\/" + stylesheet  + "\.css")))))
				{
					var sheet = document.styleSheets[k];

					// FireBug CSS editor compatibility
					while (sheet.editStyleSheet)
					{
						sheet = sheet.editStyleSheet.sheet;
					}

					return sheet;
				}
			}
		}
	},

	showMessage: function(msg, noHide)
	{
		this.msgContainer.innerHTML = msg;
		this.msgContainer.show();

		if (!noHide)
		{
			window.setTimeout(function() { this.hideMessage(msg); }.bind(this), 5000);
		}
	},

	hideMessage: function(msg)
	{
		if (this.msgContainer.innerHTML == msg)
		{
			this.msgContainer.innerHTML = '';
			this.msgContainer.hide();
		}
	}
}

MockedStyleSheet = function()
{
	this.cssRules = [];
}

MockedStyleSheet.prototype =
{
	getRule: function(selector)
	{
		selector = selector.replace(/^\s+|\s+$/g, "");
		for (var k = 0; k < this.cssRules.length; k++)
		{
			if (selector == this.cssRules[k].selectorText)
			{
				return this.cssRules[k];
			}
		}
	},

	insertRule: function(cssText)
	{
		var selector = cssText.substr(0, cssText.length - 2);
		var rule = new MockedCSSRule(selector);
		this.cssRules.push(rule);
		return rule;
	},

	createRule: function(cssRule)
	{
		var rule = this.insertRule(cssRule.selectorText + '{}');
		$H(CssCustomize.prototype.getPropertiesFromText(cssRule.cssText)).each(function(prop)
		{
			rule.setProperty(prop[0], prop[1], 'create');
		});

		return rule;
	}
}

MockedCSSRule = function(selector)
{
	this.selectorText = selector.replace(/^\s+|\s+$/g, "");
	this.properties = {};
	this.cssText = selector + '{}';

	this.style = this;
}

MockedCSSRule.prototype =
{
	setProperty: function(name, value)
	{
		if (name.match(/-moz-background/))
		{
			return;
		}

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

		this.properties[name] = value;

		this.cssText = this.getCssText();
	},

	getCssText: function()
	{
		var text = this.selectorText + ' { ';
		$H(this.properties).each(function(prop)
		{
			text += prop[0] + ': ' + prop[1] + '; '
		});

		text += ' } ';

		return text;
	}
}

StyleSheet.prototype.getRule = function(selector, debug)
{
	for (var k = 0; k < this.cssRules.length; k++)
	{
		if (selector == this.cssRules[k].selectorText)
		{
			return this.cssRules[k];
		}
	}
}

CSSStyleSheet.prototype.getRule = StyleSheet.prototype.getRule;

Customize.ThemesMenu = Class.create();
Customize.ThemesMenu.prototype = {
	dropdown : null,
	initialize : function(dropdown)
	{
		this.dropdown = $(dropdown);
		this.form = this.dropdown.up("form");
		Event.observe(this.dropdown, "change", this.changeTheme.bind(this))
	},

	changeTheme: function()
	{
		new LiveCart.AjaxRequest(this.form, null, function(resp) {
			if (resp.responseData)
			{
				if (resp.responseData.status == "success")
				{
					window.location.reload(true);
				}
			}
		}.bind(this));
	}
}