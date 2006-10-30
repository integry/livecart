if (LiveCart == undefined)
{
	var LiveCart = {}
}

LiveCart.CategoryManager = {
	
	init: function() 
	{
		this.initCategoryBrowser();
		this.initTabs();
	},
	
	initCategoryBrowser: function()
	{
		var tree = new dhtmlXTreeObject("categoryBrowser","100%","100%", 0);
		tree.setImagePath("/livecart/public/image/backend/dhtmlxtree/");
		
		tree.insertNewItem(0,1,"Electronics",0,0,0,0,"SELECT");
		tree.insertNewItem(0,2,"New Node zero",0,0,0,0,"SELECT");
		tree.insertNewItem(1,3,"New Node one",0,0,0,0,"SELECT");
		tree.insertNewItem(1,4,"New Node two",0,0,0,0,"SELECT");
		tree.insertNewNext(1,5,"New Node three",0,0,0,0,"SELECT");
	},
	
	initTabs: function()
	{
		var tabbar = new dhtmlXTabBar("managerContainer","top");
		tabbar.setImagePath("/livecart/public/image/backend/dhtmlxtabbar/");
		
		tabbar.addTab("mainContent","Main Details", "100px");
		tabbar.addTab("fieldContent","Fields", "100px");
		tabbar.addTab("filterContent","Filters", "100px");
		tabbar.addTab("subcategoryOrderContent","Subcategory Order", "100px");
		tabbar.addTab("permissionContent","Permissions", "100px");
		tabbar.addTab("imageContent","Images", "100px");
		tabbar.addTab("articleContent","Articles", "100px");
		
		tabbar.setContent("mainContent", "mainDetailsSection");
		tabbar.setTabActive("mainContent");
	}
}