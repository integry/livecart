/**
 *	@author Integry Systems
 */

Backend.Product =
{
	productTabCopies: new Array(),

	formTabCopies: new Array(),

	categoryPaths: {},

	showAddForm: function(categoryID, caller)
	{
		var container = $('addProductContainer');

		// product form has already been downloaded
		if (this.formTabCopies[categoryID])
		{
			container.update('');
			container.appendChild(this.formTabCopies[categoryID]);
			this.initAddForm(categoryID);
		}

		// retrieve product form
		else
		{
			var url = Backend.Category.links.addProduct.replace('_id_', categoryID);
			new LiveCart.AjaxUpdater(url, container, caller.up('.menu').down('.progressIndicator'));
		}

	},

	hideAddForm: function()
	{
		if ($('addProductContainer'))
		{
			Element.hide($('addProductContainer'));
		}

		if ($('categoryTabs'))
		{
			Element.show($('categoryTabs'));
		}
	},

	cancelAddProduct: function(categoryID, noHide)
	{
		container = $('addProductContainer');

		if (!noHide)
		{
			Element.hide(container);
			Element.show($('categoryTabs'));
		}

		ActiveForm.prototype.destroyTinyMceFields(container);
		this.formTabCopies[categoryID] = container.down('.productForm');
	},

	resetAddForm: function(form)
	{
		ActiveForm.prototype.resetTinyMceFields(form);
	},

	initAddForm: function(categoryID)
	{
		container = $('addProductContainer');

		Element.hide($('categoryTabs'));
		Element.show(container);

		if (window.tinyMCE)
		{
			tinyMCE.idCounter = 0;
		}

		ActiveForm.prototype.initTinyMceFields(container);
		this.toggleSkuField(container.down('form').elements.namedItem('autosku'));

		this.initSpecFieldControls(0);
		this.initInventoryControls(container.down('.inventory'));

		// init type selector logic
		var typeSel = container.down('select.productType');
		typeSel.onchange =
			function(e)
			{
				var el = e ? Event.element(e) : this;
				var cont = el.up('div.productForm');
				if (1 == el.value)
				{
					cont.addClassName('intangible');
				}
				else
				{
					cont.removeClassName('intangible');
				}
			}

		this.reInitAddForm();

		ActiveForm.prototype.resetErrorMessages(container.down('form'));

	},

	reInitAddForm: function()
	{
		container = $('addProductContainer');
		var typeSel = container.down('select.productType');
		typeSel.onchange();

		// focus Product Name field
		container.down('form').elements.namedItem('name').focus();

		// clear product image container
		$A(container.getElementsByClassName("thumbsContainer")).each(function(container) {
			container.innerHTML = "";
		});
	},

	initSpecFieldControls: function(categoryID)
	{
		var container = (0 == categoryID) ? $('addProductContainer') : $('tabProductsContent_' + categoryID);
		new Backend.Eav(container);
	},

	initInventoryControls: function(container)
	{
	  	var stockCountContainer = container.down('div.stockCount');
	  	var stockCountField = stockCountContainer.down('input');
	  	var unlimitedCb = container.down('input.isUnlimitedStock');

	  	var onchange = function()
	  	{
			if (unlimitedCb.checked)
			{
				stockCountField._backedUpValue = stockCountField.value;
				stockCountField.value = '1';
				$(stockCountContainer).hide();
			}
			else
			{
				if (stockCountField._backedUpValue)
				{
					stockCountField.value = stockCountField._backedUpValue;
				}

				$(stockCountContainer).show();
			}
		}

		onchange();
		unlimitedCb.onchange = onchange;
	},

	toggleSkuField: function(checkbox)
	{
	  	var skuField = checkbox.form.elements.namedItem('sku');
	  	skuField.disabled = checkbox.checked;
	  	if (checkbox.checked)
	  	{
			skuField._backedUpValue = skuField.value;
			skuField.value = '';
		}
		else
		{
		  	if(skuField._backedUpValue) skuField.value = skuField._backedUpValue;
			skuField.focus();
		}
	},

	saveForm: function(form)
	{
		var saveHandler = new Backend.Product.saveHandler(form);
		new LiveCart.AjaxRequest(form, null, saveHandler.saveComplete.bind(saveHandler));
	},

	openProduct: function(id, e, onComplete)
	{
		if ($('productIndicator_' + id))
		{
			Element.show($('productIndicator_' + id));
		}
		else if (e)
		{
			var indicator = Event.element(e).parentNode.down('.progressIndicator');
			if (indicator)
			{
				indicator.show();

				// ugly hack
				setTimeout(function() { indicator.hide() }, 5000);
			}
		}

		if (window.opener)
		{
			var downloadable = parseInt(e.target.up('tr').down(".cell_hiddenType").innerHTML) == 1;

			window.opener.selectProductPopup.getSelectedObject(id, downloadable);
		}
		else
		{
			Backend.Product.Editor.prototype.setCurrentProductId(id);

			var tabControl = TabControl.prototype.getInstance('productManagerContainer', Backend.Product.Editor.prototype.craftProductUrl, Backend.Product.Editor.prototype.craftProductId, {
				afterClick: function()
				{
					if (Backend.SelectPopup.prototype.popup && Backend.SelectPopup.prototype.popup.opener)
					{
						Backend.SelectPopup.prototype.popup.opener.focus();
						Backend.SelectPopup.prototype.popup.close();
					}
				}
			});

			tabControl.activateTab(null, function(response)
			{
				if(onComplete)
				{
					onComplete(response);
				}

				Backend.ajaxNav.add("#product_" + id);
			}.bind(this));

			if(Backend.Product.Editor.prototype.hasInstance(id))
			{
				Backend.Product.Editor.prototype.getInstance(id);
			}
		}

		if (e)
		{
			Event.stop(e);
		}
	 },

	setPath: function(categoryID, path)
	{
		this.categoryPaths[categoryID] = path;
	},

	resetEditors: function()
	{
		Backend.Product.productTabCopies = new Array();
		Backend.Product.formTabCopies = new Array();
		Backend.Product.Editor.prototype.__instances__ = {};
		Backend.Product.Editor.prototype.__currentId__ = null;

		$('productManagerContainer').down('.sectionContainer').innerHTML = '';

		TabControl.prototype.__instances__ = {};
	},

	massActionChanged: function(element)
	{
		if ('move' == element.value || 'copy' == element.value || 'addCat' == element.value)
		{
			var moveElement = element.up('form').down('.move');
			new Backend.Category.PopupSelector(
				function(categoryID, pathAsText, path)
				{
					var conf = 'move' == element.value ? Backend.Category.messages._confirm_move : '';
					if (conf && !confirm(conf + "\n\n" + pathAsText))
					{
						return false;
					}

					moveElement.down('input').value = categoryID;

					moveElement.up('form').down('input.submit').click();

					var select = moveElement.up('form').down('.select');
					select.value = 'enable_isEnabled';

					return true;
				},
				function()
				{
					var select = moveElement.up('form').down('.select');
					select.value = 'enable_isEnabled';
					return true;
				}
			);
		}
	},

	reloadGrid: function(categoryID)
	{
		var table = $('products_' + categoryID + '_header');

		if (!table && Backend.Product.productTabCopies[categoryID])
		{
			table = Backend.Product.productTabCopies[categoryID].getElementsByTagName('table')[0];
		}

		if (table)
		{
			table.gridInstance.reloadGrid();
		}
	},

	onQuickEditSubmit: function(obj)
	{
		var form;
		form = $(obj).up("form");
		if(validateForm(form))
		{
			new LiveCart.AjaxRequest(form, null, function(transport) {
				var response = eval( "("+transport.responseText + ")");
				if(response.status == "saved")
				{
					this.instance._getGridInstaceFromControl(this.obj).updateQuickEditGrid(transport.responseText);
					this.instance.onQuickEditCancel(this.obj);
				}
				else
				{
					ActiveForm.prototype.setErrorMessages(this.obj.up("form"), response.errors)
				}
			}.bind({instance: this, obj:obj}));
		}
		return false;
	},

	onQuickEditCancel: function(obj)
	{
		var gridInstance = this._getGridInstaceFromControl(obj);
		gridInstance.onQuickEditCancel();
		return false;
	},

	_getGridInstaceFromControl: function(control)
	{
		try {
			return $(control).up("div", 2).down("table").gridInstance;
		} catch(e) {
			return null;
		}
	},

	showQuickEditAddImageForm: function(control, productID, formUrl)
	{
		control = $(control);
		this._menuVisibility(control, ['hide', 'show']);
		// todo: do we have container?
		// if not- request and create
		new LiveCart.AjaxRequest(
			formUrl,
			null,
			function(transport)
			{
				var container = $("productImageUploadForm_"+this.productID);
				container.innerHTML = transport.responseText;
				container.style.position="absolute";
				container.show();
			}.bind({productID:productID})
		);
		return false;
	},

	hideQuickEditAddImageForm: function(control, productID)
	{
		control = $(control);
		this._menuVisibility(control, ['show', 'hide']);
		var container = $("productImageUploadForm_"+productID);
		container.hide();
		return false;
	},

	_menuVisibility: function(node, visibility)
	{
		menuNode=node.up("ul");
		for(i=0; i<visibility.length; i++)
		{
			$(menuNode.down("li",i))[visibility[i]]();
		}
	}
}

