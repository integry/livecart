if (LiveCart == undefined)
{
	var LiveCart = {}
}

LiveCart.CategoryManager = {
	
	tabbar: null,
	
	init: function() 
	{
		this.initCategoryBrowser();
		this.initTabs();
	},
	
	initCategoryBrowser: function()
	{
		var tree = new dhtmlXTreeObject("categoryBrowser","100%","100%", 0);
		tree.setImagePath("image/backend/dhtmlxtree/");
		
		tree.insertNewItem(0,1,"Electronics",0,0,0,0,"SELECT");
		tree.insertNewItem(0,2,"New Node zero",0,0,0,0,"SELECT");
		tree.insertNewItem(1,3,"New Node one",0,0,0,0,"SELECT");
		tree.insertNewItem(1,4,"New Node two",0,0,0,0,"SELECT");
		tree.insertNewNext(1,5,"New Node three",0,0,0,0,"SELECT");
	},
	
	initTabs: function()
	{
		var tabbar = new dhtmlXTabBar("managerContainer","top");
		this.tabbar = tabbar;
		tabbar.setImagePath("image/backend/dhtmlxtabbar/");
		
		tabbar.addTab("mainContent",'<img id="mainContentTabInd" style="display:none" src="image/indicator.gif" align="absmiddle"/> Main Details', "100px");
		tabbar.addTab("fieldContent",'<img id="fieldContentTabInd" style="display:none" src="image/indicator.gif" align="absmiddle"/> Fields', "100px");
		tabbar.addTab("filterContent","Filters", "100px");
		tabbar.addTab("subcategoryOrderContent","Subcategory Order", "100px");
		tabbar.addTab("permissionContent","Permissions", "100px");
		tabbar.addTab("imageContent","Images", "100px");
		tabbar.addTab("articleContent","Articles", "100px");
		
		tabbar.setOnSelectHandler(LiveCart.CategoryManager.handleTabActivation);
		tabbar.setContent("mainContent", "mainDetailsSection");
		tabbar.setTabActive("mainContent");
	},
	
	handleTabActivation: function(containerId, ido)
	{
		switch(containerId)
		{
			case 'fieldContent':
				new LiveCart.AjaxUpdater(specFieldUrl, 'specFieldSection', 'fieldContentTabInd');
				LiveCart.CategoryManager.tabbar.setContent('fieldContent', 'specFieldSection');
			break;
		}
		return true;
	}
}