if (LiveCart == undefined)
{
	var LiveCart = {}
}

LiveCart.CategoryManager = {
	
	init: function() 
	{
		var tree = new dhtmlXTreeObject("categoryBrowser","100%","100%", 0);
		tree.insertNewItem(0,1,"Some Root Node",0,0,0,0,"SELECT");
		tree.insertNewItem(0,2,"New Node zero",0,0,0,0,"SELECT");
		tree.insertNewItem(1,3,"New Node one",0,0,0,0,"SELECT");
		tree.insertNewItem(1,4,"New Node two",0,0,0,0,"SELECT");
		tree.insertNewNext(1,5,"New Node three",0,0,0,0,"SELECT");
	}
}