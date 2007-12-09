/**
 *	@author Integry Systems
 */

if (Backend == undefined)
{
	var Backend = {}
}

Backend.SelectFile = Class.create();
Backend.SelectFile.prototype =
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

	valueElement: null,

	initialize: function(valueElement)
	{
		this.valueElement = valueElement;

        var w = window.open(Backend.SelectFile.url, 'selectFile', 'width=950, height=450');

        // close the popup automatically if closing/reloading page
		Event.observe(window, 'unload', function()
		{
			w.close();
		});

		w.selectFileInstance = this;

        Event.observe(w, 'load',
            function()
            {
            	console.log('loaded');
            });

        this.window = w;
	},

	returnValue: function(value, window)
	{
		this.valueElement.value = value;
		window.close();
	},

	init: function()
	{
		this.treeBrowser = new dhtmlXTreeObject(this.window.document.getElementById("categoryBrowser"),"","", 0);
		this.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		this.treeBrowser.setStdImages('folderClosed.gif', 'folderOpen.gif', 'folderClosed.gif');
		this.treeBrowser.setOnClickHandler(this.activateCategory.bind(this));

		this.treeBrowser.setOnOpenStartHandler
			(
				function(id)
				{
					this.treeBrowser.showFeedback(id);
					return true;
				}.bind(this)
			);

		this.treeBrowser.setOnOpenEndHandler
			(
				function(id)
				{
					this.treeBrowser.hideFeedback(id);
					return true;
				}.bind(this)
			);

		this.treeBrowser.showFeedback =
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

		this.treeBrowser.hideFeedback =
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

		this.grid.setDataFormatter(Backend.SelectFile.GridFormatter);
	},

	initPage: function()
	{
		//Backend.Breadcrumb.display(1);
		this.window.Backend.Breadcrumb.setTree(this.treeBrowser);
	},

	updateHeader: function ( activeGrid, offset )
	{
		var liveGrid = activeGrid.ricoGrid;

		var totalCount = liveGrid.metaData.getTotalRows();
		var from = offset + 1;
		var to = offset + liveGrid.metaData.getPageSize();

		if (to > totalCount)
		{
			to = totalCount;
		}

		var categoryID = activeGrid.tableInstance.id.split('_')[1];
		var cont = $('productCount_' + categoryID);
		var countElement = document.getElementsByClassName('rangeCount', cont)[0];
		var notFound = document.getElementsByClassName('notFound', cont)[0];

        if (!countElement)
        {
            return false;
        }

		if (totalCount > 0)
		{
			if (!countElement.strTemplate)
			{
				countElement.strTemplate = countElement.innerHTML;
			}

			var str = countElement.strTemplate;
			str = str.replace(/\$from/, from);
			str = str.replace(/\$to/, to);
			str = str.replace(/\$count/, totalCount);

			countElement.innerHTML = str;
			notFound.style.display = 'none';
			countElement.style.display = '';
		}
		else
		{
			notFound.style.display = '';
			countElement.style.display = 'none';
		}
    },

	/**
	 * Tree browser onClick handler. Activates selected category by realoading active
	 * tab with category specific data
	 */
	activateCategory: function(categoryId)
	{
		this.window.Backend.Breadcrumb.display(categoryId);

        if (this.activeCategoryId == categoryId)
        {
            return false;
        }

        this.grid.setFilterValue('filter_file', categoryId);
		this.grid.reloadGrid();
		this.activeCategoryId = categoryId;
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

            this.treeBrowser.insertNewChild(category.parent,category.ID,category.name, null, 0, 0, 0, category.options, !category.childrenCount ? 0 : category.childrenCount);
        }.bind(this));
    },

    loadDirectory: function(dir)
    {
        this.treeBrowser.loadXML(this.links.categoryRecursiveAutoloading + "?id=" + dir);
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