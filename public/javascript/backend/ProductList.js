/**
 *	@author Integry Systems
 */

Backend.ProductList =
{
	groupController: 'backend.productList',

	itemController:  'backend.productListItem',

	getContainer: function(ownerID)
	{
		return $('tabProductListsContent_' + ownerID);
	},

	getTab: function()
	{
		return 'tabProductLists';
	}
}

Backend.ProductList.activeListCallbacks = function()
{
	this.callConstructor(arguments);
};

Backend.ProductList.activeListCallbacks.methods =
{
	namespace: Backend.ProductList,

	beforeDelete: function(li)
	{
		if(confirm(this.callbacks.namespace.messages.areYouSureYouWantToDelete))
		{
			return Backend.Router.createUrl(this.callbacks.controller, 'delete', {id: this.getRecordId(li)});
		}
	}
}

Backend.ProductList.activeListCallbacks.inheritsFrom(Backend.RelatedProduct.activeListCallbacks);

Backend.ProductList.Group = {};
Backend.ProductList.Group.Links = {};
Backend.ProductList.Group.Messages = {};

Backend.ProductList.Group.Callbacks = function()
{
	this.callConstructor(arguments);
};

Backend.ProductList.Group.Callbacks.methods =
{
	namespace: Backend.ProductList,

	afterDelete: function(li, response)
	{
		CategoryTabControl.prototype.resetTabItemsCount(this.callbacks.ownerID);
	}
}

Backend.ProductList.Group.Callbacks.inheritsFrom(Backend.RelatedProduct.Group.Callbacks);



Backend.ProductList.Group.Model = function()
{
	this.callConstructor(arguments);
};

Backend.ProductList.Group.Model.methods =
{
	namespace: Backend.ProductList,

	save: function(form, onSaveResponse)
	{
		return this.parent.save.call(this.parent, form, onSaveResponse, this);
	},

	getOwnerClass: function()
	{
		return 'Category';
	}
}

Backend.ProductList.Group.Model.inheritsFrom(Backend.RelatedProduct.Group.Model);



Backend.ProductList.Group.View = function()
{
	this.callConstructor(arguments);
};

Backend.ProductList.Group.View.methods =
{
	namespace: Backend.ProductList,

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
			e.preventDefault();
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

Backend.ProductList.Group.View.inheritsFrom(Backend.RelatedProduct.Group.View);



Backend.ProductList.Group.Controller = function()
{
	return this.callConstructor(arguments);
};

Backend.ProductList.addProductToList = function(owner, relatedownerID, popup)
{
	var self = this;
	var ownerID = ActiveList.prototype.getRecordId(owner);
	new LiveCart.AjaxRequest(
		Backend.Router.createUrl('backend.productListItem', 'add', {id: ownerID, relatedownerID: relatedownerID}),
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
				var relatedList = ActiveList.prototype.getInstance(owner.down('ul.subList'));
				relatedList.addRecord(relatedownerID, response.responseText, true);

				/*
				var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
				tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') + 1);
				*/
				new Backend.SelectPopup.prototype.popup.Backend.SaveConfirmationMessage('productRelationshipCreated');
				Backend.SaveConfirmationMessage.prototype.showMessage(Backend.getTranslation('_added_to_product_list'));
			}
		}
	);
}

Backend.ProductList.Group.Controller.methods =
{
	namespace: Backend.ProductList,

	index: function(ownerID)
	{
		this.parent.index.call(this, ownerID);

		// each group
		$A(this.container.getElementsBySelector('li.groupContainer')).each(function(li)
		{
			Backend.ProductList.Group.View.prototype.addMenu(li, this.namespace.getContainer(ownerID));
		}.bind(this));
	}
}

Backend.ProductList.Group.Controller.inheritsFrom(Backend.RelatedProduct.Group.Controller);

//Backend.ProductList.initNameSpace();