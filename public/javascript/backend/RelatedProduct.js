/**
 *	@author Integry Systems
 */

if(!Backend) Backend = {};
if(!Backend.Product) Backend.Product = {};

Backend.RelatedProduct =
{
	groupController: 'backend.productRelationshipGroup',

	itemController:  'backend.productRelationship',

	getContainer: function(ownerID)
	{
		return $('tabProductRelationship_' + ownerID + 'Content');
	}
}

Backend.RelatedProduct.activeListCallbacks = function(ownerID)
{
	this.ownerID = ownerID;
	this.controller = this.namespace.itemController;
}

Backend.RelatedProduct.activeListCallbacks.methods =
{
	namespace: Backend.RelatedProduct,

	beforeDelete: function(li)
	{
		if(confirm(this.callbacks.namespace.messages.areYouSureYouWantToDelete))
		{
			return Backend.Router.createUrl(this.callbacks.controller, 'delete', {relatedownerID: this.getRecordId(li), id: this.callbacks.ownerID});
		}
	},

	afterDelete: function(li, response)
	{
		try
		{
			response = eval('(' + response + ')');
		}
		catch(e)
		{
			return false;
		}

		if(!response.error)
		{
			var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
			tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') - 1);

			return true;
		}

		return false;
	}
}

Backend.RelatedProduct.activeListCallbacks.inheritsFrom(Backend.ProductListCommon.Product.activeListCallbacks);

Backend.RelatedProduct.addProductToList = function(ownerID, relatedownerID, popup)
{
	var self = this;
	new LiveCart.AjaxRequest(
		Backend.Router.createUrl('backend.productRelationship', 'addRelated', {id: ownerID, relatedownerID: relatedownerID}),
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
				var relatedList = ActiveList.prototype.getInstance($("noGroup" + ownerID));
				relatedList.addRecord(relatedownerID, response.responseText, true);

				var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
				tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') + 1);

				new Backend.SelectPopup.prototype.popup.Backend.SaveConfirmationMessage('productRelationshipCreated');
				new Backend.SaveConfirmationMessage('productRelationshipCreated');
			}
		}
	);
}


Backend.RelatedProduct.Group = {};

Backend.RelatedProduct.Group.Callbacks = function(ownerID)
{
	this.deleteMessage = this.namespace.Group.Messages.areYouSureYouWantToDelete;
	this.controller = this.namespace.groupController;
	this.ownerID = ownerID;
}

Backend.RelatedProduct.Group.Callbacks.methods =
{
	deleteMessage: null,

	namespace: Backend.RelatedProduct,

	afterDelete: function(li, response)
	{
		try
		{
			response = eval('(' + response + ')');
		}
		catch(e)
		{
			return false;
		}

		if (!response.error)
		{
			var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
			tabControl.setCounter('tabProductRelationship', tabControl.getCounter('tabProductRelationship') - li.getElementsByTagName('li').length + 2);

			return true;
		}

		return false;
	},

	beforeEdit:	 function(li)
	{
		var object = this.callbacks.namespace.Group.Controller.prototype.getInstance(li.down('.groupForm'));
		if(!object)
		{
			return Backend.Router.createUrl(this.callbacks.controller, 'edit', {id: this.getRecordId(li)});
		}
		else
		{
			object.toggleForm();
		}
	},

	afterEdit:	  function(li, response)
	{
		response = eval("(" + response + ")");

		var model = new this.callbacks.namespace.Group.Model(response);
		var group = new this.callbacks.namespace.Group.Controller(li.down('.groupForm'), model);
		group.toggleForm();
	}
}

Backend.RelatedProduct.Group.Callbacks.inheritsFrom(ActiveList.CallbacksCommon);

Backend.RelatedProduct.Group.Model = function(data)
{
	this.store(data || {});
	if(!this.get('ID', false))
	{
		this.isNew = true;
	}
}

