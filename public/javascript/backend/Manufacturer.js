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

	getAddContainerId: function()
	{
		return 'addManufacturer';
	},

	getInstanceContainer: function(id)
	{
		if (id)
		{
			return $("tabUserInfo_" + id + "Content");
		}
		else
		{
			return $('addManufacturer');
		}
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
	},


	afterSubmitForm: function(response)
	{
		Backend.MultiInstanceEditor.prototype.afterSubmitForm.bind(this)(response);

		if(response.status == 'success')
		{
			this.hideAddForm();
			this.open(response.manufacturer.ID);
		}
	}
}

Backend.Manufacturer.Editor.inheritsFrom(Backend.MultiInstanceEditor);

Event.observe(window, 'load',
	function()
	{
		Backend.Manufacturer.Editor.prototype.Links = {add: Router.createUrl('backend.manufacturer', 'add')}
	});
