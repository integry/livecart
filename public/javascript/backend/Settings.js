Backend.Settings = Class.create();

Backend.Settings.prototype = 
{
  	treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(categories)
	{
		this.treeBrowser = new dhtmlXTreeObject("settingsBrowser","","", 0);
		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setOnClickHandler(this.activateCategory.bind(this));

    	this.insertTreeBranch(categories, 0);    
	},
	
	insertTreeBranch: function(treeBranch, rootId)
	{
		for (k in treeBranch)
		{
		  	if('function' != typeof treeBranch[k])
		  	{
		        this.treeBrowser.insertNewItem(rootId, k, treeBranch[k].name, null, 0, 0, 0, '', 1);
				if (treeBranch[k].subs)
				{
					this.insertTreeBranch(treeBranch[k].subs, k);
				}
			}
		}  	
	},
	
	activateCategory: function(test)
	{
		var url = this.urls['edit'];
		url = url.replace('_id_', test);
		console.log(url);
		new LiveCart.AjaxUpdater(url, 'settingsContent', 'settingsIndicator');
	},
	
    /**
     * Insert array of categories into tree
     * 
     * @param array categories Array of category objects. Every category object should contain these elements
     *     parent - Id of parent category
     *     ID - Id o category
     *     name - Category name in current language
     *     options - Advanced options
     *     childrenCount - Indicates that this node has N childs
     */
    addCategories: function(categories) 
    {
        $A(categories).each(function(category) {         
            if(!category.parent || 0 == category.parent) 
            {
                category.options = "";
                category.parent = 0;
            }
            else if(!category.option) 
            {
                category.options = "";
            }

            this.treeBrowser.insertNewItem(category.parent,category.ID,category.name, null, 0, 0, 0, category.options, !category.childrenCount ? 0 : category.childrenCount);
        });
    }	
}