var BackendToolbar = Class.create();
BackendToolbar.prototype = {

	isBackend: true,

	nodes: function()
	{
		this.nodes.lastviewed = this.nodes.root.down(".lastviewed");
		this.nodes.lastViewedIndicator = $("lastViewedIndicator");
		this.nodes.quickSearchResult = $("TBQuickSearchResultOuterContainer");
		this.nodes.quickSearchQuery = $("TBQuickSearchQuery");
	},

	afterInit: function()
	{
		// remove button from toolbar, if it is droped outside any droppable area
		Droppables.add($(document.body), {
			onDrop: function(from, to, event) {
				from = $(from);
				if (from.hasClassName("dropButton"))
				{
					this.removeIcon(from);
				}
			}.bind(this)
		});
		// --

		$A($("navContainer").getElementsByTagName("li")).each(
			function(element)
			{
				element = $(element);
				var
					a = element.down("a"),
					menuItem = this.getMenuItem(a.id);
				if (!menuItem || typeof menuItem.url == "undefined" || menuItem.url == "")
				{
					return; // menu items without url are not draggable!
				}
				Event.observe(a, "click", this.cancelClickEventOnDrag.bindAsEventListener(this));
				jQuery(element).draggable(
					{
						start: function(event, ui)
						{
							var
								element = $(ui.helper[0]),
								ul = element.up("ul");
							this.draggingItem = true;
							if (ul)
							{
								ul.addClassName("importantVisible"); // make sure draggable item stay visible while dragging.
							}
						}.bind(this),

						end: function(event, ui)
						{
							var
								element = $(ui.helper[0]),
								ul = element.up("ul");
							if (ul)
							{
								ul.removeClassName("importantVisible");
							}
						},
						revert: true,
						opacity: 0.35,
						zIndex: 9999
					}
				);
			}.bind(this)
		);

		// init unitialized drop buttons
		dropButtons = $A(this.nodes.mainpanel.getElementsByClassName("uninitializedDropButton"));
		dropButtons.each(this.fillDropButtonWithData.bind(this));
		this.updateDroppables();

		// -- last viewed
		this.adjustPanel(this.nodes.lastviewed);
		Event.observe(window, "resize", function()
			{
				this.adjustPanel(this.nodes.lastviewed);
			}.bind(this)
		);
		Event.observe(document.body, "click",this.hideLastViewedMenu.bind(this));
		Event.observe(this.nodes.lastviewed.down("a"), "click", function(event) {
			event.preventDefault();
			this[["hideLastViewedMenu", "openLastViewedMenu"][this.nodes.lastviewed.down("a").hasClassName("active")?0:1]]();
		}.bindAsEventListener(this));

		// quick search result bottom relative to toolbar
		Backend.QuickSearch.getInstance(this.nodes.quickSearchResult).onShowResultContainer(this.adjustQuickSearchResult.bind(this));

		Event.observe(this.nodes.quickSearchQuery, "focus", this.hideLastViewedMenu.bind(this));

	},

	openLastViewedMenu: function()
	{

		this.nodes.quickSearchResult.hide();
		if (this.nodes.lastviewed.hasClassName("invalid"))
		{
			$A(this.nodes.lastviewed.getElementsBySelector("ul li")).each(Element.remove);
			this.adjustPanel(this.nodes.lastviewed);
			this.nodes.lastViewedIndicator.show();
			this.nodes.lastViewedIndicator.addClassName("progressIndicator");


			new LiveCart.AjaxUpdater(
				this.getPorperty("lastViewed").replace("__where__", this.whereAmI()),
				this.nodes.lastviewed.down("ul"),
				this.nodes.lastViewedIndicator,
				false,
				function() {
					this.nodes.lastviewed.removeClassName("invalid");
					this.adjustPanel(this.nodes.lastviewed);
				}.bind(this)
			);
		}

		var a = this.nodes.lastviewed.down("a");
		a.addClassName("active");
		this.getSubPanels(this.nodes.lastviewed).each(
			function(subpanel) {
				$(subpanel).addClassName("importantVisible");
			}
		);
	},

	hideLastViewedMenu: function()
	{
		if(this.isBackend == false)
		{
			return;
		}
		var a = this.nodes.lastviewed.down("a");
		a.removeClassName("active");
		this.getSubPanels(this.nodes.lastviewed).each(
			function(subpanel) {
				$(subpanel).removeClassName("importantVisible");
			}
		);
	},

	fillDropButtonWithData: function(node)
	{
		var
			menuItem = this.getMenuItem(node.id),
			a = node.down("a"),
			node = $(node);

		a.href = menuItem.url;
		a.down("small").innerHTML = menuItem.title;
		a.innerHTML = menuItem.title + a.innerHTML;
		a.style.background = "url(" +menuItem.icon+") no-repeat center center";
		node.removeClassName("uninitializedDropButton");
		node.addClassName("dropButton");

		Event.observe(node.down("a"), "click", this.cancelClickEventOnDrag.bindAsEventListener(this));
		jQuery(node).draggable({
			ghosting:true,
			revert:true,
			start: function(event, ui)
			{
				this.draggingItem = true;
			}.bind(this)
		});
		node.show();
	},

	updateDroppables: function()
	{
		var droppTargets = $A(this.nodes.mainpanel.getElementsByClassName("dropButton")); // all buttons
		droppTargets.push(this.nodes.mainpanel) // + mainpanel (ul tag), if dropped outside button
		droppTargets.each(function(element){
			element = $(element);
			if(element.hasClassName("droppable"))
			{
				return;
			}

			Droppables.add(
				element,
				{
					onDrop: function(from, to, event)
					{
						from = $(from);
						if (from.hasClassName("dropButton"))
						{
							// dragging button,
							this.sortIcons(from, to);
						}
						else
						{
							this.addIcon(from, to);
						}
					}.bind(this)
				}
			);
			element.addClassName("droppable");
		}.bind(this));
	},

	getButtonPosition: function(node)
	{
		var position;

		$A($(node).up("ul").getElementsByClassName("dropButton")).find(
			function(item,i)
			{
				position = i;
				return node == item;
			}
		);
		return position;
	},

	addIcon: function(li, insertBeforeLi)
	{
		// 1. add icon
		// 2. send ajax update
		// 3. if adding icon failed -remove

		if ($('noToolbarButtons'))
		{
			$('noToolbarButtons').hide();
		}

		node = $("dropButtonTemplate").cloneNode(true);
		node.id="button"+$(li).down("a").id;
		if ($(insertBeforeLi).hasClassName("dropButton"))
		{
			this.nodes.mainpanel.insertBefore(node, insertBeforeLi);
		}
		else
		{
			this.nodes.mainpanel.appendChild(node);
		}
		this.fillDropButtonWithData(node);
		this.updateDroppables();
		new LiveCart.AjaxRequest(
			this.getPorperty("addIcon").replace("_id_", node.id.replace("button", "")).replace("_position_",this.getButtonPosition(node)),
			null,
			function(node, transport)
			{
				var responseData = eval("(" + transport.responseText + ")");
				if (responseData.status != "success")
				{
					node.parentNode.removeChild(node);
				}
			}.bind(this, node)
		);
	},

	removeIcon: function(node)
	{
		// todo: stop observing
		node = $(node);
		if (node.tagName.toLowerCase() != "li")
		{
			node = node.up("li");
		}

		var
			id = node.id.replace("button", ""),
			menuItem = this.getMenuItem(id);

		new LiveCart.AjaxRequest
		(
			this.getPorperty("removeIcon").replace("_id_", id).replace("_position_",this.getButtonPosition(node)),
			null,
			function(node, transport)
			{
				var responseData = eval("(" + transport.responseText + ")");
				if (responseData.status == "success")
				{
					node.parentNode.removeChild(node);
				}
			}.bind(this, node)
		);
	},

	getMenuItem: function(id)
	{
		if (id.length == 0)
		{
			return null;
		}

		// window.menuArray;
		chunks = id.split("_");
		item = window.menuArray[chunks[1]];
		if(chunks.length == 3)
		{
			item = item.items[chunks[2]];
		}
		return item;
	},

	sortIcons: function(li, sortBefore)
	{
		if ($(sortBefore).hasClassName("dropButton"))
		{
			this.nodes.mainpanel.insertBefore(li, sortBefore);
		}
		else
		{
			this.nodes.mainpanel.appendChild(li); // move to end
		}
		r = $A(this.nodes.mainpanel.getElementsByClassName("dropButton")).inject([], function(r, item) {
			r.push(item.id.replace("button", ""));
			return r;
		});
		new LiveCart.AjaxRequest(this.getPorperty("sortIcons").replace('_order_', r.join(",")), null);
	},

	adjustQuickSearchResult: function(quickSearch)
	{
		var height = quickSearch.nodes.Result.getHeight();
		quickSearch.nodes.Result.style.marginTop = "-" + (height + 40) + "px";
	},

	// footerToolbar.invalidateLastViewed();
	invalidateLastViewed: function()
	{
		this.nodes.lastviewed.addClassName("invalid");
	},

	whereAmI: function()
	{
		var href = $("navSelected").down("a").href;

		if (href.match(/backend\.userGroup/))
		{
			return 'user';
		}
		else if (href.match(/backend\.customerOrder/))
		{
			return 'order';
		}
		else if (href.match(/backend\.category/))
		{
			return 'product';
		}
		return null;
	},

	tryToOpenItemWithoutReload: function(id, type)
	{
		var sectionName = this.whereAmI();

		if (type != sectionName)
		{
			return true; // can't open without reload.
		}

		if (sectionName == "product")
		{
			Element.show($('loadingProduct'));
			Backend.Product.openProduct(id, null, function() {
				Element.hide($('loadingProduct'));
			});
		}
		else if (sectionName == "order")
		{
			Element.show($('loadingOrder'));
			Backend.CustomerOrder.prototype.openOrder(id, null, function() {
				Element.hide($('loadingOrder'));
			});
		}
		else if (sectionName == "user")
		{
			Element.show($('loadingUser'));
			Backend.UserGroup.prototype.openUser(id, null, function() {
				Element.hide($('loadingUser'));
			});
		}
		else
		{
			return true;
		}

		return false; // block page reload
	}
}



BackendToolbar.prototype = Object.extend(FooterToolbar.prototype, BackendToolbar.prototype);
