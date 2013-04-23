/**
 *	@author Integry Systems
 */

Backend.CustomerOrder = Class.create();
Backend.CustomerOrder.prototype =
{
	Links: {},
	Messages: {},

	treeBrowser: null,

	urls: new Array(),

	initialize: function(groups)
	{
		ActiveGridAdvancedSearch.prototype.registerInitCallback
		(
			function(instance)
			{
			}
		);

		Backend.CustomerOrder.prototype.treeBrowser = new dhtmlXTreeObject("orderGroupsBrowser","","", 0);
		Backend.Breadcrumb.setTree(Backend.CustomerOrder.prototype.treeBrowser);

		Backend.CustomerOrder.prototype.treeBrowser.setOnClickHandler(this.activateGroup);

		Backend.CustomerOrder.prototype.treeBrowser.def_img_x = 'auto';
		Backend.CustomerOrder.prototype.treeBrowser.def_img_y = 'auto';

		Backend.CustomerOrder.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");

		Backend.CustomerOrder.prototype.treeBrowser.showFeedback =
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

		Backend.CustomerOrder.prototype.treeBrowser.hideFeedback =
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

		var orderID = Backend.getHash().match(/order_(\d+)/);
		if (orderID && orderID[1])
		{
			Element.show($('loadingOrder'));
			Backend.CustomerOrder.prototype.openOrder(orderID[1], null, function() {
				Element.hide($('loadingOrder'));
			});
		}
		else
		{
			if(!Backend.ajaxNav.getHash().match(/group_\d+#\w+/))
			{
				window.location.hash = 'group_1#tabOrders__';
			}
		}

		this.tabControl = TabControl.prototype.getInstance('orderGroupsManagerContainer', this.craftTabUrl, this.craftContainerId, {});

		Backend.CustomerOrder.prototype.instance = this;
	},

	createNewOrder: function(customerID)
	{
		var $this = this;
		new LiveCart.AjaxRequest(
			Backend.CustomerOrder.Links.createOrder + "?customerID=" + customerID,
			false,
			function(response)
			{
				response = eval("(" + response.responseText + ")");

				if('success' == response.status)
				{
					window.focus();

					setTimeout(function()
					{
						Backend.SelectPopup.prototype.popup.close();

						Backend.CustomerOrder.prototype.treeBrowser.selectItem(3, true, true);
						Backend.CustomerOrder.prototype.openOrder(response.order.ID);
						if(window.ordersActiveGrid[3])
						{
							window.ordersActiveGrid[3].reloadGrid();
						}
					}, 20);
				}
				else
				{
					 new Backend.SelectPopup.prototype.popup.Backend.SaveConfirmationMessage(Backend.SelectPopup.prototype.popup.$("userHasNoAddressError")); // show error in popup
				}
			}
		);
	},

	createUserOrder: function(customerID, feedbackElement, orderUrl)
	{
		var feedbackElement = feedbackElement.up('li').down('.progressIndicator');
		feedbackElement.show();

		new LiveCart.AjaxRequest(
			Backend.CustomerOrder.Links.createOrder + "?customerID=" + customerID,
			false,
			function(response)
			{
				response = response.responseData;

				if('success' == response.status)
				{
					 window.location.href = orderUrl + '#order_' + response.order.ID;
				}
				else
				{
					 feedbackElement.hide();
				}
			}
		);
	},

	craftTabUrl: function(url)
	{
		return url.replace(/_id_/, Backend.CustomerOrder.prototype.treeBrowser.getSelectedItemId());
	},

	craftContainerId: function(tabId)
	{
		return tabId + '_' +  Backend.CustomerOrder.prototype.treeBrowser.getSelectedItemId() + 'Content';
	},

	insertTreeBranch: function(treeBranch, rootId)
	{
		$A(treeBranch).each(function(node)
		{
			Backend.CustomerOrder.prototype.treeBrowser.insertNewItem(node.rootID, node.ID, node.name, null, 0, 0, 0, '', 1);
			this.treeBrowser.showItemSign(node.ID, 0);
			var group = document.getElementsByClassName("standartTreeRow", $("orderGroupsBrowser")).last().up('tr');
			group.id = 'group_' + node.ID;
			group.onclick = function()
			{
				Backend.CustomerOrder.prototype.treeBrowser.selectItem(node.ID, true);
			}
		}.bind(this));
	},

	activateGroup: function(id)
	{
		Backend.Breadcrumb.display(id);

		if(/*Backend.CustomerOrder.prototype.activeGroup && */Backend.CustomerOrder.prototype.activeGroup != id)
		{
			// Remove empty shippments
			var productsContainer = $("orderManagerContainer");
			if(productsContainer && productsContainer.style.display != 'none')
			{
				if(!Backend.CustomerOrder.Editor.prototype.getInstance(Backend.CustomerOrder.Editor.prototype.getCurrentId()).removeEmptyShipmentsConfirmation())
				{
					Backend.CustomerOrder.prototype.treeBrowser.selectItem(Backend.CustomerOrder.prototype.activeGroup, false);
					return;
				}
			}

			// Close popups
			if(window.selectPopupWindow)
			{
				window.selectPopupWindow.close();
			}

			Backend.CustomerOrder.prototype.activeGroup = id;
			Backend.CustomerOrder.prototype.treeBrowser.showFeedback(id);

			Backend.ajaxNav.add('group_' + id);

			Backend.CustomerOrder.prototype.instance.tabControl.activateTab('tabOrders', function() {
				Backend.CustomerOrder.prototype.treeBrowser.hideFeedback(id);
			});

			Backend.showContainer("orderGroupsManagerContainer");
		}

		Backend.CustomerOrder.prototype.activeGroup = id;
		Backend.ajaxNav.add('group_' + Backend.CustomerOrder.prototype.activeGroup + "#tabOrders");
	},

	displayCategory: function(response)
	{
		Backend.CustomerOrder.prototype.treeBrowser.hideFeedback();
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

	openOrder: function(id, e, onComplete)
	{
		if (e)
		{
			e.preventDefault();
			$('orderIndicator_' + id).style.visibility = 'visible';
		}

		Backend.CustomerOrder.Editor.prototype.setCurrentId(id);

		var tabControl = TabControl.prototype.getInstance(
			'orderManagerContainer',
			Backend.CustomerOrder.Editor.prototype.craftTabUrl,
			Backend.CustomerOrder.Editor.prototype.craftContentId
		);

		modifiedOnComplete = tabControl.activateTab(null,
			function(response)
			{
				if (onComplete)
				{
					onComplete(response);
				}

				Backend.CustomerOrder.prototype.orderLoaded = true;
				if (!Backend.getHash().match(/order_(\d+)/))
				{
					Backend.ajaxNav.add("order_" + id);
				}
				var stopRebillsLink = $("stopRebills"+id);
				if (stopRebillsLink && stopRebillsLink.hasClassName("observed") == false)
				{
					Event.observe(stopRebillsLink, "click", this.stopRebills.bindAsEventListener(this, id));
					stopRebillsLink.addClassName("observed");
				}
		}.bind(this));

		if (Backend.CustomerOrder.Editor.prototype.hasInstance(id))
		{
			Backend.CustomerOrder.Editor.prototype.getInstance(id);
		}

		Backend.showContainer("orderManagerContainer");
	},

	onStopRebillsSuccess: function(transport, orderID)
	{
		var node = $("stopRebills"+orderID), rsNode, rrNode, hasError=true;
		rsNode = $("recurringStatus"+orderID);
		rsNode.innerHTML = transport.responseData.recurringStatus;
		rrNode = $("remainingRebillsValue" + orderID);
		rrNode.innerHTML = transport.responseData.rebillCount;
		new Effect.Highlight(rrNode.up("div"));
		new Effect.Highlight(rsNode.up("div"));
		// Event.stopObserving(node, "click"); // ~ will work in 1.6
		node.hide();
	},

	onStopRebillsError: function(orderID)
	{
		var
			node = $("stopRebills"+orderID),
			pi = node.up(".stopRebillsLinkContainer").down("span");
		pi.addClassName("progressIndicator"); // resotre progress indicator on failure.
		pi.hide();
	},

	stopRebills: function(event, orderID)
	{
		event.preventDefault();
		if (confirm(Backend.getTranslation("_cancel_subscription_question").replace("[_1]", $("invoiceNumber"+orderID).innerHTML)))
		{
			new LiveCart.AjaxRequest($('cancelSubscriptionURL'+orderID).value, $('stopRebillsURL'+orderID).up(".stopRebillsLinkContainer").down("span"),
				function(orderID, transport) {
					var hasError = true;
					if (transport.responseData && transport.responseData.status)
					{
						if (transport.responseData.status == "success")
						{
							this.onStopRebillsSuccess(transport, orderID);
							hasError = false;
						}
						else if (transport.responseData.status == "confirmation_required")
						{
							if (confirm(transport.responseData.confirm))
							{
								new LiveCart.AjaxRequest($('stopRebillsURL'+orderID).value, $('stopRebillsURL'+orderID).up(".stopRebillsLinkContainer").down("span"),
									function(orderID, transport)
									{
										if (transport.responseData.status == "success")
										{
											this.onStopRebillsSuccess(transport, orderID);
										}
										else
										{
											this.onStopRebillsError(orderID);
										}
									}.bind(this, orderID)
								);
								hasError = false;
							}
						}
					}
					if (hasError)
					{
						this.onStopRebillsError(orderID);
					}
				}.bind(this, orderID)
			);
		}
	},

	updateLog: function(orderID)
	{
		this.resetTab("tabOrderLog", orderID);
	},

	changePaidStatus: function(input, url)
	{
		if (!confirm(Backend.getTranslation('_confirm_change_paid_status')))
		{
			input.checked = !input.checked;
			return false;
		}

		url = url.replace(/_stat_/, input.checked ? 1 : 0);

		new LiveCart.AjaxRequest(url, input,
			function(oReq)
			{
				input.up('.orderAmount').removeClassName('unpaid');
			}
		);
	},

	setMultiAddress: function(select, url, orderID)
	{
		url = url.replace(/_stat_/, select.value);
		new LiveCart.AjaxRequest(url, select.parentNode.down('.progressIndicator'), function()
		{
			window.location.reload();
			//this.resetTab('tabOrderInfo', orderID);
		}.bind(this));
	},

	resetTab: function(tab, orderID)
	{
		var url = $(tab).down('a').href.replace(/_id_/, orderID);
		var container = tab + "_" + orderID + "Content";
		var identificator = url + container;

		if($(container))
		{
			$(container).remove();
			TabControl.prototype.getInstance("orderManagerContainer").loadedContents[identificator] = false;
		}
	}
}

Backend.CustomerOrder.Links = {};
Backend.CustomerOrder.Messages = {};

Backend.CustomerOrder.GridFormatter =
{
	lastUserID: 0,

	orderUrl: '',

	formatValue: function(field, value, id)
	{
		if ('CustomerOrder.invoiceNumber' == field && Backend.CustomerOrder.prototype.ordersMiscPermission)
		{
			var displayedID = value ? value : id;

			value =
			'<span>' +
			'	<span class="progressIndicator" id="orderIndicator_' + id + '" style="visibility: hidden;"></span>' +
			'</span>' +
			'<a href="' + this.orderUrl + id + '#tabOrderInfo__" id="order_' + id + '" onclick="Backend.CustomerOrder.prototype.openOrder(' + id + ', event);">' +
				 displayedID
			'</a>'
		}
		else if ('CustomerOrder.invoiceNumber' == field && Backend.CustomerOrder.prototype.ordersMiscPermission)
		{
			value = value ? value : id;
		}
		else if('User.ID' == field)
		{
			Backend.CustomerOrder.GridFormatter.lastUserID = value;
		}

		if(value == '-')
		{
			value = "<center>" + value + "</center>";
		}

		return value;
	}
}

if (!Backend.User)
{
	Backend.User = {};
}

Backend.User.OrderGridFormatter = Object.clone(Backend.CustomerOrder.GridFormatter);
Backend.User.OrderGridFormatter.parentFormatValue = Backend.User.OrderGridFormatter.formatValue;
Backend.User.OrderGridFormatter.formatValue =
	function(field, value, id)
	{
		if ('CustomerOrder.invoiceNumber' == field)
		{
			var displayedID = value;

			return '<a href="' + this.orderUrl + id + '#tabOrderInfo__">' + displayedID + '</a>';
		}
		else
		{
			return this.parentFormatValue(field, value, id);
		}
	}


Backend.CustomerOrder.Editor = Class.create();
Backend.CustomerOrder.Editor.prototype =
{
	Links: {},
	Messages: {},
	Instances: {},
	CurrentId: null,

	STATUS_NEW: 0,
	STATUS_PROCESSING: 1,
	STATUS_AWAITING: 2,
	STATUS_SHIPPED: 3,
	STATUS_RETURNED: 4,

	getCurrentId: function()
	{
		return Backend.CustomerOrder.Editor.prototype.CurrentId;
	},

	setCurrentId: function(id)
	{
		Backend.CustomerOrder.Editor.prototype.CurrentId = id;
	},

	craftTabUrl: function(url)
	{
		return url.replace(/_id_/, Backend.CustomerOrder.Editor.prototype.getCurrentId());
	},

	craftContentId: function(tabId)
	{
		// Remove empty shippments
		if(tabId != 'tabOrderInfo')
		{
			var productsContainer = $("tabOrderInfo_" + Backend.CustomerOrder.Editor.prototype.getCurrentId() + "Content");
			if(productsContainer && productsContainer.style.display != 'none')
			{
				if(!Backend.CustomerOrder.Editor.prototype.getInstance(Backend.CustomerOrder.Editor.prototype.getCurrentId()).removeEmptyShipmentsConfirmation())
				{
					TabControl.prototype.getInstance("orderManagerContainer", false).activateTab($("tabOrderInfo"));
					return false;
				}
			}

			// close popups
			if(window.selectPopupWindow)
			{
				window.selectPopupWindow.close();
			}
		}

		return tabId + '_' +  Backend.CustomerOrder.Editor.prototype.getCurrentId() + 'Content'
	},

	getInstance: function(id, doInit, hideShipped, isCancelled, isFinalized, invoiceNumber)
	{
		if(!Backend.CustomerOrder.Editor.prototype.Instances[id])
		{
			Backend.CustomerOrder.Editor.prototype.Instances[id] = new Backend.CustomerOrder.Editor(id, hideShipped, isCancelled, isFinalized, invoiceNumber);
		}

		if (Backend.CustomerOrder.Editor.prototype.Instances[id].isCancelled)
		{
			$('orderManagerContainer').addClassName('cancelled');
		}
		else
		{
			$('orderManagerContainer').removeClassName('cancelled');
		}

		if (Backend.CustomerOrder.Editor.prototype.Instances[id].isFinalized)
		{
			$('orderManagerContainer').removeClassName('unfinalized');
		}
		else
		{
			$('orderManagerContainer').addClassName('unfinalized');
		}

		if(doInit !== false) Backend.CustomerOrder.Editor.prototype.Instances[id].init();

		return Backend.CustomerOrder.Editor.prototype.Instances[id];
	},

	hasInstance: function(id)
	{
		return this.Instances[id] ? true : false;
	},

	initialize: function(id, hideShipped, isCancelled, isFinalized, invoiceNumber)
	{
		if (window.footerToolbar)
		{
			try { footerToolbar.invalidateLastViewed(); } catch(e) {}
		}

		this.id = id ? id : '';

		this.hideShipped = hideShipped;
		this.isCancelled = isCancelled;
		this.isFinalized = isFinalized;
		this.invoiceNumber = invoiceNumber ? invoiceNumber : '(' + id + ')';

		this.findUsedNodes();
		this.bindEvents();

		this.toggleStatuses();

		Form.State.backup(this.nodes.form);
	},

	toggleStatuses: function()
	{
		var statusValue = parseInt(this.nodes.status.value);

		var migrations = {}
		migrations[this.STATUS_NEW]		 = [this.STATUS_NEW,this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_SHIPPED]
		migrations[this.STATUS_PROCESSING]  = [this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_SHIPPED]
		migrations[this.STATUS_AWAITING]	= [this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_SHIPPED]
		migrations[this.STATUS_SHIPPED]	 = [this.STATUS_SHIPPED,this.STATUS_RETURNED]
		migrations[this.STATUS_RETURNED]	= [this.STATUS_PROCESSING,this.STATUS_AWAITING,this.STATUS_RETURNED,this.STATUS_SHIPPED]

		$A(this.nodes.status.options).each(function(option) {
			if(migrations[statusValue].include(parseInt(option.value)))
			{
				Element.show(option);

				$$("#tabOrderInfo_" + this.id + "Content .shippableShipments select[name=status]").each(function(select)
				{
					$A(select.options).each(function(shipmentOption)
					{
						if(shipmentOption.value == option.value && shipmentOption.style.display == 'none')
						{
							Element.hide(option);
							throw $break;
						}
					}.bind(this));
				}.bind(this));
			}
			else
			{
				Element.hide(option);
			}


		}.bind(this));

		// If one shipment
		if($("orderShipments_list_" + this.id))
		{
			if($("orderShipments_list_" + this.id).childElements().size() == 1)
			{
				if(!$$("#orderShipments_list_" + this.id + " .orderShipment_USPS_select")[0].value)
				{
					this.hideShipmentStatus();
				}
			}
		}
		else if(this.hideShipped)
		{
			Element.hide(this.nodes.status.options[this.STATUS_SHIPPED]);
		}
	},

	hideShipmentStatus: function()
	{
		Element.hide(this.nodes.status.options[this.STATUS_SHIPPED]);
	},

	findUsedNodes: function()
	{
		this.nodes = {};
		this.nodes.parent = $("tabOrderInfo_" + this.id + "Content");
		this.nodes.addCoupon = $("order_" + this.id + "_addCoupon");

		// this.nodes.form = this.nodes.parent.down("form");
		this.nodes.form = $A(this.nodes.parent.getElementsByTagName("form")).find(function(f){return f.id != "calendarForm";});
		this.nodes.isCanceled = $("order_" + this.id + "_isCanceled");
		this.nodes.isCanceledIndicator = $("order_" + this.id + "_isCanceledIndicator");
		this.nodes.acceptanceStatusValue = $("order_acceptanceStatusValue_" + this.id);
		this.nodes.status = this.nodes.form.down('select.status');
		this.nodes.orderStatus = this.nodes.parent.down('.order_status');
		this.nodes.finalize = this.nodes.parent.down('.order_unfinalized');
	},

	bindEvents: function(args)
	{
		Event.observe(this.nodes.isCanceled, 'click', function(e) { e.preventDefault(); this.switchCancelled(); }.bind(this));
		Event.observe(this.nodes.status, 'change', function(e) { e.preventDefault(); this.submitForm(); }.bind(this));
		Event.observe(this.nodes.addCoupon, 'click', this.addCoupon.bindAsEventListener(this));
	},

	addCoupon: function(e)
	{
		e.preventDefault();
		var
			node = Event.element(e),
			code = prompt(Backend.Shipment.Messages.addCouponCode),
			indicator;
		if (code === null)
		{
			return;
		}
		indicator = $("order_"+this.id+"_addCouponIndicator");
		indicator.addClassName("progressIndicator");

		new LiveCart.AjaxRequest
		(
			node.href.replace("_coupon_", code),
			indicator,
			function(orderID, responseJSON)
			{
				var responseObject = eval("(" + responseJSON.responseText + ")");
				if (responseObject.status == 'success')
				{
					Backend.CustomerOrder.prototype.resetTab("tabOrderInfo", orderID);
					// 'invalidate' objects that may contain references to DOM nodes destroyed by previous line.
					Backend.CustomerOrder.Editor.prototype.Instances = {};
					Backend.Shipment.prototype.instances = {};
					ActiveList.prototype.activeListsUsers = {};

					Backend.CustomerOrder.prototype.openOrder(orderID);
				}
			}.bind(this, this.id)
		);
	},

	switchCancelled: function()
	{
		var message = this.nodes.form.elements.namedItem('isCancelled').value == 'true' ? Backend.CustomerOrder.Editor.prototype.Messages.areYouSureYouWantToActivateThisOrder : Backend.CustomerOrder.Editor.prototype.Messages.areYouSureYouWantToCancelThisOrder;
		if(!confirm(message)) return;

		new LiveCart.AjaxRequest(
			Backend.CustomerOrder.Editor.prototype.Links.switchCancelled + '/' + this.id,
			this.nodes.isCanceledIndicator,
			function(response) {
				response = response.responseData;

				if(response.status == 'success')
				{
					if (0 != response.isCanceled)
					{
						$('orderManagerContainer').addClassName('cancelled');
					}
					else
					{
						$('orderManagerContainer').removeClassName('cancelled');
					}

					Backend.CustomerOrder.Editor.prototype.Instances[this.id].isCancelled = response.isCanceled;

					this.nodes.isCanceled.up('li').className = response.isCanceled ? 'order_accept' : 'order_cancel';
					this.nodes.isCanceled.update(response.linkValue);
					this.nodes.acceptanceStatusValue.update(response.value);
					this.nodes.acceptanceStatusValue.style.color = response.isCanceled ? 'red' : 'green';
					this.nodes.form.elements.namedItem('isCancelled').value = response.isCanceled;
					Backend.CustomerOrder.prototype.updateLog(this.id);
				}
			}.bind(this)
		);
	},

	updateStatus: function()
	{
		this.form.submit();
	},

	init: function(args)
	{
	   Backend.Breadcrumb.setTree(Backend.CustomerOrder.prototype.treeBrowser);

		Backend.CustomerOrder.Editor.prototype.setCurrentId(this.id);
		var orderIndicator = $('orderIndicator_' + this.id);
		if(orderIndicator)
		{
			orderIndicator.style.visibility = 'hidden';
		}


		Backend.showContainer("orderManagerContainer");
		this.tabControl = TabControl.prototype.getInstance("orderManagerContainer", false);

		this.toggleStatuses();
		this.setPath();
	},

	setPath: function() {
		if(Backend.UserGroup.prototype.treeBrowser)
		{
			var currentUser = Backend.User.Editor.prototype.getInstance(Backend.User.Editor.prototype.CurrentId, false)

			Backend.Breadcrumb.display(
				Backend.UserGroup.prototype.activeGroup,
				[
					[currentUser.nodes.form.elements.namedItem("email").value, function(e)
					{
					   e.preventDefault();
					   Backend.hideContainer();
					   setTimeout(function()
					   {
						   Backend.Breadcrumb.display(
							   Backend.UserGroup.prototype.activeGroup,
							   this.nodes.form.elements.namedItem('email').value
						   );
					   }.bind(this), 20);

					}.bind(currentUser)],
					Backend.CustomerOrder.Editor.prototype.Messages.orderNum + this.invoiceNumber
				]

			);
		}
		else
		{
			if (Backend.Breadcrumb.treeBrowser.getSelectedItemId)
			{
				Backend.Breadcrumb.display(
					Backend.Breadcrumb.treeBrowser.getSelectedItemId(),
					Backend.CustomerOrder.Editor.prototype.Messages.orderNum + this.invoiceNumber
				);
			}
		}
	},

	cancelForm: function()
	{
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);

		if(!Backend.CustomerOrder.Editor.prototype.getInstance(Backend.CustomerOrder.Editor.prototype.getCurrentId(), false).removeEmptyShipmentsConfirmation())
		{
			Backend.CustomerOrder.prototype.treeBrowser.selectItem(Backend.CustomerOrder.prototype.activeGroup, true);
			return;
		}

		Backend.hideContainer();
		Form.restore(this.nodes.form);

		if(Backend.UserGroup.prototype.treeBrowser)
		{
			var currentUser = Backend.User.Editor.prototype.getInstance(Backend.User.Editor.prototype.CurrentId, true)
			currentUser.setPath();
		}
		else
		{
			Backend.Breadcrumb.display(Backend.CustomerOrder.prototype.activeGroup);

			Backend.ajaxNav.add('group_' + Backend.CustomerOrder.prototype.activeGroup + "#tabOrders");
		}

		// hide popups
		if(window.selectPopupWindow)
		{
			window.selectPopupWindow.close();
		}

		if (!Backend.CustomerOrder.prototype.activeGroup)
		{
			Backend.CustomerOrder.prototype.activeGroup = -1;
			Backend.CustomerOrder.prototype.treeBrowser.selectItem(1, true);
			Backend.CustomerOrder.prototype.instance.activateGroup(1);
		}
	},

	submitForm: function()
	{
		if(!confirm(Backend.CustomerOrder.Messages.areYouSureYouWantToUpdateOrderStatus))
		{
			Form.restore(this.nodes.form);
			return;
		}

		new LiveCart.AjaxRequest(
			this.nodes.form,
			this.nodes.form.down('select'),
			function(responseJSON)
			{
				try
				{
					var responseObject = eval("(" + responseJSON.responseText + ")");
				}
				catch(e)
				{
					var responseObject = {status: 'failure', response : {}};
				}

				this.afterSubmitForm(responseObject);
			}.bind(this)
		);
	},

	afterSubmitForm: function(response)
	{
		try { footerToolbar.invalidateLastViewed(); } catch(e) {}
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);

		if(response.status == 'success')
		{
			Backend.CustomerOrder.prototype.updateLog(this.id);

			var shippableShipments = $$("#tabOrderInfo_" + this.id + "Content .shippableShipments .orderShipment");
			var shippedShipments = $$("#tabOrderInfo_" + this.id + "Content .shippedShipments .orderShipment");
			var updateStatuses = function(li)
			{
				var statusValue = this.nodes.status.value;
				var instnace = Backend.Shipment.prototype.getInstance(li);
				instnace.nodes.status.value = statusValue;

				instnace.afterChangeStatus({status: "success"});
				this.nodes.status.value = statusValue;
			}.bind(this)

			if(this.nodes.status.value != Backend.CustomerOrder.Editor.prototype.STATUS_RETURNED)
			{
				shippableShipments.each(function(li){ updateStatuses(li); }.bind(this));
			}
			else if(shippableShipments.size() == 0)
			{
				shippedShipments.each(function(li) { updateStatuses(li); }.bind(this));
			}
			Form.State.backup(this.nodes.form);
		}
		else
		{
			Form.State.restore(this.nodes.form);
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}

		this.toggleStatuses();
	},

	removeEmptyShipmentsFromHTML: function()
	{
		new LiveCart.AjaxRequest(Backend.Shipment.Links.removeEmptyShipments + "/" + this.id);

		$$("#tabOrderInfo_" + this.id + "Content .shippableShipments .orderShipment").each(function(shipemnt)
		{
			 if(!shipemnt.down('li'))
			 {
				 Element.remove(shipemnt);
			 }
		});

		var shipments = $$("#tabOrderInfo_" + this.id + "Content .shippableShipments .orderShipment");
		if(shipments.size() == 1)
		{
			var firstItemsList = ActiveList.prototype.getInstance(shipments.first().down('ul'));
			Element.removeClassName(firstItemsList.ul, 'activeList_add_sort');
			firstItemsList.destroySortable();
		}

		Backend.CustomerOrder.Editor.prototype.getInstance(this.id, false).toggleStatuses();
		Backend.Shipment.prototype.updateOrderStatus(this.id);
	},


	hasEmptyShipments: function()
	{
		//
		if($("order" + this.id + "_shippableShipments").style.display == 'none')
		{
		  return false;
		}

		var hasEmptyShipments = false;
		$$("#tabOrderInfo_" + this.id + "Content .shippableShipments .orderShipment").each(function(itemList)
		{
			 if(!itemList.down('li'))
			 {
				 hasEmptyShipments = true;
				 return $break;
			 }
		})

		return hasEmptyShipments;
	},

	removeEmptyShipmentsConfirmation: function()
	{
		if(!Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime) Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = 0;

		var container = $$("#tabOrderInfo_" + this.id + "Content .orderShipments").first();

		if($("orderManagerContainer").style.display == 'none' || !container || (container.style.display == 'none'))
		{
			Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = (new Date()).getTime();
			return true;
		}
		else
		{
			if(!this.hasEmptyShipments())
			{
				Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = (new Date()).getTime();
				this.removeEmptyShipmentsFromHTML();
				return true;
			}

			// Confirm can be called few times in a row. To prevent multiple dialogs
			// rember user last decision
			if((new Date()).getTime() - Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime < 100)
			{
				return Backend.Shipment.removeEmptyShipmentsConfirmationLastUserDecision;
			}

			if(confirm(Backend.Shipment.Messages.emptyShipmentsWillBeRemoved))
			{
				Backend.Shipment.removeEmptyShipmentsConfirmationLastUserDecision = true;
				Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = (new Date()).getTime();

				this.removeEmptyShipmentsFromHTML();

				return true;
			}
			else
			{
				Backend.Shipment.removeEmptyShipmentsConfirmationLastUserDecision = false;
				Backend.Shipment.removeEmptyShipmentsConfirmationLastCallTime = (new Date()).getTime();
				return false;
			}
		}
	},

	resetEditors: function()
	{
		Backend.CustomerOrder.Editor.prototype.Instances = {};
		Backend.CustomerOrder.Editor.prototype.CurrentId = null;
		$('orderManagerContainer').down('.sectionContainer').innerHTML = '';
		TabControl.prototype.__instances__ = {};
	},

	toggleInvoicesTab: function()
	{
		$('tabOrderInvoices')[arguments[0] == true ? "show" : "hide"]();
	}
}


Backend.CustomerOrder.Address = Class.create();
Backend.CustomerOrder.Address.prototype =
{
	Links: {},
	Messages: {},
	Instances: {},

	getCurrentId: function()
	{
		return Backend.CustomerOrder.Address.prototype.CurrentId;
	},

	setCurrentId: function(id)
	{
		Backend.CustomerOrder.Address.prototype.CurrentId = id;
	},

	getInstance: function(root, doInit)
	{
		if(!Backend.CustomerOrder.Address.prototype.Instances[root.id])
		{
			Backend.CustomerOrder.Address.prototype.Instances[root.id] = new Backend.CustomerOrder.Address(root);
		}

		if(doInit !== false) Backend.CustomerOrder.Address.prototype.Instances[root.id].init();

		return Backend.CustomerOrder.Address.prototype.Instances[root.id];
	},

	hasInstance: function(root)
	{
		return this.Instances[root.id] ? true : false;
	},

	initialize: function(root, type)
  	{
		this.findUsedNodes(root);
		this.bindEvents();
		this.type = type;

		this.stateSwitcher = this.nodes.form.elements.namedItem('stateID').stateSwitcher;

		Form.State.backup(this.nodes.form);
		this.stateID = this.nodes.form.elements.namedItem('stateID').value;
	},

	findUsedNodes: function(root)
	{
		this.nodes = {};
		this.nodes.parent = root;
		this.nodes.form = root;
		this.nodes.cancel = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');

		this.nodes.cancelEdit = this.nodes.form.down('.order_cancelEditAddress');
		this.nodes.showEdit = this.nodes.form.down('.order_editAddress');
		this.nodes.view = this.nodes.form.down('.orderAddress_view');
		this.nodes.edit = this.nodes.form.down('.orderAddress_edit');
	},

	bindEvents: function(args)
	{
		Event.observe(this.nodes.cancel, 'click', function(e) { e.preventDefault(); this.cancelForm()}.bind(this));
		Event.observe(this.nodes.form.elements.namedItem('existingUserAddress'), 'change', function(e) { this.useExistingAddress()}.bind(this));
		Element.observe(this.nodes.showEdit, 'click', function(e) { e.preventDefault(); this.showForm(); }.bind(this));
		Element.observe(this.nodes.cancelEdit, 'click', function(e) { e.preventDefault(); this.hideForm(); }.bind(this));
	},

	showForm: function()
	{
		this.nodes.showEdit.hide();
		this.nodes.cancelEdit.show();
		this.nodes.view.hide();
		this.nodes.edit.show();

		$A(this.nodes.parent.up('.addressContainer').getElementsByTagName('form')).invoke('hide');
		this.nodes.parent.show();
	},

	hideForm: function()
	{
		this.nodes.showEdit.show();
		this.nodes.cancelEdit.hide();
		this.nodes.view.show();
		this.nodes.edit.hide();

		$A(this.nodes.parent.up('.addressContainer').getElementsByTagName('form')).invoke('show');
	},

	useExistingAddress: function()
	{
		if(this.nodes.form.elements.namedItem('existingUserAddress').value)
		{
			var address = Backend.CustomerOrder.Editor.prototype.existingUserAddresses[this.nodes.form.elements.namedItem('existingUserAddress').value];

			['firstName', 'lastName', 'countryID', 'stateID', 'stateName', 'city', 'address1', 'address2', 'postalCode', 'phone'].each(function(field)
			{
				if (address.UserAddress[field])
				{
					this.nodes.form.elements.namedItem(field).value = address.UserAddress[field];
				}
			}.bind(this));

			if (address.UserAddress.attributes)
			{
				$H(address.UserAddress.attributes).each(function(attr)
				{
					this.nodes.form.elements.namedItem('specField_' + attr[1].fieldID).value = attr[1].value;
				}.bind(this));
			}

			this.stateSwitcher.updateStates(null, function(){ this.nodes.form.elements.namedItem('stateID').value = address.UserAddress.stateID; }.bind(this));
		}
	},

	init: function(args)
	{
		Backend.CustomerOrder.Address.prototype.setCurrentId(this.id);
		var orderIndicator = $('orderIndicator_' + this.id);
		if(orderIndicator)
		{
			orderIndicator.style.visibility = 'hidden';
		}

		this.tabControl = TabControl.prototype.getInstance("orderManagerContainer", false);
	},

	cancelForm: function()
	{
		var $this = this;

		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.State.restore(this.nodes.form, ['existingUserAddress']);
		this.stateSwitcher.updateStates(null, function(){ $this.nodes.form.elements.namedItem('stateID').value = $this.stateID; });
		this.hideForm();
	},

	submitForm: function()
	{
		new LiveCart.AjaxRequest(
			this.nodes.form,
			false,
			function(responseJSON)
			{
				ActiveForm.prototype.resetErrorMessages(this.nodes.form);
				this.afterSubmitForm(responseJSON);
			}.bind(this)
		);
	},

	afterSubmitForm: function(response)
	{
		if(response.responseData && response.responseData.errors)
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.responseData.errors)
		}
		else
		{
			this.stateID = this.nodes.form.elements.namedItem('stateID').value;
			Form.State.backup(this.nodes.form);

			if (this.nodes.form.down('.addressCountryName'))
			{
				this.nodes.form.down('.addressCountryName').innerHTML = this.nodes.form.elements.namedItem('countryID').options[this.nodes.form.elements.namedItem('countryID').selectedIndex].text
			}

			if(this.nodes.form.elements.namedItem('stateID').options.length == 0)
			{
				this.nodes.form.down('.addressStateName').innerHTML = this.nodes.form.elements.namedItem('stateName').value
			}
			else
			{
				this.nodes.form.down('.addressStateName').innerHTML = this.nodes.form.elements.namedItem('stateID').options[this.nodes.form.elements.namedItem('stateID').selectedIndex].text
			}

			if (this.nodes.form.down('.addressFullName'))
			{
				this.nodes.form.down('.addressFullName').innerHTML = this.nodes.form.elements.namedItem('firstName').value + " " +this.nodes.form.elements.namedItem('lastName').value
			}

			$H({addressCompanyName: 'companyName', addressCity: 'city', addressAddress1: 'address1', addressAddress2: 'address2', addressPostalCode: 'postalCode', addressPhone: 'phone'}).each(function(field)
			{
				var el = this.nodes.form.down('.' + field[0]);
				var input = this.nodes.form.elements.namedItem(field[1]);

				if (el && input)
				{
					el.innerHTML = input.value;
				}
			}.bind(this));

			this.hideForm();

			Backend.CustomerOrder.prototype.updateLog(this.nodes.form.elements.namedItem('orderID').value);
		}
	}
}

