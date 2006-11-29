if (Backend == undefined)
{
	var Backend = {}
}

Backend.CategoryManager = {

	tabControll: null,
	activeCategoryId: null,
	treeBrowser: null,

	init: function()
	{
		this.initTabs();
		this.initCategoryBrowser();
	},

	initCategoryBrowser: function()
	{
		this.treeBrowser = new dhtmlXTreeObject("categoryBrowser","","", 0);
		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setOnClickHandler(this.activateCategory);
		this.treeBrowser.enableDragAndDrop(1);
	},

	activateCategory: function(categoryNodeId)
	{

		Element.update('activeCategoryPath', Backend.CategoryManager.getPath(categoryNodeId));
		var tab = Backend.CategoryManager.tabControll.getActiveTab();
		if (tab.urlPattern == undefined)
		{
			tab.urlPattern = new String(tab.url);
		}
		tab.url = tab.urlPattern.replace('%id%', categoryNodeId);
		alert(tab.url);
		Backend.CategoryManager.tabControll.reloadActiveTab();
	},

	initTabs: function()
	{
		this.tabControll = new TabControll('tabList', '', 'image/indicator.gif');
	},

	getPath: function(nodeId)
	{
		var path = new Array();
		var parentId = nodeId;
		var nodeStr = '';
		do
		{
			nodeStr = Backend.CategoryManager.treeBrowser.getItemText(parentId)
			path.push(nodeStr);
			parentId = this.treeBrowser.getParentId(parentId)
		}
		while(parentId != 0)

		path = path.reverse();
		var pathStr = path.join(' > ');
		return pathStr;
	}
}