Backend.Product.saveHandler = Class.create();
Backend.Product.saveHandler.prototype =
{
  	initialize: function(form)
  	{
		this.form = form;
	},

	saveComplete: function(originalRequest)
	{
	  	ActiveForm.prototype.resetErrorMessages(this.form);
		var response = eval('(' + originalRequest.responseText + ")");

		if (response.errors)
		{
			ActiveForm.prototype.setErrorMessages(this.form, response.errors);
		}
		else
		{
			var categoryID = this.form.elements.namedItem('categoryID').value;

			if (response.specFieldHtml)
			{
				var specFieldContainer = this.form.down('div.specFieldContainer');
				if (specFieldContainer)
				{
					ActiveForm.prototype.destroyTinyMceFields(container);
					specFieldContainer.innerHTML = response.specFieldHtml;
					Backend.Product.initSpecFieldControls(categoryID);
					response.specFieldHtml.evalScripts();
				}
			}

			// reload product grids
			var path = Backend.Product.categoryPaths[categoryID]
			if (path)
			{
				for (var k = 0; k <= path.length; k++)
				{
					var category = path[k] ? path[k].ID : 1;
					Backend.Product.reloadGrid(category);
				}
			}

			// reset form and add more products
			if (this.form.elements.namedItem('afterAdding').checked)
			{
				try { footerToolbar.invalidateLastViewed(); } catch(e) {}
				this.form.reset();
				document.getElementsByClassName('product_sku', this.form)[0].disabled = false;
				Backend.Product.reInitAddForm();
			}

			// continue to edit the newly added product
			else
			{
				Element.show($('loadingProduct'));
				Backend.Product.openProduct(response.id,
											null,
											function()
											{
												Element.hide($('loadingProduct'));
												Backend.Product.cancelAddProduct(categoryID);
												this.form.reset();
											}.bind(this)
											);
  			}
 		}
	}
}

