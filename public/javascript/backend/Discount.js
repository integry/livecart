/**
 *	@author Integry Systems
 */

if (!Backend.Discount)
{
	Backend.Discount = {}
}

Backend.Discount.GridFormatter =
{
	url: '',

	formatValue: function(field, value, id)
	{
		if ('DiscountCondition.name' == field)
		{
			value = '<span><span class="progressIndicator discountIndicator" id="discountIndicator_' + id + '" style="display: none;"></span></span>' +
				'<a href="' + this.url + '#discount_' + id + '" id="discount_' + id + '" onclick="Backend.Discount.Editor.prototype.open(' + id + ', event); return false;">' +
					 value +
				'</a>';
		}

		return value;
	}
}

Backend.Discount.Editor = function()
{
	this.callConstructor(arguments);
}

Backend.Discount.Editor.methods =
{
	namespace: Backend.Discount.Editor,

	getMainContainerId: function()
	{
		return 'discountManagerContainer';
	},

	getAddContainerId: function()
	{
		return 'addDiscountContainer';
	},

	getInstanceContainer: function(id)
	{
		return id ? $("tabUserInfo_" + id + "Content") : $(this.getAddContainerId());
	},

	getListContainer: function()
	{
		return $('discountGrid');
	},

	getNavHashPrefix: function()
	{
		return '#discount_';
	},

	getActiveGrid: function()
	{
		return window.activeGrids["discount_0"];
	},

	reInitAddForm: function()
	{
		$('cancel_discount_add').onclick = function(e)
		{
			this.hideAddForm();
			Event.stop(e);
		}.bind(this);

		$('discountAddForm').onsubmit = this.beforeSaveAdd.bind(this);
	},

	beforeSaveAdd: function(e)
	{
		var instance = this.saveAdd(e);
	}
}

Backend.Discount.Editor.inheritsFrom(Backend.MultiInstanceEditor);