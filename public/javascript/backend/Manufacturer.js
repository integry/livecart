/**
 *	@author Integry Systems
 */

if (!Backend.Manufacturer)
{
	Backend.Manufacturer = {}
}

Backend.Manufacturer.GridFormatter =
{
	url: '',

	formatValue: function(field, value, id)
	{
		if ('Manufacturer.name' == field)
		{
			value = '<span><span class="progressIndicator manufacturerIndicator" id="manufacturerIndicator_' + id + '" style="display: none;"></span></span>' +
				'<a href="' + this.url + '#manufacturer_' + id + '" id="manufacturer_' + id + '" onclick="Backend.Manufacturer.Editor.prototype.open(' + id + ', event); return false;">' +
					 value +
				'</a>';
		}

		return value;
	}
}

Backend.Manufacturer.Editor = function()
{
	this.callConstructor(arguments);
}

Backend.Manufacturer.Editor.methods =
{
	namespace: Backend.Manufacturer.Editor,

	getMainContainerId: function()
	{
		return 'manufacturerManagerContainer';
	},

	getInstanceContainer: function(id)
	{
		return $("tabUserInfo_" + id + "Content");
	},

	getListContainer: function()
	{
		return $('manufacturerGrid');
	},

	getNavHashPrefix: function()
	{
		return '#manufacturer_';
	},

	getActiveGrid: function()
	{
		return window.activeGrids["manufacturer_0"];
	}
}

Backend.Manufacturer.Editor.inheritsFrom(Backend.MultiInstanceEditor);