Backend.Product.Editor = Class.create();
Backend.Product.Editor.prototype =
{
	__currentId__: null,
	__instances__: {},

	initialize: function(id, path)
	{
		try { footerToolbar.invalidateLastViewed(); } catch(e) {}
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
		this.nodes.parent = $("tabProductBasic_" + this.id + "Content");
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
		Backend.Product.Editor.prototype.setCurrentProductId(this.id);

		if ($('productIndicator_' + this.id))
		{
			Element.hide($('productIndicator_' + this.id));
		}

		this.showProductForm();
		this.tabControl = TabControl.prototype.getInstance("productManagerContainer", false);

		this.setPath();

		this.addTinyMce();

		if(tabs)
		{
			this.tabControl.setAllCounters(tabs, this.id);
		}
		else
		{
			this.setTabCounters()
		}

		this.initSpecFieldControls();

		Backend.Product.initInventoryControls(this.nodes.form.down('.inventory'));

		var typeSel = this.nodes.form.elements.namedItem("type");
		typeSel.onchange = this.changeType;
		typeSel.onchange();
	},

	setPath: function() {
		Backend.Breadcrumb.display(
			this.path,
			this.nodes.form.elements.namedItem("name").value
		);
	},

	changeType: function()
	{
		var
			bundle = $('tabProductBundle'),
			recurring = $('tabRecurring');

		if (2 == this.value)
		{
			bundle.show();
			bundle.removeClassName('hidden');
			recurring.hide();
		}
		else if (3 == this.value)
		{
			recurring.show();
			recurring.removeClassName('hidden');
			bundle.hide();
		}
		else
		{
			bundle.hide();
			recurring.hide();
		}
	},

	initSpecFieldControls: function()
	{
		new Backend.Eav($('tabProductsContent_' + this.id));
	},

	setTabCounters: function()
	{
		if(!this.tabControl.restoreAllCounters(this.id))
		{
			new LiveCart.AjaxRequest(
				Backend.Product.Editor.prototype.links.countTabsItems + "/" + this.id,
				false,
				function(reply)
				{
					var counters = eval("(" + reply.responseText + ")");
					this.tabControl.setAllCounters(counters, this.id);
				}.bind(this)
			);
		}
	},

	craftProductUrl: function(url)
	{
		return url.replace(/_categoryID_/, Backend.Category.treeBrowser.getSelectedItemId()).replace(/_id_/, Backend.Product.Editor.prototype.getCurrentProductId());
	},

	craftProductId: function(tabId)
	{
		return tabId + '_' +  Backend.Product.Editor.prototype.getCurrentProductId() + 'Content'
	},

	getCurrentProductId: function()
	{
		return Backend.Product.Editor.prototype.__currentId__;
	},

	setCurrentProductId: function(id)
	{
		Backend.Product.Editor.prototype.__currentId__ = id;
	},

	getInstance: function(id, doInit, path, tabs)
	{
		if(!Backend.Product.Editor.prototype.__instances__[id])
		{
			Backend.Product.Editor.prototype.__instances__[id] = new Backend.Product.Editor(id, path);
		}

		if(doInit !== false)
		{
			Backend.Product.Editor.prototype.__instances__[id].__init__(tabs);
		}

		return Backend.Product.Editor.prototype.__instances__[id];
	},

	hasInstance: function(id)
	{
		return this.__instances__[id] ? true : false;
	},

	showProductForm: function(args)
	{
		this.hideCategoriesContainer();
	},

	cancelForm: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form);
		ActiveForm.prototype.resetTinyMceFields(this.nodes.form);
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
			try { footerToolbar.invalidateLastViewed(); } catch(e) {}
			Form.State.backup(this.nodes.form);
			if (response.specFieldHtml)
			{
				var specFieldContainer = this.nodes.form.down('div.specFieldContainer');
				if (specFieldContainer)
				{
					ActiveForm.prototype.destroyTinyMceFields(specFieldContainer);
					specFieldContainer.innerHTML = response.specFieldHtml;
					this.initSpecFieldControls();
					response.specFieldHtml.evalScripts();
				}
			}

			for (var k = 0; k < this.path.length; k++)
			{
				var category = this.path[k] ? this.path[k].ID : 1;
				Backend.Product.reloadGrid(category);
			}

			this.resetPricingTab();
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	},

	resetPricingTab: function()
	{
		this.tabControl.resetContent($('tabProductDiscounts'));
	},

	hideCategoriesContainer: function(args)
	{
		Element.hide($("catgegoryContainer"));
		Element.hide($("managerContainer"));
		Element.show($("productManagerContainer"));
		$('productManagerContainer').removeClassName('treeManagerContainer');
	},

	showCategoriesContainer: function(args)
	{
		var container = $("catgegoryContainer");
		if (!container)
		{
			return;
		}

		Element.show(container);

		if($("productManagerContainer")) Element.hide($("productManagerContainer"));
		if($("managerContainer")) Element.show($("managerContainer"));

		if (!Backend.Category.treeBrowser.getSelectedItemId())
		{
			Backend.Category.treeBrowser.selectItem(1, false);
			Backend.Category.activateCategory(1);
		}

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
	},

	goToProductPage: function()
	{
		var node = $('productPage');
		if (!node.urlTemplate)
		{
			node.urlTemplate = node.href;
		}

		node.href = node.urlTemplate.replace('_id_', Backend.Product.Editor.prototype.getCurrentProductId());
	}
}

