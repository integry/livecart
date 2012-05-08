/**
 *	@author Integry Systems
 */

if (Backend == undefined)
{
	var Backend = {}
}

Backend.Category = {

	/**
	 * category tab controll instance
	 */
	tabControl: null,

	/**
	 * Category tree browser instance
	 */
	treeBrowser: null,

	/**
	 * Id of currenty selected category. Used for category tab content switching
	 */
	activeCategoryId: null,

	links: {},

	/**
	 * Category module initialization
	 */
	init: function(categories)
	{
		this.initCategoryBrowser(categories);
		this.initTabs();
		this.initTreeControls();

		this.loadBookmarkedCategory();
		this.initPage();
		this.showControls();
	},

	initPage: function()
	{
		// check for bookmark
		if (!Backend.getHash())
		{
			window.location.hash = $('tabProducts') ?  'cat_1#tabProducts__' : 'cat_1#tabMainDetails__';
			Backend.Breadcrumb.display(1);
		}

		var elements = Backend.getHash().split('#');

		if (elements[1].substr(0, 4) == 'cat_')
		{
			var parts = elements[1].split('_');
			var categoryId = parts[1];
		}
		else
		{
			var categoryId = 1;
		}

		Backend.Category.activeCategoryId = categoryId;
		Backend.Category.selectItem(categoryId);

		return true;
	},

	initTreeControls: function()
	{
		jQuery("#createNewCategoryLink").click(function(e)
		{
			e.preventDefault();
			Backend.Category.createNewBranch();
		}.bind(this));

		jQuery("#removeCategoryLink").click(function(e)
		{
			e.preventDefault();
			if (confirm(Backend.Category.messages._confirm_category_remove))
			{
				Backend.Category.removeBranch();
			}
		}.bind(this));

		jQuery("#moveCategoryUp").click(function(e)
		{
			e.preventDefault();
			this.moveCategory(Backend.Category.activeCategoryId, 'up_strict');
		}.bind(this));

		jQuery("#moveCategoryDown").click(function(e)
		{
			e.preventDefault();
			this.moveCategory(Backend.Category.activeCategoryId, 'down_strict');
		}.bind(this));
	},

	/**
	 * Builds category tree browser object and initializes its params
	 */
	initCategoryBrowser: function(categories)
	{
		var self = this;
		categories.state = 'open';

		this.treeBrowser = jQuery('#categoryBrowser').jstree(
			{
				ui:
				{
					select_limit: 1,
					selected_parent_open: true,
					select_prev_on_delete: true
				},
				plugins: ['json_data', 'ui', 'themesz', 'dnd', 'crrm', 'themeroller'],
				json_data:
				{
					data: [categories],
					ajax:
					{
						url: function(node) { return Router.createUrl('backend.category', 'branch', {id: node.attr('id')}); }
					}
				},
				themeroller:
				{
					item: ''
				},
				themes:
				{
					theme: 'classic'
				}
			}).bind("select_node.jstree", function (event, data)
			{
				self.activateCategory(data.rslt.obj.attr("id"), data);
	        }).bind("load_node.jstree", function (event, data)
			{
				if (Backend.Category.afterLoad)
				{
					Backend.Category.afterLoad();
					Backend.Category.afterLoad = null;
				}
	        }).bind("move_node.jstree", function (event, data)
	        {
				Backend.Category.reorderCategory(Backend.Category.getNodeId(data.rslt.o), Backend.Category.getNodeId(data.rslt.r));
	        });

		Backend.Breadcrumb.setTree(this.treeBrowser);
	},

	initTabs: function()
	{
		this.tabControl = new CategoryTabControl(this.treeBrowser, 'tabList', 'sectionContainer', 'image/indicator.gif');
	},

	getSelectedId: function()
	{
		return this.treeBrowser.jstree('get_selected').attr('id');
	},

	getParentId: function(id)
	{
		if (!id)
		{
			return null;
		}

		return this.getTreeInst()._get_parent(jQuery('#' + id)).attr('id');
	},

	getNodeId: function(node)
	{
		return jQuery(node).attr('id');
	},

	selectItem: function(id)
	{
		return this.treeBrowser.jstree('select_node', '#' + id);
	},

	getTreeInst: function()
	{
		return this.treeBrowser.jstree('get_parent', '');
	},

	getNode: function(categoryID)
	{
		return this.treeBrowser.find('#' + categoryID);
	},

	showControls: function()
	{
		// popup window
		if (!$("categoryBrowserActions"))
		{
			return false;
		}

		var categoryId = Backend.Category.getSelectedId();

		if(categoryId == '1')
		{
			if ($("removeCategoryLink"))
			{
				$("removeCategoryLink").parentNode.hide();
			}

			if ($("moveCategoryUp"))
			{
				$("moveCategoryUp").parentNode.hide();
				$("moveCategoryDown").parentNode.hide();
			}
		}
		else
		{
			if ($("removeCategoryLink"))
			{
				$("removeCategoryLink").parentNode.show();
			}

			if ($("moveCategoryUp"))
			{
				if(Backend.Category.getParentId(categoryId))
				{
					var inst = this.getTreeInst();
					jQuery("#moveCategoryDown").parent().toggle(inst._get_next(this.getNode(categoryId), true) != false);
					jQuery("#moveCategoryUp").parent().toggle(inst._get_prev(this.getNode(categoryId), true) != false);
				}
			}
		}
	},

	/**
	 * Tree browser onClick handler. Activates selected category by realoading active
	 * tab with category specific data
	 *
	 */
	activateCategory: function(categoryId)
	{
		Backend.Product.hideAddForm();
		Backend.Breadcrumb.display(categoryId);

		Backend.Category.showControls();

		if(Backend.Product) Backend.Product.Editor.prototype.showCategoriesContainer();

		if (Backend.Category.activeCategoryId == categoryId)
		{
			return false;
		}

		Backend.Category.tabControl.switchCategory(categoryId, Backend.Category.activeCategoryId);
		Backend.Category.activeCategoryId = categoryId;

		// and register browser history event to enable backwar/forward navigation
		Backend.ajaxNav.add('cat_' + categoryId);

		if (!Backend.Category.tabControl.activeTab)
		{
			var tabList = $('tabList');
			if (tabList)
			{
				Backend.Category.tabControl.activeTab = tabList.down('li.tab');
			}
		}

		if (!Backend.Category.tabControl.activeTab)
		{
			return false;
		}

		Backend.Category.tabControl.activeTab.onclick();

		var currentProductId = Backend.Product.Editor.prototype.getCurrentProductId();
		if(currentProductId)
		{
			Backend.Product.Editor.prototype.getInstance(currentProductId, false).removeTinyMce();
		}
	},

	createNewBranch: function()
	{
		new LiveCart.AjaxRequest(
			this.getUrlForNewNode(Backend.Category.getSelectedId()),
			false,
			function(response) { this.afterNewBranchCreated(response) }.bind(this)
		);
	},

	moveCategory: function(categoryID, direction)
	{
		var node = this.getNode(categoryID);
		if('up_strict' == direction)
		{
			var sibling = this.getTreeInst()._get_prev(node, true);
			this.getTreeInst().move_node(node, sibling, 'before');
			Backend.Category.treeBrowser._reorderDirection = 'left';
		}
		else
		{
			var sibling = this.getTreeInst()._get_next(node, true);
			this.getTreeInst().move_node(node, sibling, 'after');
			Backend.Category.treeBrowser._reorderDirection = 'right';
		}

		this.reorderCategory(categoryID, this.getParentId(categoryID));
		Backend.Category.treeBrowser._reorderDirection = false;

		Backend.Category.showControls();
	},

	afterNewBranchCreated: function(response)
	{
		var newCategory = eval('(' + response.responseText + ')');
		var parentCategoryId = Backend.Category.getSelectedId();

		var parent = jQuery('#' + parentCategoryId);
		this.getTreeInst().create_node(parent, 'last',
			{
				attr: {'class': 'ui-state-disabled', id: newCategory.ID},
				state: 'leaf',
				data: newCategory.name_lang
			});

		var self = this;
		var tree = this.getTreeInst();

		var open = function()
		{
			tree.open_node(parent);
			self.treeBrowser.jstree('deselect_all');
			self.selectItem(newCategory.ID);
			self.tabControl.activateTab("tabMainDetails");
		}

		if (!tree._is_loaded(parent))
		{
			Backend.Category.afterLoad = open;
			tree.load_node(parent);
		}
		else
		{
			open();
		}
	},

	/**
	 * Updating category branch via ajax request
	 */
	updateBranch: function(formObj)
	{
		new LiveCart.AjaxRequest(formObj, null, this.afterBranchUpdate.bind(this));
	},

	/**
	 * Post-processing request
	 */
	afterBranchUpdate: function(response)
	{
		var categoryData = eval('(' + response.responseText + ')');

		var node = jQuery('#' + categoryData.ID);
		node.toggleClass('ui-state-disabled', categoryData.isEnabled == 0);
		this.getTreeInst().rename_node(node, categoryData.name_lang);
	},

	/**
	 * Gets an URL for creating a new node (uses a globaly defined variable "newNodeUrl")
	 */
	getUrlForNewNode: function(parentNodeId)
	{
		return this.buildUrl(this.links.create, parentNodeId);
	},

	getUrlItemsInTabsCount: function(categoryId)
	{
		return this.buildUrl(Backend.Category.links.countTabsItems, categoryId);
	},

	getUrlForNodeRemoval: function(nodeId)
	{
		return this.buildUrl(this.links.remove, nodeId);
	},

	getUrlForNodeReorder: function(id, pid, direction)
	{
		direction = direction || '';
		return Backend.Category.links.reorder
				.replace('_id_', id)
				.replace('_pid_', pid)
				.replace('_direction_', direction);
	},

	buildUrl: function(urlPattern, id)
	{
		return urlPattern.replace('_id_', id);
	},

	/**
	 * Removes a selected category (including sub-trees) from a store
	 */
	removeBranch: function()
	{
		var nodeIdToRemove = this.getSelectedId();
		var parentNodeId = this.getParentId(nodeIdToRemove);

		new LiveCart.AjaxRequest(this.getUrlForNodeRemoval(nodeIdToRemove), null,
			function(transport)
			{
				if(transport.responseData.status == "confirm" && confirm(transport.responseData.confirmMessage))
				{
					new LiveCart.AjaxRequest(transport.responseData.url, null,
						function(transport)
						{
							if(transport.responseData.status == "success")
							{
								this.successCallback();
							}
						}.bind(this)
					);
				}
				else if(transport.responseData.status == "success")
				{
					this.successCallback();
				}
			}.bind({
				successCallback:function(nodeIdToRemove, parentNodeId)
				{
					return function()
					{
						this.removeBranchSuccess(nodeIdToRemove, parentNodeId)
					}.bind(this);
				}.bind(this)(nodeIdToRemove, parentNodeId)
			})
		);
	},

	removeBranchSuccess: function(nodeIdToRemove, parentNodeId)
	{
		this.treeBrowser.jstree('deselect_all');
		this.getTreeInst().delete_node(this.getNode(nodeIdToRemove));
		this.selectItem(parentNodeId);
	},

	reorderCategory: function(targetId, parentId)
	{
		if (!parentId)
		{
			return false;
		}

		var node = this.getNode(targetId).find('a').first();
		node.addClass('jstree-loading');
		new LiveCart.AjaxRequest(Backend.Category.getUrlForNodeReorder(targetId, parentId, Backend.Category.treeBrowser._reorderDirection), null, function()
		{
			node.removeClass('jstree-loading');
		});

		return true;
	},

	loadBookmarkedCategory: function()
	{
		var match = Backend.getHash().match(/cat_(\d+)/);

		if(match)
		{
			this.activeCategoryId = match[1];

			this.loadBranch(this.activeCategoryId);
		}
	},

	loadBranch: function(id, onload)
	{
		if (!jQuery('#' + id).length)
		{
			new LiveCart.AjaxRequest(Router.createUrl('backend.category', 'recursivePath', {id: id}), null, function(oR)
			{
				var node = Backend.Category.getNode(1);
				node.append(Backend.Category.getTreeInst()._parse_json(oR.responseData));

				if (onload)
				{
					onload();
				}
			});
		}
	},

	loadBookmarkedProduct: function()
	{
		var productID = Backend.getHash().match(/product_(\d+)/);
		if (productID && productID[1])
		{
			Element.show($('loadingProduct'));
			Backend.Product.openProduct(productID[1], null, function() { Element.hide($('loadingProduct')); });
		}
	}
}



