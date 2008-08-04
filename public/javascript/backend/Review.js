/**
 *	@author Integry Systems
 */

if (!Backend.Review)
{
	Backend.Review = {}
}

Backend.Review.GridFormatter =
{
	url: '',

	productIDs: {},

	formatValue: function(field, value, id)
	{
		if ('Product.ID' == field)
		{
			this.productIDs[id] = value;
		}

		if ('ProductReview.title' == field || 'ProductReview.nickname' == field)
		{
			return '<span><span class="progressIndicator ReviewIndicator" id="reviewIndicator_' + id + '" style="display: none;"></span></span>' +
				'<a href="' + this.url + '#review_' + id + '" id="review_' + id + '" onclick="Backend.Review.Editor.prototype.open(' + id + ', event); return false;">' +
					 value +
				'</a>';
		}

		if ('Product.name' == field)
		{
			return '<span class="progressIndicator" style="display: none;"></span><a href="' + Backend.Product.GridFormatter.productUrl + this.productIDs[id] + '" onclick="Backend.Product.openProduct(' + this.productIDs[id] + ', event); return false;">' + value + '</a>';
		}

		return value;
	}
}

Backend.Review.Editor = function()
{
	this.callConstructor(arguments);
}

Backend.Review.Editor.methods =
{
	namespace: Backend.Review.Editor,

	open: function(id, e, onComplete)
	{
		this.categoryContainerStyle = $('managerContainer').style.display;
		this.productContainerStyle = $('productManagerContainer').style.display;

		var targ = Event.element(e).up('table.activeGrid');
		var owner = targ.id.match(/reviews_([c0-9]+)/)[1];
		var res = this.parent.open.call(this, id, e, onComplete, owner);

		$('managerContainer').hide();
		$('productManagerContainer').hide();

		return res;
	},

	cancelForm: function()
	{
		this.parent.cancelForm.apply(this, arguments);
		$('managerContainer').style.display = this.categoryContainerStyle;
		$('productManagerContainer').style.display = this.productContainerStyle;
	},

	getMainContainerId: function(owner)
	{
		return 'reviewManagerContainer';
	},

	getInstanceContainer: function(id)
	{
		return $("tabReviewEdit_" + id + "Content");
	},

	getListContainer: function(owner)
	{
		return this.isCategory() ? $('managerContainer') : $('productManagerContainer');
	},

	getNavHashPrefix: function()
	{
		return '';
		return '#review_';
	},

	isCategory: function()
	{
		return this.owner.substr(0, 1) == 'c';
	},

	getActiveGrid: function()
	{
		return window.activeGrids["reviews_" + this.owner];
	}
}

Backend.Review.Editor.inheritsFrom(Backend.MultiInstanceEditor);