Backend.Product.Prices = Class.create();
Backend.Product.Prices.prototype =
{
	__instances__: {},

	initialize: function(parent, product)
	{
		this.product = product;

		this.__nodes__($(parent));
		this.__bind__();

		$A(this.nodes.form.down('fieldset.pricing').getElementsByClassName('price')).each(function(price)
		{
			var listPrice = price.form.elements.namedItem('listPrice_' + price.name.substr(6));

			price.onchange = function()
			{
				listPrice.disabled = this.value.length == 0;
			}

			price.onkeyup = price.onchange;

			price.onchange();
		});

		Backend.Product.initInventoryControls(this.nodes.form.down('.inventory'));

		Form.State.backup(this.nodes.form);
	},

	getInstance: function(parent, product)
	{
		var parentNode = $(parent);
		if(!Backend.Product.Prices.prototype.__instances__[parentNode.id])
		{
			Backend.Product.Prices.prototype.__instances__[parentNode.id] = new Backend.Product.Prices(parentNode.id, product);
		}

		Backend.Product.Prices.prototype.__instances__[parentNode.id].__init__();
		return Backend.Product.Prices.prototype.__instances__[parentNode.id];
	},

	__nodes__: function(parent)
	{
		this.nodes = {};
		this.nodes.parent = parent;
		this.nodes.form = parent;

		this.nodes.submit = this.nodes.parent.down("input.submit");
		this.nodes.cancel = this.nodes.parent.down("a.cancel");
	},

	__bind__: function(args)
	{
		var self = this;
		Event.observe(this.nodes.cancel, "click", function(e) {
			Event.stop(e);
			self.resetForm();
		});
	},

	__init__: function(args)
	{
	},

	submitForm: function()
	{
		new LiveCart.AjaxRequest(this.nodes.form, null, this.saveComplete.bind(this));
	},

	resetForm: function(response)
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.State.restore(this.nodes.form);
	},

	saveComplete: function(responseJSON)
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);

		var responseObject = eval("(" + responseJSON.responseText + ")");

		this.afterSubmitForm(responseObject);
	},

	afterSubmitForm: function(response)
	{
		if('success' == response.status)
		{
			var self = this;
			$H(response.prices).each(function(price) {
				self.nodes.form.elements.namedItem(price.key).value = price.value;
			});

			Form.State.backup(this.nodes.form);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	}
}

