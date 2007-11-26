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
		this.treeBrowser = new dhtmlXTreeObject("categoryBrowser","","", 0);
		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setStdImages('folderClosed.gif', 'folderOpen.gif', 'folderClosed.gif');
		this.treeBrowser.setOnClickHandler(this.activateCategory);

		this.treeBrowser.setOnOpenStartHandler
			(
				function(id) 
				{ 
					Backend.SelectFile.treeBrowser.showFeedback(id);
					return true; 
				}
			);

		this.treeBrowser.setOnOpenEndHandler
			(
				function(id) 
				{ 
					Backend.SelectFile.treeBrowser.hideFeedback(id);
					return true; 
				}
			);

		Backend.Breadcrumb.setTree(this.treeBrowser);
		
		this.grid.setDataFormatter(Backend.SelectFile.GridFormatter);
	},

	initPage: function()
	{
		Backend.Breadcrumb.display(1);

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

        Backend.SelectFile.grid.setFilterValue('filter_file', categoryId);
		Backend.SelectFile.grid.reloadGrid();
		Backend.SelectFile.activeCategoryId = categoryId;
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
console.log(!category.childrenCount ? 0 : category.childrenCount);
            Backend.SelectFile.treeBrowser.insertNewChild(category.parent,category.ID,category.name, null, 0, 0, 0, category.options, !category.childrenCount ? 0 : category.childrenCount);
        });
    },

    loadDirectory: function(dir)
    {
        Backend.SelectFile.treeBrowser.loadXML(Backend.SelectFile.links.categoryRecursiveAutoloading + "?id=" + dir);
    }
}

Backend.SelectFile.GridFormatter =
{
	productUrl: '',
	
	formatValue: function(field, value, id)
	{
		if ('fileSize' == field)
		{
			value = value + ' KB';
		}
		
		else if('fileName' == field)
		{
            value = '<a href="#" id="file_' + id + '" onclick="Backend.Product.openProduct(' + id + ', event); return false;">' +
                value +
            '</a>';
		}
		
		return value;
	}
}