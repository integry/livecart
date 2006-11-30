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
	 * Category module initialization
	 */
	init: function()
	{
		this.initCategoryBrowser();
		this.initTabs();
	},

	/**
	 * Builds category tree browser object (dhtmlxTree) and initializes its params
	 */
	initCategoryBrowser: function()
	{
		this.treeBrowser = new dhtmlXTreeObject("categoryBrowser","","", 0);
		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setOnClickHandler(this.activateCategory);
		this.treeBrowser.enableDragAndDrop(1);
	},

	activateCategory: function(categoryNodeId)
	{
		Element.update('activeCategoryPath', Backend.Category.getPath(categoryNodeId));
		var tab = Backend.Category.tabControl.getActiveTab();
		if (tab.urlPattern == undefined)
		{
			tab.urlPattern = new String(tab.url);
		}

		Backend.Category.tabControl.reloadActiveTab();
	},

	initTabs: function()
	{
		this.tabControl = new CategoryTabControl(this.treeBrowser, 'tabList', '', 'image/indicator.gif');
	},

	getPath: function(nodeId)
	{
		var path = new Array();
		var parentId = nodeId;
		var nodeStr = '';
		do
		{
			nodeStr = Backend.Category.treeBrowser.getItemText(parentId)
			path.push(nodeStr);
			parentId = this.treeBrowser.getParentId(parentId)
		}
		while(parentId != 0)

		path = path.reverse();
		var pathStr = path.join(' > ');
		return pathStr;
	}
}

var CategoryTabControl = Class.create();
CategoryTabControl.prototype = {

	activeTab: null,
	indicatorImageName: null,
	treeBrowser: null,

	initialize: function(treeBrowser, tabContainerName, sectionContainerName, indicatorImageName)
	{
		this.treeBrowser = treeBrowser;
		if (indicatorImageName != undefined)
		{
			this.indicatorImageName = indicatorImageName;
		}

		var tabList = document.getElementsByClassName("tab");
		for (var i = 0; i < tabList.length; i++)
		{
			tabList[i].onclick = this.handleTabClick.bindAsEventListener(this);
			tabList[i].onmouseover = this.handleTabMouseOver.bindAsEventListener(this);
			tabList[i].onmouseout = this.handleTabMouseOut.bindAsEventListener(this);

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
				Element.show(tabList[i].id + 'Content');
			}
			else
			{
				Element.hide(tabList[i].id + 'Content');
			}
		}
	},

	handleTabMouseOver: function(evt)
	{
		if (this.activeTab != evt.target)
		{
			Element.removeClassName(evt.target, 'inactive');
			Element.addClassName(evt.target, 'hover');
		}
	},

	handleTabMouseOut: function(evt)
	{
		if (this.activeTab != evt.target)
		{
			Element.removeClassName(evt.target, 'hover');
			Element.addClassName(evt.target, 'inactive');
		}
	},

	handleTabClick: function(evt)
	{
		var targetTab = evt.target;
		this.activateTab(targetTab);
	},

	activateTab: function(targetTab)
	{
		var categoryId = this.treeBrowser.getSelectedItemId();
		var tabId = targetTab.id;

		if (this.activeTab == targetTab && !Element.empty(this.getContainerId(targetTab.id, categoryId)))
		{
			return;
		}

		if (this.activeTab != null)
		{
			Element.removeClassName(this.activeTab, 'active');
			Element.addClassName(this.activeTab, 'inactive');
			Element.hide(this.getContainerId(this.activeTab.id, categoryId));
		}
		this.activeTab = targetTab;
		Element.removeClassName(this.activeTab, 'hover');
		Element.addClassName(this.activeTab, 'active');
		Element.show(this.getContainerId(this.activeTab.id, categoryId));

		if (Element.empty(contentId))
		{
			new LiveCart.AjaxUpdater(this.getTabUrl(tabId, categoryId),
									 this.getContainerId(tabId, categoryId),
									 this.getIndicatorId(tabId));
		}
	},

	showTabContent: function()
	{

	},

	hideTabContent: function()
	{

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
		var url = $(tabName).url.replace('%id%', categoryId);
		return url;
	},

	/**
	 * Reset content related to a given tab. When tab will be activated content must
	 * be resent
	 */
	resetContent: function(tabObj, categoryId)
	{
		var contentContainerId = this.getTabUrl(tabObj.id, categoryId);
		$(contentContainerId).innerHTML = '';
	},

	reloadActiveTab: function()
	{
		categoryId = this.treeBrowser.getSelectedItemId();
		this.resetContent(this.activeTab, categoryId);
		this.activateTab(this.activeTab, categoryId);
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