Backend.Product.QuantityPrice = function(container, rules)
{
	this.rules = rules;
	this.container = container;
	this.hiddenValue = container.parentNode.down('.hiddenValue');
	this.headRow = container.down('thead').down('tr');

	var hasSerialized = false;

	if (this.rules && this.rules.serializedRules)
	{
		$H(this.rules.serializedRules).each(function(s) {hasSerialized = true;});
	}

	if (hasSerialized)
	{
		$H(this.rules.serializedRules).each(function(pair, index)
		{
			var quant = pair[0];
			var prices = pair[1];
			var col = this.createColumn();
			this.headRow.lastChild.down('input').value = quant;

			if (prices instanceof Array)
			{
				var obj = {};
				for (k = 0; k < prices.length; k++)
				{
					obj[k] = prices[k];
				}
				prices = obj;
			}

			$H(prices).each(function(pair)
			{
				var group = pair[0];
				var price = pair[1];
				var row = this.getGroupRow(group);
				row.getElementsByTagName('input')[index + 1].value = price;
			}.bind(this));
		}.bind(this));

		this.createRow();
		this.createColumn();
		this.deleteColumn(1);
		this.deleteRow(this.container.down('select'));

		$A(this.container.getElementsByTagName('select')).each(this.changeGroup.bind(this));

		this.container.parentNode.show();
	}
	else
	{
		this.container.parentNode.hide();
		this.menuLink = this.container.up('fieldset').down('a.menu');
		this.menuLink.onclick = this.showForm.bind(this);
		this.menuLink.show();
	}

	this.initCells(this.container);
}