Backend.RelatedProduct.Group.Model.methods =
{
	namespace: Backend.RelatedProduct,

	save: function(form, onSaveResponse, scope)
	{
		var self = scope || this;
		form.action = Backend.Router.createUrl(self.namespace.groupController, self.isNew ? 'create' : 'update');
		return this.parent.save.call(self, form, onSaveResponse);
	},

	getOwnerID: function()
	{
		return this.get(this.getOwnerIDField(), '');
	},

	getOwnerIDField: function()
	{
		return this.getOwnerClass() + '.ID';
	},

	getOwnerClass: function()
	{
		return 'Product';
	}
}

Backend.RelatedProduct.Group.Model.inheritsFrom(MVC.Model);

Backend.RelatedProduct.Group.Controller = function(root, model)
{
	this.model = model;
	this.view = new this.namespace.Group.View(root, this.model.getOwnerID());
	this.model.setController(this);

	this.setDefaultValues();

	this.bindActions();

	Form.State.backup(this.view.nodes.root);
	this.instances[this.view.nodes.root.id] = this;
}

Backend.RelatedProduct.Group.Controller.prototype =
{
	instances: {},

	groupList: null,

	namespace: Backend.RelatedProduct,

	index: function(ownerID)
	{
		var container = this.container = this.namespace.getContainer(ownerID);

		var newFormElement = container.down('.newForm').down('.groupForm');
		var modelParams = {}
		modelParams[this.namespace.Group.Model.prototype.getOwnerClass()] = {ID: ownerID}
		var emptyGroupModel = new this.namespace.Group.Model(modelParams);
		var newForm = new this.namespace.Group.Controller(newFormElement, emptyGroupModel);

		Event.observe(container.down('.addGroup').down('a'), "click", function(e)
		{
			Event.stop(e);
			newForm.showNewForm();
		});

		var addProduct = container.down('.addProduct');
		if (addProduct)
		{
			Event.observe(addProduct.down('a'), 'click', function(e)
			{
				Event.stop(e);
				var self = this;
				new Backend.SelectPopup(
					this.namespace.links.selectProduct,
					this.namespace.messages.selectProductTitle,
					{
						onObjectSelect: function() { self.namespace.addProductToList(ownerID, this.objectID, this.popup.document) }
					}
				);
			}.bind(this));
		}

		// items without group
		var noGroup = container.down('.noGroup');
		if (noGroup)
		{
			ActiveList.prototype.getInstance(noGroup, new this.namespace.activeListCallbacks(ownerID));
		}

		// group list
		var groupList = ActiveList.prototype.getInstance(container.down('.activeListGroup'), new this.namespace.Group.Callbacks(ownerID));
		newForm.view.setGroupList(groupList);

		// each group
		$A(container.getElementsBySelector('li.groupContainer')).each(function(ul)
		{
			var group = ul.down('ul.subList');
			if (group)
			{
				ActiveList.prototype.getInstance(group, new this.namespace.activeListCallbacks(ownerID));
			}
		}.bind(this));

		groupList.createSortable(true);
	},

	getInstance: function(rootNode)
	{
		rootNode = $(rootNode);
		if (!rootNode.id)
		{
			Backend.setUniqueID(rootNode);
		}

		return this.instances[rootNode.id];
	},

	setDefaultValues: function()
	{
		this.view.assign('name', this.model.get('name'));
		this.view.assign('ID', this.model.get('ID', ''));
		this.view.assign('ownerID', this.model.getOwnerID());
	},

	bindActions: function()
	{
		var self = this;

		Event.observe(this.view.nodes.save, 'click', function(e) { Event.stop(e); self.onSave(); });
		Event.observe(this.view.nodes.cancel, 'click', function(e) { Event.stop(e); self.onCancel(); });
		Event.observe(this.view.nodes.newGroupCancelLink, 'click', function(e) { Event.stop(e); self.onCancel(); });
	},

	onSave: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.view.nodes.root);
		this.model.save(this.view.nodes.root.down('form'), function(status) {
			this.onSaveResponse(status) ;
		}.bind(this));
	},

	onCancel: function()
	{
		Form.State.restore(this.view.nodes.root);

		if(this.model.isNew)
		{
			this.view.hideNewForm();
		}
		else
		{
			this.view.hideForm();
		}
	},

	onSaveResponse: function(status)
	{
		if('success' == status)
		{
			if(this.model.isNew)
			{
				this.view.assign('ID', this.model.get('ID'));
				this.view.assign('ownerID', this.model.getOwnerID());
				this.view.createNewGroup();
				this.model.store('ID', false);

				this.view.hideNewForm();
			}
			else
			{
				this.view.hideForm('yellow');
			}
			Form.State.restore(this.view.nodes.root);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.view.nodes.root, this.model.errors);
		}
	},

	toggleForm: function()
	{
		if('block' != this.view.nodes.root.style.display)
		{
			this.view.showForm();
		}
		else
		{
			this.view.hideForm();
		}
	},

	hideNewForm: function()
	{
		this.view.hideNewForm();
	},

	showNewForm: function()
	{
		this.view.showNewForm();
	}
}