var CategoryTabControl = Class.create();

/**
 * Category manager tab control
 */
CategoryTabControl.prototype = {

	tabItemsCounts: { },
	activeTab: null,
	indicatorImageName: null,
	treeBrowser: null,
	sectionContainerName: null,

	initialize: function(treeBrowser, tabContainerName, sectionContainerName, indicatorImageName)
	{
		this.treeBrowser = treeBrowser;
		Backend.Breadcrumb.setTree(this.treeBrowser);

		this.sectionContainerName = sectionContainerName;

		if (indicatorImageName != undefined)
		{
			this.indicatorImageName = indicatorImageName;
		}

		var tabList = document.getElementsByClassName("tab");

		jQuery('a', tabList).click(function(e) { e.preventDefault(); });

		for (var i = 0; i < tabList.length; i++)
		{
			tabList[i].onclick = this.handleTabClick;
			tabList[i].onmouseover = this.handleTabMouseOver.bindAsEventListener(this);
			tabList[i].onmouseout = this.handleTabMouseOut.bindAsEventListener(this);

			tabList[i].tabControl = this;

			aElementList = tabList[i].getElementsByTagName('a');
			if (aElementList.length > 0)
			{
				// Getting an URL pattern that tab is pointing to by analysing "<A>" element
				tabList[i].url = aElementList[0].href;
			}

			if (tabList[i].id == '')
			{
				tabList[i].id = 'tab' + i;
			}

			if (Element.hasClassName(tabList[i], 'active'))
			{
				this.activeTab = tabList[i];
				var containerId = this.getContainerId(tabList[i].id, Backend.Category.getSelectedId());
				if ($(containerId) != undefined)
				{
					Element.show(containerId);
				}
			}
			else
			{
				//Element.hide(this.getContainerId(tabList[i].id, treeBrowser.getSelectedItemId()));
			}

			var tab = tabList[i];
			if (!tab.down('.tabCounter'))
			{
				var firstLink = jQuery('a', tab);
				firstLink.html('<span class="tabName">' + firstLink.html() + '</span><span class="tabCounter"> </span>');
			}
		}

		// register for AJAX browser navigation handler
		//this.activeTab.onclick();
	},

	handleTabMouseOver: function(evt, out)
	{
		var out = out ? 1 : 0;
		var target = "";
		if (evt.target == undefined)
		{
			target = evt.srcElement;
		}
		else
		{
			target = evt.target;
		}

		target = jQuery(target).closest('li.tab')[0];

		if (this.activeTab != target)
		{
			var classes = ['inactive', 'hover ui-state-hover'];
			jQuery(target).removeClass(classes[out]);
			jQuery(target).addClass(classes[1 - out]);
		}
	},

	handleTabMouseOut: function(evt)
	{
		this.handleTabMouseOver(evt, 1);
	},

	/**
	 * Tab click event handler (performs tab styling and content activation)
	 */
	handleTabClick: function()
	{
		Backend.Category.selectItem(Backend.Category.activeCategoryId);
		this.tabControl.activateTab(this);

		Backend.ajaxNav.add('cat_' + Backend.Category.activeCategoryId + '#' + this.id, this.id);
	},

	/**
	 * Activates a given tab of currenty selected category
	 */
	activateTab: function(targetTab)
	{
		targetTab = $(targetTab);

		var tab = targetTab;

		var categoryId = Backend.Category.getSelectedId();
		if (!categoryId)
		{
			categoryId = Backend.Category.activeCategoryId;
		}

		this.updateTabItemsCount(categoryId);

		$(this.sectionContainerName).childElements().invoke("hide");

		// get help context
		var helpContext = document.getElementsByClassName('tabHelp', targetTab);
		if (helpContext.length > 0)
		{
			Backend.setHelpContext(helpContext[0].firstChild.nodeValue);
		}

		var tabId = targetTab.id;

		if (this.activeTab == targetTab)
		{
			var containerId = this.getContainerId(targetTab.id, categoryId)
			if ($(containerId) != undefined)
			{
				if (!Element.empty(containerId))
				{
					Element.show(this.getContainerId(targetTab.id, categoryId));
					return;
				}
			}
		}

		if (this.activeTab != null)
		{
			jQuery(this.activeTab).removeClass('active ui-tabs-selected ui-state-active');
			jQuery(this.activeTab).addClass('inactive');
			var activeContainerId = this.getContainerId(this.activeTab.id, categoryId);
			if ($(activeContainerId) != undefined)
			{
				Element.hide(activeContainerId);
			}
		}

		this.activeTab = targetTab;
		jQuery(this.activeTab).removeClass('hover ui-state-hover');
		jQuery(this.activeTab).addClass('active ui-tabs-selected ui-state-active');

		this.loadTabContent(tabId, categoryId);

		$(this.sectionContainerName).immediateDescendants().each(function(container)
		{
			container.hide();
		});

		Element.show(this.getContainerId(this.activeTab.id, categoryId));
	},

	loadTabContent: function(tabId, categoryId)
	{
		var containerId = this.getContainerId(tabId, categoryId);

		if ($(containerId) == undefined)
		{
			new Insertion.Bottom(this.sectionContainerName, '<div class="' + tabId + ' tabPageContainer" id="' + containerId + '"><div class="indicatorContainer"><img src="' + TabControl.prototype.indicatorImageName + '" /></div></div>');
		}

		if (categoryId != "" && (Element.empty(containerId) || jQuery('.indicatorContainer', jQuery(containerId))))
		{
			new LiveCart.AjaxUpdater(
				this.getTabUrl(tabId, categoryId),
				this.getContainerId(tabId, categoryId),
				null,
				undefined,
				function()
				{
					if ('tabMainDetails' == tabId)
					{
						var nameField = $(containerId).down('form').elements.namedItem('name');
						if ('New Category ' == nameField.value.substr(0, 13))
						{
							nameField.select();
							nameField.focus();
						}
					}
				}
			);
		}
	},

	getContainerId: function(tabName, categoryId)
	{
		return tabName + 'Content_' + categoryId;
	},

	getTabUrl: function(tabName, categoryId)
	{
		var url = $(tabName).url.replace('_id_', categoryId);
		return url;
	},

	/**
	 * Reset content related to a given tab. When tab will be activated content must
	 * be resent
	 */
	resetContent: function(tabObj, categoryId)
	{
		var contentContainerId = this.getContainerId(tabObj.id, categoryId);
		if ($(contentContainerId) != undefined)
		{
			$(contentContainerId).innerHTML = '';
			Element.hide(contentContainerId);
		}
	},

	reloadActiveTab: function()
	{
		categoryId = Backend.Category.getSelectedId();;
		this.resetContent(this.activeTab, categoryId);
		this.activateTab(this.activeTab, categoryId);
	},

	switchCategory: function(currentCategory, previousActiveCategoryId)
	{
		if (previousActiveCategoryId != null && this.activeTab)
		{
			var prevContainer = this.getContainerId(this.activeTab.id, previousActiveCategoryId);
			if ($(prevContainer) != undefined)
			{
				Element.hide(prevContainer);
			}
		}
	},

	loadCategoryTabsCount: function(categories)
	{
		$H(categories).each(function(category) {
			CategoryTabControl.prototype.tabItemsCounts[category.key] = category.value;
		});
	},

	updateTabItemsCount: function(categoryID)
	{
		if(categoryID != "" && !CategoryTabControl.prototype.tabItemsCounts[categoryID])
		{
			new LiveCart.AjaxRequest(
				Backend.Category.getUrlItemsInTabsCount(categoryID),
				false,
				function(response)
				{
					CategoryTabControl.prototype.tabItemsCounts[categoryID] = eval("(" + response.responseText + ")");
					CategoryTabControl.prototype.setTabItemsCount(categoryID);
				}
			);
		}
		else
		{
			CategoryTabControl.prototype.setTabItemsCount(categoryID);
		}
	},

	setTabItemsCount: function(categoryID)
	{
		$H(CategoryTabControl.prototype.tabItemsCounts[categoryID]).each(function(tab) {
			if ($(tab.key))
			{
				jQuery('span.tabCounter', $(tab.key)).html(tab.value);
			}
		});
	},

	resetTabItemsCount: function(categoryID)
	{
		CategoryTabControl.prototype.tabItemsCounts[categoryID] = null;
		CategoryTabControl.prototype.updateTabItemsCount(categoryID);
	},

	getActiveTab: function()
	{
		return this.activeTab;
	},

	setTabUrl: function(tabId, url)
	{
		$('tabId').url = url;
	}
}