Backend.Product.QuantityPrice.prototype =
{
	showForm: function(e)
	{
		Event.stop(e);
		this.menuLink.hide();
		this.container.parentNode.show();
	},

	initCells: function(container)
	{
		$A(container.getElementsByClassName('quantity')).each(function(field) { field.onchange = this.changeQuantity.bindAsEventListener(this); field.onkeyup = function(){NumericFilter(field);}}.bind(this));
		$A(container.getElementsByTagName('select')).each(function(field) { field.onchange = this.changeGroup.bindAsEventListener(this);}.bind(this));
		$A(container.getElementsByClassName('qprice')).each(function(field) { field.onchange = this.changePrice.bindAsEventListener(this); field.onkeyup = function(){NumericFilter(field);}}.bind(this));
	},

	getGroupRow: function(groupID)
	{
		var row = null;
		$A(this.container.getElementsByTagName('select')).each(function(field)
		{
			if (field.value == groupID)
			{
				row = field.up('tr');
				return;
			}
		});

		if (!row)
		{
			row = this.createRow();
			row.down('select').value = groupID;
		}

		return row;
	},

	changeQuantity: function(field)
	{
		if (field instanceof Event)
		{
			field = Event.element(field);
		}

		// last column
		if (this.getColumnNumber(field) == this.getColumnCount() -1)
		{
			if (field.value != '')
			{
				this.createColumn();
			}
		}

		if (field.value == '')
		{
			this.deleteColumn(this.getColumnNumber(field));
		}

		this.indexForm();
	},

	changePrice: function()
	{
		this.indexForm();
	},

	changeGroup: function(field)
	{
		if (field instanceof Event)
		{
			field = Event.element(field);
		}

		if (field.value == '')
		{
			if (this.getRowNumber(field) != this.getRowCount() - 1)
			{
				this.deleteRow(field);
			}
		}
		else
		{
			// last column
			if (this.getRowNumber(field) == this.getRowCount() - 1)
			{
				this.createRow();
			}
		}

		var selectedGroups = {};
		var selects = this.container.getElementsByTagName('select');
		$A(selects).each(function(sel)
		{
			if (0 == sel.value.length)
			{
				return;
			}

			selectedGroups[sel.value] = sel;
		});

		$A(selects).each(function(sel)
		{
			var opts = sel.getElementsByTagName('option');
			$A(opts).each(function(opt)
			{
				if (selectedGroups[opt.value] && (selectedGroups[opt.value] != sel))
				{
					opt.hide();
				}
				else
				{
					opt.show();
				}
			});
		}.bind(this));

		this.indexForm();
	},

	getColumnNumber: function(field)
	{
		return this.getNodeIndex(field.up('td'));
	},

	getRowNumber: function(field)
	{
		return this.getNodeIndex(field.up('tr')) + 1;
	},

	getColumnCount: function()
	{
		return this.container.down('tr').getElementsByTagName('td').length;
	},

	getRowCount: function()
	{
		return this.container.getElementsByTagName('tr').length;
	},

	deleteColumn: function(columnID)
	{
		var rows = this.container.getElementsByTagName('tr');
		for (var k = 0; k < rows.length; k++)
		{
			var cell = rows[k].getElementsByTagName('td')[columnID];
			cell.parentNode.removeChild(cell);
		}
	},

	deleteRow: function(field)
	{
		var row = field.up('tr');
		row.parentNode.removeChild(row);
	},

	createColumn: function()
	{
		var rows = this.container.getElementsByTagName('tr');
		for (var k = 0; k < rows.length; k++)
		{
			var cell = rows[k].lastChild.cloneNode(true);
			rows[k].appendChild(cell);
			cell.down('input').value = '';
			this.initCells(cell);
		}
	},

	createRow: function()
	{
		var row = $A(this.container.getElementsByTagName('tr')).pop();
		var cloned = row.cloneNode(true);
		this.container.down('tbody').appendChild(cloned);
		$A(cloned.getElementsByTagName('input')).each(function(f) {f.value = '';});
		this.initCells(cloned);

		return cloned;
	},

	// there should be an existing function for that
	getNodeIndex: function(node)
	{
		var nodes = node.parentNode.getElementsByTagName(node.tagName);
		for (k = 0; k < nodes.length; k++)
		{
			if (nodes[k] == node)
			{
				return k;
			}
		}
	},

	indexForm: function()
	{
		var val = {quant: [], group: [], price: []};
		$A(this.container.getElementsByClassName('quantity')).each(function(f){val.quant.push(f.value); });
		$A(this.container.getElementsByTagName('select')).each(function(f){val.group.push(f.value); });
		$A(this.container.getElementsByClassName('qprice')).each(function(f){val.price.push(f.value); });
		this.hiddenValue.value = Object.toJSON(val);
	}
}

