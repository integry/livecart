/**
 *	@author Integry Systems
 */

if (Backend == undefined)
{
	var Backend = {}
}

var oldLoadFunc = Backend.SpecField.prototype.loadSpecFieldAction;
Backend.SpecField.prototype.loadSpecFieldAction = function()
{
	var res = oldLoadFunc.apply(this, arguments);

	// users
	if (4 == this.categoryID)
	{
		var toggleFunc = function() { this.toggleCheckboxes(this.nodes.isRequired, [this.nodes.isDisplayed]); }.bind(this);
		toggleFunc();
		this.nodes.isRequired.onchange = toggleFunc;
		this.nodes.labels['isDisplayedInList'].innerHTML = Backend.getTranslation('_show_in_invoice');
	}
	// orders
	else if (2 == this.categoryID)
	{
		var requiredFunc = function() { this.toggleCheckboxes(this.nodes.isRequired, [this.nodes.isDisplayed, this.nodes.isDisplayedInList]); }.bind(this);
		requiredFunc();
		this.nodes.isRequired.onchange = requiredFunc;

		var editableFunc = function() { this.toggleCheckboxes(this.nodes.isDisplayed, [this.nodes.isDisplayedInList]); }.bind(this);
		editableFunc();
		this.nodes.isDisplayed.onchange = editableFunc;
	}

	return res;
}

Backend.SpecField.prototype.toggleCheckboxes = function(main, others)
{
	for (k = 0; k < others.length; k++)
	{
		var container = others[k].up('p');
		if (main.checked)
		{
			container.hide();
		}
		else
		{
			container.show();
		}

		others[k].checked = main.checked;
	}
}

Backend.CustomField = {

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
	init: function()
	{
		this.initCategoryBrowser();
		this.initTabs();
	},

	initPage: function()
	{
		// check for bookmark
		if (Backend.getHash().length == 0)
		{
			window.location.hash = 'cat_2#tabFields__';
		}

		Backend.CustomField.treeBrowser.showFeedback =
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

		Backend.CustomField.treeBrowser.hideFeedback =
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

		var elements = Backend.getHash().split('#');
		if (elements[1].substr(0, 4) == 'cat_')
		{
			var parts = elements[1].split('_');
			var categoryId = parts[1];

			Backend.CustomField.activeCategoryId = categoryId;
			Backend.CustomField.treeBrowser.selectItem(categoryId, false, false);

			return true;
		}

		if($('categoryBrowser').getElementsByClassName('selectedTreeRow')[0])
		{
			var treeNode = $('categoryBrowser').getElementsByClassName('selectedTreeRow')[0].parentNode;
			treeNode.onclick();
			Backend.ajaxNav.add('cat_' + treeNode.parentObject.id + '#tabFields');
		}
	},

	/**
	 * Builds category tree browser object (dhtmlxTree) and initializes its params
	 */
	initCategoryBrowser: function()
	{
		this.treeBrowser = new dhtmlXTreeObject("categoryBrowser","","", 0);

		Backend.CustomField.treeBrowser.setCategoryStyle =
			function(category)
			{
				this.setItemColor(category.ID, (category.isEnabled < 1 ? '#999' : '#000'), (category.isEnabled < 1 ? '#999' : '#fff'));
			}

		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setOnClickHandler(this.activateCategory);
	},

	initTabs: function()
	{
		this.tabControl = new CategoryTabControl(this.treeBrowser, 'tabList', 'sectionContainer', 'image/indicator.gif');
	},

	/**
	 * Tree browser onClick handler. Activates selected category by realoading active
	 * tab with category specific data
	 *
	 * @todo Find some better way to reference/retrieve the DOM nodes from tree by category ID's
	 * (automatically assign ID's somehow?). Also necessary for bookmarking (the ID's have to be preassigned).
	 */
	activateCategory: function(categoryId)
	{
		if (Backend.CustomField.activeCategoryId == categoryId)
		{
			return false;
		}

		Backend.CustomField.tabControl.switchCategory(categoryId, Backend.CustomField.activeCategoryId);
		Backend.CustomField.activeCategoryId = categoryId;

		// set ID for the current tree node element
		$('categoryBrowser').getElementsByClassName('selectedTreeRow')[0].parentNode.id = 'cat_' + categoryId;

		// and register browser history event to enable backwar/forward navigation
		// Backend.ajaxNav.add('cat_' + categoryId);
		if(Backend.CustomField.tabControl.activeTab) Backend.CustomField.tabControl.activeTab.onclick();
	},

	buildUrl: function(urlPattern, id)
	{
		return urlPattern.replace('_id_', id);
	},

	/**
	 * Insert array of categories into tree
	 *
	 * @param array categories Array of category objects. Every category object should contain these elements
	 *	 parent - Id of parent category
	 *	 ID - Id o category
	 *	 name - Category name in current language
	 *	 options - Advanced options
	 *	 childrenCount - Indicates that this node has N childs
	 */
	addCategories: function(categories, root)
	{
		root = root || 0;

		$A(categories).each(function(category)
		{
			category.options = "";

			// strip HTML
			category.name = '<b>' + category.name + '</b>';
			category.name = category.name.replace(/<(?:.|\s)*?>/g, "");

			this.treeBrowser.insertNewItem(root,category.ID,category.name, null, 0, 0, 0, category.options, 0);
			if (category.sub)
			{
				this.addCategories(category.sub, category.ID);
			}
		}.bind(this));
	}
}

