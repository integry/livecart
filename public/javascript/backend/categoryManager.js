if (LiveCart == undefined)
{
	var LiveCart = {}
}

/*
var CategoryTabControll = Class.create();

CategoryTabControll.prototype = {
	tabControll: null,
	
	initialize: function() {
		this.tabControll = new TabControll();
	},
	
}
*/

LiveCart.CategoryManager = {
	
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
		this.treeBrowser = new dhtmlXTreeObject("categoryBrowser","100%","100%", 0);
		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setOnClickHandler(this.activateCategory);
		this.treeBrowser.enableDragAndDrop(1);
		
		this.treeBrowser.insertNewItem(0,1,"Electronics",0,0,0,0, "CHILD");
		this.treeBrowser.insertNewItem(0,2,"Computers",0,0,0,0, "CHILD");
		this.treeBrowser.insertNewItem(0,3,"Cars & Motorsports",0,0,0,0, "CHILD");
		this.treeBrowser.insertNewItem(0,4,"Cameras & Photo",0,0,0,0, "CHILD");
		
		this.treeBrowser.insertNewItem(1,10,"Camcorders",0,0,0,0, "CHILD");
		this.treeBrowser.insertNewItem(1,11,"Televisions",0,0,0,0, "CHILD");
		this.treeBrowser.insertNewItem(1,12,"DVD",0,0,0,0, "SELECT");
		this.treeBrowser.insertNewItem(1,12,"MP3",0,0,0,0, "CHILD");
		this.treeBrowser.insertNewItem(1,12,"GPS",0,0,0,0, "CHILD");
		
		this.treeBrowser.insertNewItem(2,21,"Laptops",0,0,0,0, "CHILD");
		this.treeBrowser.insertNewItem(2,22,"Desktops",0,0,0,0, "CHILD");
		this.treeBrowser.insertNewItem(2,23,"Software",0,0,0,0, "CHILD");
		this.treeBrowser.insertNewItem(2,24,"PDAs",0,0,0,0, "CHILD");
	},
	
	activateCategory: function(categoryNodeId) 
	{
		
		Element.update('activeCategoryPath', LiveCart.CategoryManager.getPath(categoryNodeId));
		var tab = LiveCart.CategoryManager.tabControll.getActiveTab();
		if (tab.urlPattern == undefined)
		{
			tab.urlPattern = new String(tab.url);
		}
		tab.url = tab.urlPattern.replace('%id%', categoryNodeId);
		LiveCart.CategoryManager.tabControll.reloadActiveTab();
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
			nodeStr = LiveCart.CategoryManager.treeBrowser.getItemText(parentId)
			path.push(nodeStr);
			parentId = this.treeBrowser.getParentId(parentId)
		}
		while(parentId != 0)
		
		path = path.reverse();
		var pathStr = path.join(' > ');
		return pathStr;
	}
}