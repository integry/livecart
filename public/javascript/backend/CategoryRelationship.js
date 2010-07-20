/**
 *	@author Integry Systems
 */

if (!Backend.CategoryRelationship)
{
	Backend.CategoryRelationship = {}
}

Backend.CategoryRelationship = function(container, product, categories)
{
	this.container = container;
	this.category = product;

	this.findUsedNodes();
	this.bindEvents();

	this.additionalCategories.id = 'relatedCategories_' + this.category.ID;

	if (categories)
	{
		for (k = 0; k < categories.length; k++)
		{
			this.addCategory(categories[k].ID, this.getCategoryPath(categories[k].RelatedCategory));
		}
	}

	this.createActiveList();
}

Backend.CategoryRelationship.prototype =
{
	findUsedNodes: function()
	{
		this.addCategoryLink = this.container.down('.addAdditionalCategory').down('a');
		this.additionalCategories = this.container.down('.additionalCategories');
		this.categoryTemplate = this.container.down('.categoryTemplate');
	},

	bindEvents: function()
	{
		Event.observe(this.addCategoryLink, 'click', this.initAddCategory.bindAsEventListener(this));
	},

	getCategoryPath: function(category)
	{
		var path = '';

		if (category.ParentNode.ParentNode)
		{
			path = this.getCategoryPath(category.ParentNode) + ' &gt; ';
		}

		path += category.name_lang;

		return path;
	},

	initAddCategory: function(e)
	{
		Event.stop(e);
		var selector = new Backend.Category.PopupSelector(
			this.saveAddCategory.bind(this),
			null,
			this.category.ID
		);
	},

	saveAddCategory: function(categoryID, pathAsText, path, selector)
	{
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.categoryRelationship', 'addCategory', {id: this.category.ID, categoryId: categoryID}), null,
			function(originalRequest)
			{
				if (originalRequest.responseData.data)
				{
					var el = this.addCategory(categoryID, pathAsText);
					new Effect.Highlight(el);
					this.updateTabCount();
					this.createActiveList();
				}
			}.bind(this));
	},

	addCategory: function(categoryID, pathAsText)
	{
		var node = this.categoryTemplate.cloneNode(true);
		node.id = this.additionalCategories.id + '_' + categoryID;
		this.additionalCategories.appendChild(node);

		node.down('.categoryName').update(pathAsText);
		node.show();
		node.removeClassName('categoryTemplate');

		Event.observe(node.down('.recordDelete'), 'click', this.initDeleteCategory.bindAsEventListener(this));

		node.categoryID = categoryID;

		return node;
	},

	initDeleteCategory: function(e)
	{
		Event.stop(e);
		var node = Event.element(e).up('li');
		new LiveCart.AjaxRequest(Backend.Router.createUrl('backend.categoryRelationship', 'delete', {id: this.category.ID, categoryId: node.categoryID}), node.down('.progressIndicator'), function() { node.parentNode.removeChild(node); this.updateTabCount(); }.bind(this));
	},

	createActiveList: function()
	{
		var activeList = ActiveList.prototype.getInstance(this.additionalCategories,
			{
				beforeSort:	 function(li, order)
				{
					return Backend.Router.createUrl('backend.categoryRelationship', 'saveOrder', {draggedId: this.getRecordId(li), id: this.getRecordId(li.parentNode)})  + '&' + order;
				},

				afterSort: function(li) {}
			}
		);

		activeList.decorateItems();
		activeList.createSortable(true);
	},

	updateTabCount: function()
	{
		var count = this.additionalCategories.getElementsByTagName('li').length + 1;
		var tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
		tabControl.setCounter('tabRelatedCategories', count);
	}
}