var CategoryTabControl = Class.create();

/**
 * Category manager tab control
 */
CategoryTabControl.prototype = {

	activeTab: null,
	indicatorImageName: null,
	treeBrowser: null,
	sectionContainerName: null,

	initialize: function(treeBrowser, tabContainerName, sectionContainerName, indicatorImageName)
	{
		this.treeBrowser = treeBrowser;

		this.sectionContainerName = sectionContainerName;

		if (indicatorImageName != undefined)
		{
			this.indicatorImageName = indicatorImageName;
		}

		var tabList = document.getElementsByClassName("tab");
		for (var i = 0; i < tabList.length; i++)
		{
			tabList[i].onclick = this.handleTabClick;

			tabList[i].tabControl = this;

			aElementList = tabList[i].getElementsByTagName('a');
			if (aElementList.length > 0)
			{
				// Getting an URL pattern that tab is pointing to by analysing "<A>" element
				tabList[i].url = aElementList[0].href;
				new Insertion.After(aElementList[0], aElementList[0].innerHTML);
				// inserting indicator element which will be show on tab activation
				new Insertion.Before(aElementList[0], '<img src="' + this.indicatorImageName + '" class="tabIndicator" id="' + tabList[i].id + 'Indicator" alt="Tab indicator" style="display:none"/> ');
				Element.remove(aElementList[0]);
			}

			if (tabList[i].id == '')
			{
				tabList[i].id = 'tab' + i;
			}
			if (Element.hasClassName(tabList[i], 'active'))
			{
				this.activeTab = tabList[i];
				var containerId = this.getContainerId(tabList[i].id, treeBrowser.getSelectedItemId());
				if ($(containerId) != undefined)
				{
					Element.show(containerId);
				}
			}
			else
			{
				//Element.hide(this.getContainerId(tabList[i].id, treeBrowser.getSelectedItemId()));
			}
		}

		// register for AJAX browser navigation handler
		//this.activeTab.onclick();
	},

	/**
	 * Tab click event handler (performs tab styling and content activation)
	 */
	handleTabClick: function()
	{
		this.tabControl.activateTab(this);

		Backend.ajaxNav.add('cat_' + Backend.CustomField.activeCategoryId + '#' + this.id, this.id);
	},

	/**
	 * Activates a given tab of currenty selected category
	 */
	activateTab: function(targetTab, categoryIdToActivate)
	{
		targetTab = $(targetTab);

		var tab = targetTab;
		var id = categoryIdToActivate;

		var categoryId = (categoryIdToActivate == undefined ? this.treeBrowser.getSelectedItemId() : categoryIdToActivate);

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
			Element.removeClassName(this.activeTab, 'active');
			Element.addClassName(this.activeTab, 'inactive');
			var activeContainerId = this.getContainerId(this.activeTab.id, categoryId);
			if ($(activeContainerId) != undefined)
			{
				Element.hide(activeContainerId);
			}
		}

		this.activeTab = targetTab;
		Element.removeClassName(this.activeTab, 'hover');
		Element.addClassName(this.activeTab, 'active');

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
			new Insertion.Bottom(this.sectionContainerName, '<div class="' + tabId + '" id="' + containerId + '"></div>');
		}
		if (categoryId != "" && Element.empty(containerId))
		{
			// temporary "content" to avoid the content to be loaded twice
			$(containerId).update('&nbsp;');

			Backend.CustomField.treeBrowser.showFeedback(categoryId);
			new LiveCart.AjaxUpdater(this.getTabUrl(tabId, categoryId),
									 this.getContainerId(tabId, categoryId),
									 this.getIndicatorId(tabId),
									 undefined,
									 function(){
									   Backend.CustomField.treeBrowser.hideFeedback(categoryId);
									 }
									 );
		}
	},

	getIndicatorId: function(tabName)
	{
		return tabName + 'Indicator';
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
		categoryId = this.treeBrowser.getSelectedItemId();
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

	getActiveTab: function()
	{
		return this.activeTab;
	},

	setTabUrl: function(tabId, url)
	{
		$('tabId').url = url;
	}
}