Backend.Category.PopupSelector = Class.create();
Backend.Category.PopupSelector.prototype =
{
	onAccept: null,
	onCancel: null,

	initialize: function(onAccept, onCancel, categoryID)
	{
		this.onAccept = onAccept;
		this.onCancel = onCancel;

		if (!Backend.Category.links.popup)
		{
			Backend.Category.links.popup = Backend.Router.createUrl('backend.category', 'popup');
		}

		var w = window.open(Backend.Category.links.popup + (categoryID ? '#cat_' + categoryID : ''), 'selectCategory', 'width=260, height=450');

		this.window = w;

		window.popupOnload =
			function()
			{
				Event.observe(w.document.getElementById('select'), 'click', function()
					{
						var cat = w.Backend.Category;
						var pathAsText = cat.getTreeInst().get_path(cat.getNode(cat.getSelectedId())).join(' > ');

						var res = true;

						if (this.onAccept)
						{
							res = this.onAccept(cat.getSelectedId(), pathAsText, {}, w);
						}

						if (res)
						{
							w.close();
						}

					}.bindAsEventListener(this) );

				Event.observe(w.document.getElementById('cancel'), 'click', function(e)
					{
						var res = true;

						if (this.onCancel)
						{
							res = this.onCancel(this);
						}

						if (res)
						{
							w.close();
						}

						Event.stop(e);
					}.bindAsEventListener(this) );
			}.bind(this);

		// close the popup automatically if closing/reloading page
		Event.observe(window, 'unload', function()
		{
			w.close();
		});
	}
}
