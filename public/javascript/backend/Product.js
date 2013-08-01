app.controller('ProductController', function ($scope, $http, $resource, $dialog)
{
	$scope.resource = $resource(Router.createUrl('backend.product', 'lists', {id: $scope.category.id}), {}, {'query':  {method:'GET', isArray: false}});

	$http.get(Router.createUrl('backend.product', 'category', {id: $scope.category.id})).
		success(function(data, status, headers, config)
		{
			$scope.columnDefs = data.options.columnDefs;
			$scope.data = data.data;
			$scope.totalServerItems = data.totalCount;
		});

	$scope.edit = function(id)
	{
		var d = $dialog.dialog({dialogFade: false, resolve: {id: function(){ return id; } }});
		d.open(Router.createUrl('backend.product', 'edit'), 'EditProductController');
	};
});

app.controller('ProductPresentationController', function ($scope, $http)
{
	$http.get(Router.createUrl('backend.product', 'presentation', {id: $scope.product.ID})).
		success(function(data)
		{
			$scope.presentation = data;
		});

	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend.presentation', 'updatePresentation'), $scope.presentation);
	}

    $scope.cancel = function()
    {
        dialog.close();
    };
});

app.controller('EditProductController', ['$scope', '$http', 'dialog', 'id', function ($scope, $http, dialog, id)
{
	$http.get(Router.createUrl('backend.product', 'get', {id: id})).
		success(function(data)
		{
			$scope.product = data;
		});

	$scope.getSpecFieldTemplate = function(product)
	{
		if (!product)
		{
			return;
		}

		return Router.createUrl('backend.product', 'specFields', {id: product.ID});
	};

	$scope.save = function(form)
	{
		$http.post(Router.createUrl('backend.product', 'update'), $scope.product);
	}

    $scope.cancel = function()
    {
        dialog.close();
    };
}]);

app.directive('quantityPrice', function($compile)
{
    return {
        restrict: "E",
        scope: true,
        controller: function($scope, $element, $attrs)
        {
			$scope.groups = [{id: '0', oldValue: '0'}, {id: '', oldValue: ''}];
			$scope.quantities = [];
			$scope.isInitialized = false;
			$scope.isActive = false;

			$scope.init = function(currency)
			{
				$scope.currency = currency;
				$scope.$watch('product.quantityPrice.' + currency + '.serializedRules', function(newPrice)
				{
					if (!newPrice || $scope.isInitialized)
					{
						return;
					}

					$scope.isInitialized = true;
					$scope.isActive = true;

					$scope.quantities = [];
					_.each(newPrice, function(prices, quant)
					{
						var quantity = {quantity: quant, oldValue: quant};
						_.each(prices, function(price, group)
						{
							quantity[group] = price;
						});

						$scope.quantities.push(quantity);
					});

					var groups = [];
					_.each(newPrice, function(prices) { groups = groups.concat(_.keys(prices)); });

					$scope.groups = [];
					_.each(_.uniq(groups), function(group)
					{
						var id = (group).toString();
						$scope.groups.push({id: id, oldValue: id});
					});

					$scope.groups.push({id: '', oldValue: ''});
					$scope.addQuantity();
					$scope.sortGroups();
				});
			};

			$scope.updateQuantities = function(quantity)
			{
				if (quantity.oldValue == '')
				{
					$scope.addQuantity();
				}

				quantity.oldValue = quantity.quantity;
			};

			$scope.updateOnBlur = function(quantity)
			{
				if (quantity.quantity == '')
				{
					$scope.quantities.splice($scope.quantities.indexOf(quantity), 1);
					$scope.addQuantity();
				}

				$scope.quantities = _.sortBy($scope.quantities, function(quantity)
				{
					return quantity.quantity != '' ? parseInt(quantity.quantity) : 'a';
				});
			};

			$scope.addQuantity = function()
			{
				if (($scope.quantities.length == 0) || (_.last($scope.quantities).quantity != ''))
				{
					$scope.quantities.push({quantity: '', oldValue: '', 0: ''});
				}
			};

			$scope.addGroup = function(group)
			{
				if (group.oldValue == '0')
				{
					$scope.groups.unshift({id: '0', oldValue: '0'});
				}
				else if (group.oldValue === '')
				{
					$scope.groups.push({id: '', oldValue: ''});
				}

				var existing = _.findWhere($scope.groups, {id: group.id, oldValue: group.id});
				if (existing)
				{
					$scope.groups.splice(_.indexOf($scope.groups, existing), 1);
				}

				_.each($scope.quantities, function(quantity)
				{
					quantity[group.id] = quantity[group.oldValue];
					delete quantity[group.oldValue];
				});

				group.oldValue = group.id;

				$scope.sortGroups();
			};

			$scope.sortGroups = function()
			{
				$scope.groups = _.sortBy($scope.groups, function(group) { return group.id != '0'; });
			};

			$scope.$watch('quantities', function()
			{
				if (!$scope.product)
				{
					return;
				}

				var rules = {};
				_.each($scope.quantities, function(quantity)
				{
					if ('' === quantity.quantity)
					{
						return;
					}

					rules[quantity.quantity] = {};
					_.each(quantity, function(price, id)
					{
						if (!isNaN(parseInt(id)))
						{
							rules[quantity.quantity][id] = price;
						}
					});
				});

				$scope.product.quantityPrice[$scope.currency].serializedRules = rules;
			}, true);

			$scope.addQuantity();
		}
    };
});


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

	saveForm: function(form)
	{
		var saveHandler = new Backend.Product.saveHandler(form);
		new LiveCart.AjaxRequest(form, null, saveHandler.saveComplete.bind(saveHandler));
	},

	openProduct: function(id, e, onComplete)
	{
		if ($('productIndicator_' + id))
		{
			var indicator = $('productIndicator_' + id);
		}
		else if (e)
		{
			var indicator = Event.element(e).parentNode.down('.progressIndicator');
			if (indicator)
			{
				// ugly hack
				setTimeout(function() { indicator.hide() }, 5000);
			}
		}

		if (indicator)
		{
			Element.show(indicator);
		}

		if (window.opener && window.opener.selectProductPopup && e)
		{
			var downloadable = parseInt(Event.element(e).up('tr').down(".cell_hiddenType").innerHTML) == 1;

			window.opener.selectProductPopup.getSelectedObject(id, downloadable, indicator);
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
			e.preventDefault();
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
		else if (('set_specField' == element.value.substr(0, 13)) || ('remove_specField' == element.value.substr(0, 16)))
		{
			var container = element.up('form').down('.' + element.value);

			if (!container)
			{
				return;
			}

			var id = element.value.match(/_([0-9]+)/)[1];
			var container = element.up('form').down('.specFieldValueContainer');
			new LiveCart.AjaxUpdater(Backend.Router.createUrl('backend.product', 'massActionField', {id: id}), container, element.parentNode.down('#progressIndicator_specField'), false, function()
			{
				new Backend.Eav(container);
				container.show();
			});
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
		Event.observe(this.nodes.cancel, 'click', function(e) { e.preventDefault(); self.cancelForm()});
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
		return url.replace(/_categoryID_/, Backend.Category.getSelectedId()).replace(/_id_/, Backend.Product.Editor.prototype.getCurrentProductId());
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

		if (!Backend.Category.getSelectedId())
		{
			//Backend.Category.treeBrowser.selectItem(1, false);
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
