/**
 *	@author Integry Systems
 */

if (Backend == undefined)
{
	var Backend = {}
}

Backend.SelectFile =
{
	/**
	 * Category tree browser instance
	 */
	treeBrowser: null,

	grid: null,

	/**
	 * Id of currenty selected category. Used for category tab content switching
	 */
	activeCategoryId: null,

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
		if (window.location.hash.length == 0)
        {
            window.location.hash = $('tabProducts') ?  '#cat_1#tabProducts__' : '#cat_1#tabMainDetails__';

			Backend.Breadcrumb.display(1);
        }

		Backend.SelectFile.treeBrowser.showFeedback =
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

		Backend.SelectFile.treeBrowser.hideFeedback =
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

		var elements = window.location.hash.split('#');
		if (elements[1].substr(0, 4) == 'cat_')
		{
			var parts = elements[1].split('_');
			var categoryId = parts[1];

            Backend.SelectFile.activeCategoryId = categoryId;
			Backend.SelectFile.treeBrowser.selectItem(categoryId, false, false);

			return true;
		}

        if($('categoryBrowser').getElementsByClassName('selectedTreeRow')[0])
        {
    		var treeNode = $('categoryBrowser').getElementsByClassName('selectedTreeRow')[0].parentNode;
    		treeNode.onclick();
    		Backend.ajaxNav.add('cat_' + treeNode.parentObject.id + '#tabProducts');
        }
	},

	/**
	 * Builds category tree browser object (dhtmlxTree) and initializes its params
	 */
	initCategoryBrowser: function()
	{
		this.treeBrowser = new dhtmlXTreeObject("categoryBrowser","","", 0);

		Backend.SelectFile.treeBrowser.setCategoryStyle =
			function(category)
			{
                this.setItemColor(category.ID, (category.isEnabled < 1 ? '#999' : '#000'), (category.isEnabled < 1 ? '#999' : '#fff'));
            }

		Backend.Breadcrumb.setTree(this.treeBrowser);

		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setOnClickHandler(this.activateCategory);
	},

	initTabs: function()
	{
//		this.tabControl = new CategoryTabControl(this.treeBrowser, 'tabList', 'sectionContainer', 'image/indicator.gif');
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
        Backend.Breadcrumb.display(categoryId);

        if (Backend.SelectFile.activeCategoryId == categoryId)
        {
            return false;
        }
console.log(Backend.SelectFile.grid.ricoGrid.options.largeBufferSize);
        Backend.SelectFile.grid.setFilterValue('filter_file', categoryId);
		Backend.SelectFile.grid.reloadGrid();
		Backend.SelectFile.activeCategoryId = categoryId;

		// set ID for the current tree node element
		$('categoryBrowser').getElementsByClassName('selectedTreeRow')[0].parentNode.id = 'cat_' + categoryId;
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

		Backend.SelectFile.treeBrowser.setItemText(categoryData.ID, categoryData.name);
		Backend.SelectFile.treeBrowser.setCategoryStyle(categoryData);
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
        return this.buildUrl(Backend.SelectFile.links.countTabsItems, categoryId);
	},

	buildUrl: function(urlPattern, id)
	{
		return urlPattern.replace('_id_', id);
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

            Backend.SelectFile.treeBrowser.insertNewItem(category.parent,category.ID,category.name, null, 0, 0, 0, category.options, !category.childrenCount ? 0 : category.childrenCount);
            Backend.SelectFile.treeBrowser.setCategoryStyle(category);
        });
    },

    loadBookmarkedCategory: function(categoryID)
    {
        var match = window.location.hash.match(/cat_(\d+)/);
        if(match)
        {
            var alreadyLoaded = false;
            try
            {
                $A(Backend.SelectFile.treeBrowser._globalIdStorage).each(function(id)
                {
                    if(id == match[1])
					{
					   alreadyLoaded = true;
					   throw $break;
				    }
                });
            }
            catch(e) { }

            if(!alreadyLoaded)
            {
                Backend.SelectFile.treeBrowser.loadXML(Backend.SelectFile.links.categoryRecursiveAutoloading + "?id=" + match[1]);
            }
        }
    },
}