Backend.RelatedProduct.Group.View = function(root, ownerID)
{
	this.ownerID = ownerID;
	this.findNodes(root, ownerID);
	this.clear();
}

Backend.RelatedProduct.Group.View.methods =
{
	ownerID: null,

	groupList: null,

	namespace: Backend.RelatedProduct,

	findNodes: function(root, ownerID)
	{
		this.container = this.namespace.getContainer(ownerID);

		this.nodes = {};
		this.nodes.root = root;
		this.nodes.form = ('FORM' == this.nodes.root.tagName) ? this.nodes.root : this.nodes.root.down('form');

		// controls
		this.nodes.controls = this.nodes.root.down('.controls');
		this.nodes.save = this.nodes.controls.down('.save');
		this.nodes.cancel = this.nodes.controls.down('.cancel');

		this.nodes.id = this.nodes.form.elements.namedItem('ID');
		this.nodes.ownerID = this.nodes.form.elements.namedItem('ownerID');
		this.nodes.name = this.nodes.form.elements.namedItem('name');

		this.nodes.title = this.nodes.root.previous('.groupTitle');

		this.nodes.newGroupCancelLink = this.container.down('.addCancel');

		this.bindForm(this.nodes.form);
		this.bindVariable(this.nodes.title, 'name');
		this.bindVariable(this.nodes.id, 'ID');
		this.bindVariable(this.nodes.ownerID, this.namespace.Group.Model.prototype.getOwnerIDField());
	},

	setGroupList: function(groupList)
	{
		this.groupList = groupList;
	},

	createNewGroup: function()
	{
		var containerDiv = document.createElement('div');
		containerDiv.update(
			'<span class="groupTitle">' + this.nodes.name.value + '</span>'
			+ this.nodes.root.up('.tabPageContainer').down('.blankItem').innerHTML
			+ '<ul id="' + this.get('ID') + '" class="subList activeList_add_sort activeList_add_edit activeList_add_delete activeList_accept_subList">'
			+ '</ul>'
		);

		var li = this.groupList.addRecord(this.get('ID'), containerDiv);
		Element.addClassName(li, 'groupContainer');

		var newGroupProductsList = ActiveList.prototype.getInstance(li.down('.subList'), new this.namespace.activeListCallbacks(this.ownerID));
		ActiveList.prototype.recreateVisibleLists();

		this.groupList.touch(true)

		this.clear();

		return li;
	},

	showForm: function()
	{
		var li = this.nodes.root.up("li");
		var activeList = ActiveList.prototype.getInstance(li.up('ul'));

		ActiveList.prototype.collapseAll();
		this.nodes.title.hide();
		activeList.toggleContainerOn(li.down('.groupForm'));

		this.clear();
	},

	hideForm: function(highlight)
	{
		var li = this.nodes.root.up("li");
		var activeList = ActiveList.prototype.getInstance(li.up('ul'));

		this.nodes.title.show();
		activeList.toggleContainerOff(li.down('.groupForm'), highlight);

		this.clear();
	},

	hideNewForm: function()
	{
		this.getNewForm().hide("add", this.nodes.root);
	},

	showNewForm: function()
	{
		this.getNewForm().show("add", this.nodes.root);
	},

	getNewForm: function()
	{
		return new ActiveForm.Slide(this.container.down('ul.menu'));
	}
}

Backend.RelatedProduct.Group.View.inheritsFrom(MVC.View);

Backend.RegisterMVC(Backend.RelatedProduct.Group);