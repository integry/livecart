var BackendToolbar = Class.create();
BackendToolbar.prototype = {
	nodes : {},
	// draggable menu items contains <a> tags, clicking on them reloads page
	// this flag is used for workround
	draggingItem: false,

	initialize: function(rootNode, urls)
	{
		this.urls = urls;
		this.nodes.root = $(rootNode);
		this.nodes.mainpanel = this.nodes.root.down("ul");

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
				Event.observe($(element).down("a"), "click",
					function(event) {
						if (this.draggingItem)
						{
							// drag fires only one click event, setting flag to false will allow next clicks on menu to operate normally
							this.draggingItem = false;
							Event.stop(event);
						}
					}.bindAsEventListener(this)
				);

				new Draggable(
					$(element),
					{
						onStart: function(inst)
						{
							var
								element = $(inst.element),
								ul = element.up("ul");

							if (ul)
							{
								// if parent node is hidden draggable item disapears,
								// this force menu to stay open while dragging
								ul.addClassName("importantVisible");
							}
							this.draggingItem = true;
						}.bind(this),

						onEnd: function(inst, event)
						{
							var
								element = $(inst.element),
								ul = element.up("ul");
							if (ul)
							{
								ul.removeClassName("importantVisible");
							}
						},
						change: function()
						{
							// console.log(arguments);
						},
						ghosting:true,
						revert:true,
						zindex:9999
					}
				);
			}.bind(this)
		);

		// init unitialized drop buttons

		dropButtons = $A(this.nodes.mainpanel.getElementsByClassName("uninitializedDropButton"));
		dropButtons.each(this.fillDropButtonWithData.bind(this));

		this.updateDroppables();
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
		new Draggable(node, {
			ghosting:true,
			revert:true,
			onEnd:
				function(from, to, event)
				{
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

	registerViewedItem: function(group, name, url)
	{
		
	},

	updateViewedItems: function()
	{
		
	},

	getButtonPosition: function(node)
	{
		return $(node).previousSiblings().length - 2; // !! note: will be broken, if some 'no-dropButton' node added/removed before dropButton section.
	},

	addIcon: function(li, insertBeforeLi)
	{
		// 1. add icon
		// 2. send ajax update
		// 3. if adding icon failed -remove

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
			this.urls.addIcon.replace("_id_", node.id.replace("button", "")).replace("_position_",this.getButtonPosition(node)),
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

		if (confirm(Backend.getTranslation("_remove_button_from_toolbar").replace("[_1]", menuItem.title)))
		{
			new LiveCart.AjaxRequest
			(
				this.urls.removeIcon.replace("_id_", id).replace("_position_",this.getButtonPosition(node)),
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
		}
	},

	getMenuItem: function(id)
	{
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
		new LiveCart.AjaxRequest(this.urls.sortIcons.replace('_order_', r.join(",")), null);
	},

	updateIconList: function()
	{
		
	}
}