Backend.CustomerOrder.CustomFields = Class.create();
Backend.CustomerOrder.CustomFields.prototype =
{
	orderId: null,
	container: null,
	form: null,

	initialize: function(orderId)
	{
		this.orderId = orderId;
		this.container = $('tabOrderInfo_' + orderId + 'Content').down('.customFields');
		this.form = this.container.down('form');

		Event.observe(this.container.down('.order_editFields').down('a'), 'click', this.showEditForm.bindAsEventListener(this));
		Event.observe(this.container.down('.order_cancelEditFields').down('a'), 'click', this.hideEditForm.bindAsEventListener(this));
		Event.observe(this.container.down('a.cancel'), 'click', this.hideEditForm.bindAsEventListener(this));
		Event.observe(this.form, 'submit', this.submitForm.bindAsEventListener(this));
	},

	showEditForm: function(e)
	{
		e.preventDefault();
		this.container.addClassName('editing');
	},

	hideEditForm: function(e)
	{
		e.preventDefault();
		this.container.removeClassName('editing');
		this.form.reset();
		ActiveForm.prototype.resetErrorMessages(this.form);
	},

	submitForm: function(e)
	{
		e.preventDefault();
		if (!validateForm(this.form))
		{
			return false;
		}

		new LiveCart.AjaxUpdater(this.form, this.container, null, null, this.submitComplete.bind(this));
	},

	submitComplete: function()
	{
		this.container.removeClassName('editing');
	}
}