Backend.Product.GridFormatter =
{
	productUrl: '',

	getClassName: function(field, value)
	{

	},

	formatValue: function(field, value, id)
	{
		if ('Product.name' == field && Backend.Product.productsMiscPermision)
		{
			value = '<span>' +
						'<span class="progressIndicator" id="productIndicator_' + id + '" style="display: none;"></span>' +
					'</span>' +
					'<a href="' + this.productUrl + id + '" id="product_' + id + '" onclick="Backend.Product.openProduct(' + id + ', event); return false;">' +
						value +
					'</a>';
		}

		return value;
	}
}

Backend.Product.previewUploadedImage = function(upload, res)
{
	var
		root,
		template,
		image,
		emptyUpload,
		uploadContainer;

	LiveCart.AjaxRequest.prototype.showConfirmation(res);
	if (res.status == "success")
	{
		upload = $(upload);
		root = upload.up(".productImages");
		template = root.down(".thumbTemplate").cloneNode(true);
		root.down(".thumbsContainer").appendChild(template);
		template = $(template);
		template.down(".fileName").update(res.name);
		image = template.down(".fileImage img");
		template.down(".productImageFileName").value=res.file;
		if(res.thumb)
		{
			image.src = res.thumb;
		}
		else
		{
			image.hide();
		}
		template.show();

		// empty (replace with new) input="type" control
		emptyUpload = root.down(".upload_productImageEmpty").cloneNode(true);
		uploadContainer = root.down(".uploadContainer");
		$(uploadContainer.down("input")).remove();
		uploadContainer.insertBefore(emptyUpload, uploadContainer.down("div"));
		emptyUpload.removeClassName("upload_productImageEmpty");
		new LiveCart.FileUpload(emptyUpload, root.down(".fileUploadOptions").value , Backend.Product.previewUploadedImage);
		emptyUpload.show();
	}

}