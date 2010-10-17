/**
 *	@author Integry Systems
 */

if (!Backend.User)
{
	Backend.User = {};
}

Backend.UserGroup = Class.create();
Backend.UserGroup.prototype =
{
	Links: {},
	Messages: {},

	treeBrowser: null,

  	urls: new Array(),

	initialize: function(groups)
	{
		var self = this;

		Backend.UserGroup.prototype.treeBrowser = new dhtmlXTreeObject("userGroupsBrowser","","", 0);
		Backend.Breadcrumb.setTree(Backend.UserGroup.prototype.treeBrowser);

		Backend.UserGroup.prototype.treeBrowser.setOnClickHandler(this.activateGroup);

		Backend.UserGroup.prototype.treeBrowser.def_img_x = 'auto';
		Backend.UserGroup.prototype.treeBrowser.def_img_y = 'auto';

		Backend.UserGroup.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		Backend.UserGroup.prototype.treeBrowser.setOnClickHandler(this.activateGroup.bind(this));

		Backend.UserGroup.prototype.treeBrowser.showFeedback =
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

		Backend.UserGroup.prototype.treeBrowser.hideFeedback =
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

		this.insertTreeBranch(groups, 0);

		var userID = Backend.getHash().match(/user_(\d+)/);
		if (userID && userID[1])
		{
			Element.show($('loadingUser'));
			Backend.UserGroup.prototype.openUser(userID[1], null, function() { Element.hide($('loadingUser')); });
		}
		else
		{
			var id = -2
			var match = null;
			if(!(match = Backend.ajaxNav.getHash().match(/group_(-?\d+)#\w+/)))
			{
				window.location.hash = 'group_' + id + '#tabUsers__';
			}
			else
			{
				id = match[1];
			}

			// a hackish solution to hide tree feedback after the first/initial list has been loaded
			Backend.UserGroup.prototype.activeGroup = -3;
		}

		self.tabControl = TabControl.prototype.getInstance('userGroupsManagerContainer', self.craftTabUrl, self.craftContainerId, {});

		window.currentUserGroup = self;

		this.bindEvents();
	},

	bindEvents: function()
	{
		var self = this;

		if($("userGroups_add"))
		{
			Event.observe($("userGroups_add"), "click", function(e) {
				Event.stop(e);
				self.createNewGroup();
			});
		}

		if($("userGroups_delete"))
		{
			Event.observe($("userGroups_delete"), "click", function(e) {
				Event.stop(e);
				self.deleteGroup();
			});
		}
	},

	deleteGroup: function()
	{
		var $this = this;

		if(Backend.UserGroup.prototype.activeGroup < 0)
		{
			 return alert(Backend.UserGroup.prototype.Messages.youCanntoDeleteThisGroup)
		}

		if (confirm(Backend.UserGroup.prototype.Messages.confirmUserGroupRemove))
		{
			new LiveCart.AjaxRequest(
				Backend.User.Group.prototype.Links.removeNewGroup + '/' + Backend.UserGroup.prototype.activeGroup,
				false,
				function(response)
				{
					response = eval("(" + response.responseText + ")");
					if('success' == response.status)
					{
						Backend.UserGroup.prototype.treeBrowser.deleteItem(response.userGroup.ID, true);
						var firstId = parseInt(Backend.UserGroup.prototype.treeBrowser._globalIdStorage[1]);
						if(firstId)
						{
							Backend.UserGroup.prototype.treeBrowser.selectItem(firstId, true);
						}
					}
				}
			);
		}
	},

	createNewGroup: function()
	{
		new LiveCart.AjaxRequest(
			Backend.User.Group.prototype.Links.createNewUserGroup,
			false,
			function(response)
			{
				this.afterGroupAdded(response, this)
			}.bind(this)
		);
	},

	afterGroupAdded: function(response, self)
	{
		var newGroup = eval('(' + response.responseText + ')');
		self.treeBrowser.insertNewItem(-2, newGroup.ID, newGroup.name, 0, 0, 0, 0, 'SELECT');

		self.activateGroup(newGroup.ID, 'tabUserGroup');
	},
	craftTabUrl: function(url)
	{
		return url.replace(/_id_/, Backend.UserGroup.prototype.treeBrowser.getSelectedItemId());
	},

	craftContainerId: function(tabId)
	{
		return tabId + '_' +  Backend.UserGroup.prototype.treeBrowser.getSelectedItemId() + 'Content';
	},

	insertTreeBranch: function(treeBranch, rootId)
	{
		var self = this;


		$A(treeBranch).each(function(node)
		{
			Backend.UserGroup.prototype.treeBrowser.insertNewItem(node.rootID, node.ID, node.name, null, 0, 0, 0, '', 1);
			self.treeBrowser.showItemSign(node.ID, 0);
			var group = document.getElementsByClassName("standartTreeRow", $("userGroupsBrowser")).last();
			group.id = 'group_' + node.ID;
			group.onclick = function()
			{
				Backend.UserGroup.prototype.treeBrowser.selectItem(node.ID, true);
			}
		});
	},

	activateGroup: function(id, activateTab)
	{
		Backend.Breadcrumb.display(id);

		if($('newUserForm_' + Backend.UserGroup.prototype.activeGroup) && Element.visible('newUserForm_' + Backend.UserGroup.prototype.activeGroup))
		{
			Backend.User.Add.prototype.getInstance(Backend.UserGroup.prototype.activeGroup).hideAddForm();
		}

		if(!activateTab)
		{
			activateTab = $('tabUsers');
		}

		if(id < 0)
		{
			$("tabUserGroup").hide();
			$("tabRoles").hide();
		}
		else
		{
			$("tabUserGroup").show();
			$("tabRoles").show();
		}

		if(Backend.User.Editor.prototype.getCurrentId())
		{
			var user = Backend.User.Editor.prototype.getInstance(Backend.User.Editor.prototype.getCurrentId(), false);
			user.cancelForm();
		}

		if ($("userGroups_delete"))
		{
			if(id <= 1)
			{
				$("userGroups_delete").parentNode.hide();
			}
			else
			{
				$("userGroups_delete").parentNode.show();
			}
		}

		if(/*Backend.UserGroup.prototype.activeGroup && */Backend.UserGroup.prototype.activeGroup != id)
		{
			Backend.UserGroup.prototype.activeGroup = id;
			Backend.UserGroup.prototype.treeBrowser.showFeedback(id);

			Backend.ajaxNav.add('group_' + id);

			this.tabControl.activateTab(activateTab, function() {
				Backend.UserGroup.prototype.treeBrowser.hideFeedback(id);
			});

			Backend.showContainer("userGroupsManagerContainer");
		}

		Backend.UserGroup.prototype.activeGroup = id;
	   // Backend.ajaxNav.add('group_' + Backend.UserGroup.prototype.activeGroup + "#tabUsers");
	},

	displayCategory: function(response)
	{
		Backend.UserGroup.prototype.treeBrowser.hideFeedback();
		var cancel = document.getElementsByClassName('cancel', $('userGroupsContent'))[0];
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
		var indicator = document.getElementsByClassName('progressIndicator', form)[0];
		new LiveCart.AjaxRequest(form, indicator, this.displaySaveConfirmation.bind(this));
	},

	displaySaveConfirmation: function()
	{
		new Backend.SaveConfirmationMessage(document.getElementsByClassName('yellowMessage')[0]);
	},


	openUser: function(id, e, onComplete)
	{
		if (e)
		{
			Event.stop(e);

			if(!e.target)
			{
				e.target = e.srcElement
			}

			Element.show(e.target.up('td').down('.progressIndicator'));
		}

		if (window.opener)
		{
			window.opener.selectProductPopup.getSelectedObject(id);
			return;
		}

		Backend.User.Editor.prototype.setCurrentId(id);

		var tabControl = TabControl.prototype.getInstance(
			'userManagerContainer',
			Backend.User.Editor.prototype.craftTabUrl,
			Backend.User.Editor.prototype.craftContentId
		);

		tabControl.activateTab(null,
								   function(response)
								   {
										if (onComplete)
										{
											onComplete(response);
										}

										Backend.ajaxNav.add("#user_" + id);
								   });

		if(Backend.User.Editor.prototype.hasInstance(id))
		{
			Backend.User.Editor.prototype.getInstance(id);
		}
	}
}


Backend.UserGroup.GridFormatter =
{
	userUrl: '',

	getClassName: function(field, value)
	{

	},

	formatValue: function(field, value, id)
	{
		if('User.email' == field && window.opener && window.opener.Backend.CustomerOrder)
		{
			value = '<span><span class="progressIndicator userIndicator" id="userIndicator_' + id + '" style="display: none;"></span></span>' + '<a href="#select" onclick="window.opener.Backend.CustomerOrder.prototype.customerPopup.getSelectedObject(' + id + '); return false;">' + value + '</a>';
		}

		else if ('User.email' == field)
		{
			value = '<span><span class="progressIndicator userIndicator" id="userIndicator_' + id + '" style="display: none;"></span></span>' +
				'<a href="' + this.userUrl + id + '" id="user_' + id + '" onclick="Backend.UserGroup.prototype.openUser(' + id + ', event); return false;">' +
					 value +
				'</a>';
		}

		if(value == '-')
		{
			value = "<center>" + value + "</center>";
		}

		return value;
	}
}


if (window.ActiveGrid)
{
	Backend.UserGroup.massActionHandler = Class.create();
	Object.extend(Object.extend(Backend.UserGroup.massActionHandler.prototype, ActiveGrid.MassActionHandler.prototype),
		{
			customComplete:
				function()
				{
					Backend.User.Editor.prototype.resetEditors();

					if(window.activeGrids['users_-2'])
					{
						window.activeGrids['users_-2'].reloadGrid();
					}

					this.blurButton();
				}
		}
	);
}

Backend.User.Group = Class.create();
Backend.User.Group.prototype =
{
	Links: {},
	Messages: {},
	Instances: {},

	initialize: function(root)
	{
		this.findNodes(root);
		this.bindEvents();

		Form.State.backup(this.nodes.form);
	},

	getInstance: function(root)
	{
		if(!Backend.User.Group.prototype.Instances[$(root).id])
		{
			Backend.User.Group.prototype.Instances[$(root).id] = new Backend.User.Group(root);
		}

		return Backend.User.Group.prototype.Instances[$(root).id];
	},

	findNodes: function(root)
	{
		this.nodes = {};
		this.nodes.root = $(root);
		this.nodes.form = this.nodes.root.tagName == 'FORM' ? this.nodes.root : this.nodes.root.down('form');


		this.nodes.name = $(this.nodes.form).elements.namedItem('name');
		this.nodes.description = $(this.nodes.form).elements.namedItem('description');
		this.nodes.ID = $(this.nodes.form).elements.namedItem('ID');
		this.nodes.cancel = this.nodes.root.down('.userGroup_cancel');
	},

	bindEvents: function()
	{
		Event.observe(this.nodes.cancel, "click", function(e) { Event.stop(e); this.cancel() }.bind(this));
	},

	save: function()
	{
		this.nodes.form.action = Backend.User.Group.prototype.Links.save + "/" + this.nodes.ID.value
		var request = new LiveCart.AjaxRequest(
		   this.nodes.form,
		   false,
		   function(response)
		   {
			   response = eval("(" + response.responseText + ")");
			   this.afterSave(response);
		   }.bind(this)
		);

		this.saving = false;
	},

	afterSave: function(response)
	{
		if(response.status == 'success')
		{
			Backend.UserGroup.prototype.treeBrowser.setItemText(Backend.UserGroup.prototype.activeGroup, this.nodes.name.value);
			$$(".user_userGroup > select option[value=" + Backend.UserGroup.prototype.activeGroup + "]").each(function(option)
			{
				option.text = this.nodes.name.value;
			}.bind(this));

			Form.State.backup(this.nodes.form);
		}
		else
		{
			ActiveForm.prototype.serErrorMessages(this.nodes.form, response.errors);
		}
	},

	cancel: function()
	{
		Form.State.restore(this.nodes.form);
	}
}

Backend.User.Editor = Class.create();
Backend.User.Editor.prototype =
{
	Links: {},
	Messages: {},
	Instances: {},
	CurrentId: null,

	getCurrentId: function()
	{
		return Backend.User.Editor.prototype.CurrentId;
	},

	setCurrentId: function(id)
	{
		Backend.User.Editor.prototype.CurrentId = id;
	},

	craftTabUrl: function(url)
	{
		return url.replace(/_id_/, Backend.User.Editor.prototype.getCurrentId());
	},

	craftContentId: function(tabId)
	{
		return tabId + '_' +  Backend.User.Editor.prototype.getCurrentId() + 'Content'
	},

	getInstance: function(id, doInit)
	{
		if(!Backend.User.Editor.prototype.Instances[id])
		{
			Backend.User.Editor.prototype.Instances[id] = new Backend.User.Editor(id);
		}

		if(doInit !== false) Backend.User.Editor.prototype.Instances[id].init();

		return Backend.User.Editor.prototype.Instances[id];
	},

	showAddForm: function(groupID)
	{

	},

	hasInstance: function(id)
	{
		return this.Instances[id] ? true : false;
	},

	initialize: function(id)
	{
		try { footerToolbar.invalidateLastViewed(); } catch(e) {}

		this.id = id ? id : '';

		this.findUsedNodes();
		this.bindEvents();
		this.oldUserGroup = this.nodes.form.elements.namedItem("UserGroup").value;

		Backend.User.Editor.prototype.showShippingAddress.apply(this);

		Form.State.backup(this.nodes.form, false, false);
	},

	findUsedNodes: function()
	{
		this.nodes = {};
		this.nodes.parent = $("tabUserInfo_" + this.id + "Content");
		this.nodes.form = this.nodes.parent.down("form");
		this.nodes.cancel = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');

		this.nodes.sameAddress = $("user_" + this.nodes.form.elements.namedItem("UserGroup").value + "_" + this.id +"_sameAddresses");
		this.nodes.shippingAddress = $("user_" + this.nodes.form.elements.namedItem("UserGroup").value + "_" + this.id +"_shippingAddress");
		this.nodes.billingAddress = $("user_" + this.nodes.form.elements.namedItem("UserGroup").value + "_" + this.id +"_billingAddress");

		this.nodes.password = this.nodes.form.down('.user_password');
		this.nodes.showPassword = this.nodes.form.down('.user_password_show');
		this.nodes.generatePassword = this.nodes.form.down('.user_password_generate');
	},

	bindEvents: function(args)
	{
		var self = this;
		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancelForm()});
		Event.observe(this.nodes.sameAddress, "click", function(e) {
			Backend.User.Editor.prototype.showShippingAddress.apply(this);
		}.bind(this));

		Event.observe(this.nodes.showPassword, "click", function(e) {
			Backend.User.Add.prototype.togglePassword.apply(this, [this.nodes.showPassword.checked]) }.bind(this)
		);
		Event.observe(this.nodes.generatePassword, "click", function(e) {
			Event.stop(e);
			Backend.User.Add.prototype.generatePassword.apply(this)
		}.bind(this));

		Event.observe(this.nodes.form.elements.namedItem("shippingAddress_firstName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("shippingAddress_lastName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("shippingAddress_companyName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("billingAddress_firstName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("billingAddress_lastName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("billingAddress_companyName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
   },

	init: function(args)
	{
		Backend.User.Editor.prototype.setCurrentId(this.id);
		var userIndicator = $('userIndicator_' + this.id);
		document.getElementsByClassName('UserIndicator').each(function(span)
		{
		   if('' == span.style.display)
		   {
			   Element.hide(span);
			   throw $break;
		   }
		});

		if(userIndicator)
		{
			Element.hide(userIndicator);
		}
		Backend.showContainer('userManagerContainer');

		this.tabControl = TabControl.prototype.getInstance("userManagerContainer", false);

		this.setPath();
	},

	setPath: function() {
		Backend.Breadcrumb.display(
			Backend.UserGroup.prototype.treeBrowser.getSelectedItemId(),
			this.nodes.form.elements.namedItem('email').value
		);
	},

	cancelForm: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form, false, false);

		Backend.User.Editor.prototype.setCurrentId(0);
		Backend.UserGroup.prototype.activeGroup = -333;
		window.currentUserGroup.activateGroup(window.currentUserGroup.treeBrowser.getSelectedItemId());
	},

	submitForm: function()
	{
		Backend.User.Editor.prototype.cloneBillingFormValues(this.nodes.sameAddress, this.nodes.shippingAddress, this.nodes.billingAddress);

		this.nodes.form.action = Backend.User.Editor.prototype.Links.update + "/" + this.id;
		new LiveCart.AjaxRequest(
			this.nodes.form,
			false,
			function(responseJSON) {
				ActiveForm.prototype.resetErrorMessages(this.nodes.form);
				var responseObject = eval("(" + responseJSON.responseText + ")");
				this.afterSubmitForm(responseObject);
		   }.bind(this)
		);
	},

	afterSubmitForm: function(response)
	{
		if(response.status == 'success')
		{
			if(!response.user.UserGroup)
			{
			   response.user.UserGroup = {ID: -1};
			}

			if(response.user.UserGroup.ID != this.oldUserGroup)
			{
				if(window.activeGrids["users_-2"])
				{
					window.activeGrids["users_-2"].reloadGrid();
				}

				if(window.activeGrids["users_" + this.oldUserGroup])
				{
					window.activeGrids["users_" + this.oldUserGroup].reloadGrid();
				}

				if(window.activeGrids["users_" + response.user.UserGroup.ID])
				{
					window.activeGrids["users_" + response.user.UserGroup.ID].reloadGrid();
				}
			}

			this.oldUserGroup = this.nodes.form.elements.namedItem("UserGroup").value;
			Form.State.backup(this.nodes.form, false, false);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	},

	showShippingAddress: function()
	{
		if(this.nodes.sameAddress.checked)
		{
			this.nodes.billingAddress.style.display = 'block';
			this.nodes.shippingAddress.hide();
		}
		else
		{
			this.nodes.billingAddress.style.display = 'inline';
			this.nodes.shippingAddress.show();
		}
	},

	cloneBillingFormValues: function(checkbox, shippingAddress, billingAddress) {
		if(checkbox.checked)
		{
			$A(['input', 'select', 'textarea']).each(function(field)
			{
				$A($(shippingAddress).getElementsByTagName(field)).each(function(input)
				{
					if(input.id)
					{
						var prototypeInput = $(input.id.replace(/shippingAddress/, "billingAddress"));

						// Select fields
						if(input.options)
						{
							input.options.length = 0;
							$A(prototypeInput.options).each(function(option) {
								input.options[input.options.length] = new Option(option.text, option.value);
							}.bind(this));
							input.selectedIndex = prototypeInput.selectedIndex;
							input.style.display = prototypeInput.style.display;
						}

						input.style.display = prototypeInput.style.display;
						input.value = prototypeInput.value;
					}
				}.bind(this));
			}.bind(this));
		}
	},

	resetEditors: function()
	{
		Backend.User.Editor.prototype.Instances = {};
		Backend.User.Editor.prototype.CurrentId = null;

		$('userManagerContainer').down('.sectionContainer').innerHTML = '';

		TabControl.prototype.__instances__ = {};
	}
}





Backend.User.Add = Class.create();
Backend.User.Add.prototype =
{
	Instances: {},

	getInstance: function(groupID, grid)
	{
		if(!Backend.User.Add.prototype.Instances[groupID])
		{
			Backend.User.Add.prototype.Instances[groupID] = new Backend.User.Add(groupID, grid);
		}

		return Backend.User.Add.prototype.Instances[groupID];
	},

	initialize: function(groupID, grid)
  	{
		this.groupID = groupID;

		this.findUsedNodes();
		this.bindEvents();

		Backend.User.Editor.prototype.showShippingAddress.apply(this);

		Form.State.backup(this.nodes.form, false, false);
	},

	findUsedNodes: function()
	{
		this.nodes = {};
		this.nodes.parent = $("newUserForm_" + this.groupID);
		this.nodes.form = this.nodes.parent.down("form");
		this.nodes.cancel = this.nodes.parent.down('a.cancel');
		this.nodes.cancel2 = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');

		this.nodes.password = this.nodes.form.down('.user_password');
		this.nodes.showPassword = this.nodes.form.down('.user_password_show');
		this.nodes.generatePassword = this.nodes.form.down('.user_password_generate');

		this.nodes.menuShowLink = $("userGroup_" + this.groupID + "_addUser");
		this.nodes.menu = $("userGroup_" + this.groupID + "_addUser_menu");
		this.nodes.menuCancelLink = $("userGroup_" + this.groupID + "_addUserCancel");
		this.nodes.menuForm = this.nodes.parent;

		this.nodes.sameAddress = $("user_" + this.groupID + "_0_sameAddresses");
		this.nodes.shippingAddress = $("user_" + this.groupID + "_0_shippingAddress");
		this.nodes.billingAddress = $("user_" + this.groupID + "_0_billingAddress");
	},

	showAddForm: function()
	{
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.show("addUser", this.nodes.menuForm, ['billingAddress_countryID', 'shippingAddress_countryID'], function(){ Element.hide("userGroupsManagerContainer") });
	},

	hideAddForm: function()
	{
		if (window.ActiveForm)
		{
			var menu = new ActiveForm.Slide(this.nodes.menu);
			this.nodes.menuForm.hide();
			menu.hide("addUser", this.nodes.menuForm, function(){ Element.show("userGroupsManagerContainer") });
		}
	},

	bindEvents: function(args)
	{
		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancelForm()}.bind(this));
		Event.observe(this.nodes.cancel2, 'click', function(e) { Event.stop(e); this.cancelForm()}.bind(this));
		Event.observe(this.nodes.submit, 'click', function(e) { Event.stop(e); this.submitForm()}.bind(this));
		Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); this.cancelForm();}.bind(this));
		Event.observe(this.nodes.sameAddress, "click", function(e) { Backend.User.Editor.prototype.showShippingAddress.apply(this) }.bind(this));
		Event.observe(this.nodes.showPassword, "click", function(e) { this.togglePassword(this.nodes.showPassword.checked) }.bind(this));
		Event.observe(this.nodes.generatePassword, "click", function(e) { Event.stop(e); this.generatePassword() }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("shippingAddress_firstName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("shippingAddress_lastName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("shippingAddress_companyName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("billingAddress_firstName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("billingAddress_lastName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
		Event.observe(this.nodes.form.elements.namedItem("billingAddress_companyName"), "focus", function(e) { Backend.User.Add.prototype.setDefaultValues.apply(this); }.bind(this));
	},

	generatePassword: function()
	{
		this.nodes.password.value = "";
		new LiveCart.AjaxRequest(
			Backend.User.Editor.prototype.Links.generatePassword,
			this.nodes.parent.down(".generatePasswordProgressIndicator"),
			function(response)
			{
				setTimeout(function()
				{
					Backend.User.Add.prototype.togglePassword.apply(this, [true]);
					this.nodes.password.value = response.responseText;
				}.bind(this), 50);
			}.bind(this)
		);
	},

	togglePassword: function(show)
	{
		this.nodes.showPassword.checked = show;
		this.nodes.password.type = show ? 'text' : 'password';
	},

	setDefaultValues: function()
	{
		$A(["billingAddress", "shippingAddress"]).each(function(type)
		{
			var allFieldsAreEmpty = true;
			$A(this.nodes.parent.down(".user_" + type).getElementsByTagName("input")).each(function(input)
			{
				if(input.value && input.type != 'hidden')
				{
					allFieldsAreEmpty = false;
					throw $break;
				}
			});

			if(allFieldsAreEmpty)
			{
				this.nodes.form.elements.namedItem(type + "_firstName").value = this.nodes.form.elements.namedItem("firstName").value;
				this.nodes.form.elements.namedItem(type + "_lastName").value = this.nodes.form.elements.namedItem("lastName").value
				this.nodes.form.elements.namedItem(type + "_companyName").value = this.nodes.form.elements.namedItem("companyName").value;
			}
		}.bind(this));
	},

	showShippingAddress: function() {
		Backend.User.Editor.prototype.showShippingAddress.apply(this);
	},

	cancelForm: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form, false, false);
		this.hideAddForm();
	},

	submitForm: function()
	{
		if (!validateForm(this.nodes.form))
		{
			return false;
		}

		Backend.User.Editor.prototype.cloneBillingFormValues(this.nodes.sameAddress, this.nodes.shippingAddress, this.nodes.billingAddress);

		this.nodes.form.action = Backend.User.Editor.prototype.Links.create;
		new LiveCart.AjaxRequest(
			this.nodes.form,
			false,
			function(responseJSON)
			{
			 	ActiveForm.prototype.resetErrorMessages(this.nodes.form);
			 	var responseObject = eval("(" + responseJSON.responseText + ")");
			 	this.afterSubmitForm(responseObject);
			}.bind(this)
		);
	},

	afterSubmitForm: function(response)
	{
		if(response.status == 'success')
		{
			window.usersActiveGrid[this.groupID].reloadGrid();
			Form.State.restore(this.nodes.form, false, false);
			this.hideAddForm();
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	}
}


Backend.User.StateSwitcher = Class.create();
Backend.User.StateSwitcher.prototype =
{
	countrySelector: null,
	stateSelector: null,
	stateTextInput: null,
	url: '',

	initialize: function(countrySelector, stateSelector, stateTextInput, url)
	{
		this.countrySelector = countrySelector;
		this.stateSelector = stateSelector;
		this.stateTextInput = stateTextInput;
		this.url = url;

		if (this.stateSelector.length > 0)
		{
			Element.show(this.stateSelector);
			Element.hide(this.stateTextInput);
		}
		else
		{
			Element.hide(this.stateSelector);
			Element.show(this.stateTextInput);
		}

		Event.observe(countrySelector, 'change', this.updateStates.bind(this));
	},

	updateStates: function(e, onComplete)
	{
		var url = this.url + '/?country=' + this.countrySelector.value;

		var self = this;
		new LiveCart.AjaxRequest(
			url,
			false,
			function(response)
			{
				var states = $H(eval('(' + response.responseText + ')'));

				self.updateStatesComplete(states, onComplete)
			}
		);

		var indicator = document.getElementsByClassName('progressIndicator', this.countrySelector.parentNode);
		if (indicator.length > 0)
		{
			this.indicator = indicator[0];
			Element.show(this.indicator);
		}

		this.stateSelector.length = 0;
		this.stateTextInput.value = '';
	},

	updateStatesComplete: function(states, onComplete)
	{
		if (!states.size())
		{
			Element.hide(this.stateSelector);
			Element.show(this.stateTextInput);
			this.stateTextInput.focus();
			this.stateSelector.options.length = 0;
		}
		else
		{
			this.stateSelector.options[this.stateSelector.length] = new Option('', '', true);

			states.each(function(state)
			{
				this.stateSelector.options[this.stateSelector.length] = new Option(state.value, state.key, false);
			}.bind(this));
			Element.show(this.stateSelector);
			Element.hide(this.stateTextInput);

			this.stateTextInput.value = ''

			this.stateSelector.focus();
		}

		if (this.indicator)
		{
			Element.hide(this.indicator);
		}

		if(onComplete)
		{
			onComplete();
		}
	}
}

Backend.UserQuickEdit =
{
	showOrderDetails: function(node)
	{
		node = $(node);
		// row last td contains full info html
		node.up("div",1).previous().innerHTML=node.down("td").siblings().pop().innerHTML;
	},

	togglePassword: function(node)
	{
		var input = $(node).up("div").getElementsByClassName("user_password")[0];
		input.type = node.checked ? "text" : "password";
		input.focus();
	},

	generatePassword: function(node, uri)
	{
		new LiveCart.AjaxRequest(uri,
			$(node).up("div").getElementsByClassName("user_password")[0],
			function(transport)
			{
				var
					container = $(this.node).up("div"),
					chekbox=container.getElementsByClassName("user_password_show")[0];
					
				container.getElementsByClassName("user_password")[0].value=transport.responseText;
				chekbox.checked = true;
				Backend.UserQuickEdit.togglePassword(chekbox);
			}.bind({node:node})
		);
	}
}