Backend.CustomerOrder.DateCompletedEditor = Class.create();
Backend.CustomerOrder.DateCompletedEditor.prototype =
{
	VIEW : 0,
	EDIT: 1,

	initialize: function()
	{
		this.viewContainer = $("dateCreatedLabel");
		this.editContainer = $("calendarForm");

		Event.observe($('editDateCompleted'), "click", function(e) {
			e.preventDefault();
			this.toggle(this.EDIT);
		}.bind(this));

		Event.observe($('cancelDateCompleted'), "click", function(e) {
			e.preventDefault();
			this.toggle(this.VIEW);
		}.bind(this));

		Event.observe($('saveDateCompleted'), "click", function(e) {
			e.preventDefault();
			new LiveCart.AjaxRequest(
				this.editContainer.action,
				$("indicatorDateCompleted"),
				this.ajaxResponse.bind(this),
				this.collectParameters()
			);
		}.bind(this));
	},

	ajaxResponse: function(transport)
	{
		try {
			if(transport.responseData.status == "saved")
			{
				var dateField = $("dateCreatedVisible");
				dateField.innerHTML = transport.responseData.date;
				this.toggle(this.VIEW);
				new Effect.Highlight($(dateField.parentNode) );
				return;
			}
			throw "err";
		}
		catch(e)
		{
			// status not saved or response is corrupted.
		}
	},

	collectParameters: function()
	{
		return {
			parameters:$H({
				dateCompleted:$('dateCompleted_real').value,
				orderID: $A(document.getElementsByName("orderID")).shift().value
				}).toQueryString()
			};
	},

	toggle: function(type)
	{
		switch(type)
		{
			case this.EDIT:
				this.viewContainer.addClassName("hidden");
				this.editContainer.removeClassName("hidden");
				break;
			case this.VIEW:
				this.viewContainer.removeClassName("hidden");
				this.editContainer.addClassName("hidden");
				break;
		}
	}
}
