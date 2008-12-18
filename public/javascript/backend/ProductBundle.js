/**
 *	@author Integry Systems
 */

Backend.ProductBundle =
{
	itemController:  'backend.productBundleItem',

	getContainer: function(ownerID)
	{
		return $('tabProductBundle_' + ownerID + 'Content');
	},

	getTab: function()
	{
		return 'tabProductBundle';
	}
}

Backend.ProductBundle.activeListCallbacks = function()
{
	this.callConstructor(arguments);
};

Backend.ProductBundle.activeListCallbacks.methods =
{
	namespace: Backend.ProductBundle,

	tab: 'tabProductBundle',

	beforeDelete: function(li)
	{
		if (confirm(this.callbacks.namespace.messages.areYouSureYouWantToDelete))
		{
			li.oldParentNode = li.parentNode;
			return Backend.Router.createUrl(this.callbacks.controller, 'delete', {id: this.getRecordId(li), relatedProductID: this.getRecordId(li.parentNode)});
		}
	},

	afterDelete: function(li, response)
	{
		this.callbacks.updateTotal(li.oldParentNode, response.evalJSON().total);
		return this.callbacks.parent.afterDelete.call(this, li, response);
	},

	updateTotal: function(li, total)
	{
		li.up('.tabProductBundleContent').down('.total').down('.price').update(total);
	}
}

Backend.ProductBundle.activeListCallbacks.inheritsFrom(Backend.RelatedProduct.activeListCallbacks);

Backend.ProductBundle.Group = {};
Backend.ProductBundle.Group.Links = {};
Backend.ProductBundle.Group.Messages = {};

Backend.ProductBundle.Group.Callbacks = function()
{
	this.callConstructor(arguments);
};

Backend.ProductBundle.Group.Callbacks.methods =
{
	namespace: Backend.ProductBundle,

	afterDelete: function(li, response)
	{
		CategoryTabControl.prototype.resetTabItemsCount(this.callbacks.ownerID);
	}
}

Backend.ProductBundle.Group.Callbacks.inheritsFrom(Backend.RelatedProduct.Group.Callbacks);



Backend.ProductBundle.Group.Model = function()
{
	this.callConstructor(arguments);
};

Backend.ProductBundle.Group.Model.methods =
{
	namespace: Backend.ProductBundle,

	save: function(form, onSaveResponse)
	{
		return this.parent.save.call(this.parent, form, onSaveResponse, this);
	},

	getOwnerClass: function()
	{
		return 'Product';
	}
}

Backend.ProductBundle.Group.Model.inheritsFrom(Backend.RelatedProduct.Group.Model);



Backend.ProductBundle.Group.View = function()
{
	this.callConstructor(arguments);
};

Backend.ProductBundle.Group.View.methods =
{
	namespace: Backend.ProductBundle,

	menu: null,

	createNewGroup: function()
	{
		var li = this.parent.createNewGroup.call(this);
		this.addMenu(li);
		CategoryTabControl.prototype.resetTabItemsCount(this.ownerID);
	},

	addMenu: function(li, container)
	{
		var menu = this.getMenu(container);
		li.insertBefore(menu, li.down('ul.subList'));

		var addProduct = menu.down('.addProduct');

		Event.observe(addProduct.down('a'), 'click', function(e)
		{
			Event.stop(e);
			var self = this;
			new Backend.SelectPopup(
				this.namespace.links.selectProduct,
				this.namespace.messages.selectProductTitle,
				{
					onObjectSelect: function() { self.namespace.addProductToList(li, this.objectID, this.popup.document) }
				}
			);
		}.bind(this));
	},

	getMenu: function(container)
	{
		if (!this.menu)
		{
			var menuContainer = container ? container : this.container;
			this.menu = menuContainer.down('.addProductToListMenu').down('ul');
		}

		return this.menu.cloneNode(true);
	}
}

Backend.ProductBundle.Group.View.inheritsFrom(Backend.RelatedProduct.Group.View);



Backend.ProductBundle.Group.Controller = function()
{
	return this.callConstructor(arguments);
};

Backend.ProductBundle.addProductToList = function(ownerID, relatedownerID, popup)
{
	var self = this;
	new LiveCart.AjaxRequest(
		Backend.Router.createUrl('backend.productBundleItem', 'add', {id: ownerID, relatedownerID: relatedownerID}),
		false,
		function(response)
		{
			var evaluatedResponse = response.responseData;;

			popup.getElementById('productIndicator_' + relatedownerID).hide();

			if(evaluatedResponse && evaluatedResponse.error && evaluatedResponse.error.length > 0)
			{
				// error
				Backend.SaveConfirmationMessage.prototype.showMessage(evaluatedResponse.error, 'red');
				new Backend.SelectPopup.prototype.popup.Backend.SaveConfirmationMessage('productRelationshipCreateFailure');
			}
			else
			{
				var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
				tabControl.setCounter('tabProductBundle', tabControl.getCounter('tabProductBundle') + 1);

				var relatedList = ActiveList.prototype.getInstance($('productBundle_' + ownerID));
				var record = relatedList.addRecord(relatedownerID, response.responseText, true);

				Backend.ProductBundle.activeListCallbacks.methods.updateTotal(record, record.down('.newTotal').innerHTML);

				new Backend.SelectPopup.prototype.popup.Backend.SaveConfirmationMessage('productRelationshipCreated');
				Backend.SaveConfirmationMessage.prototype.showMessage(Backend.getTranslation('_added_to_product_bundle'));
			}
		}
	);
}

Backend.ProductBundle.Group.Controller.methods =
{
	namespace: Backend.ProductBundle,

	index: function(ownerID)
	{
		this.parent.index.call(this, ownerID);

		// each group
		$A(this.container.getElementsBySelector('li.groupContainer')).each(function(li)
		{
			Backend.ProductBundle.Group.View.prototype.addMenu(li, this.namespace.getContainer(ownerID));
		}.bind(this));
	}
}

Backend.ProductBundle.Group.Controller.inheritsFrom(Backend.RelatedProduct.Group.Controller);