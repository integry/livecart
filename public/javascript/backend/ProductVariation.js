/**
 *	@author Integry Systems
 */

if (!Backend.ProductVariation)
{
	Backend.ProductVariation = {}
}

Backend.ProductVariation.Editor = function(parentId, params)
{
	this.parentId = parentId;
	this.types = params.variationTypes;
	this.variations = params.variations;
	this.matrix = params.matrix;

	this.findUsedNodes();
	this.bindEvents();
	this.initializeEditor();
}

Backend.ProductVariation.Editor.prototype =
{
	typeInstances: {},

	findUsedNodes: function()
	{
		this.container = $('tabProductVariations_' + this.parentId + 'Content');
		this.mainMenu = this.container.down('ul.menu');
		this.addLink = this.mainMenu.down('.addType');

		this.mainForm = this.container.down('form.variationForm');
		this.tableTemplate = this.container.down('#productVariationTemplate');
	},

	bindEvents: function()
	{
		Event.observe(this.addLink, 'click', this.showAddTypeForm.bindAsEventListener(this));
	},

	initializeEditor: function()
	{
		var table = this.tableTemplate.cloneNode(true);
		this.table = table;
		this.mainForm.appendChild(table);

		var rowTemplate = table.down('tbody').down('tr');
		rowTemplate.parentNode.removeChild(rowTemplate);

		table.id = '';

		if (0 == this.types.length)
		{
			// add default type
			new Backend.ProductVariationType({}, this);
		}
		else
		{
			// add created types
			//this.types.each();
		}

	},

	registerType: function(type)
	{
		this.typeInstances[type.getIndex()] = type;
	},

	getTypeByIndex: function(index)
	{
		return this.typeInstances[index];
	},

	addVariation: function()
	{

	},

	deleteColumn: function(columnID)
	{
		var rows = this.table.getElementsByTagName('tr');
		for (var k = 0; k < rows.length; k++)
		{
			var cell = rows[k].getElementsByTagName('td')[columnID];
			cell.parentNode.removeChild(cell);
		}
	},

	deleteRow: function(field)
	{
		var row = field.up('tr');
		row.parentNode.removeChild(row);
	},

	createColumn: function()
	{
		var rows = this.table.getElementsByTagName('tr');
		for (var k = 0; k < rows.length; k++)
		{
			var clonedCell = rows[k].down('td');
			if (!clonedCell)
			{
				clonedCell = rows[k].down('th');
			}

			var cell = clonedCell.cloneNode(true);
			rows[k].insertBefore(cell, clonedCell);
		}

		return 0;
	},

	createRow: function()
	{
		var row = $A(this.table.getElementsByTagName('tr')).pop();
		var cloned = row.cloneNode(true);
		this.table.down('tbody').appendChild(cloned);
		$A(cloned.getElementsByTagName('input')).each(function(f) {f.value = '';});
		this.initCells(cloned);

		return cloned;
	},

	showAddTypeForm: function(e)
	{
		Event.stop(e);

	},

	hideAddTypeForm: function(e)
	{
		Event.stop(e);

	},

	showAddVariationForm: function(e)
	{
		Event.stop(e);

	},

	hideAddVariationForm: function(e)
	{
		Event.stop(e);

	},

}

Backend.ProductVariationType = function(data, editor)
{
	this.data = data;
	this.editor = editor;

	this.create();
}

Backend.ProductVariationType.prototype =
{
	getEditor: function()
	{
		return this.editor;
	},

	create: function()
	{
		this.index = this.editor.createColumn();
	},

	getCells: function()
	{
		var cells = [];
		$A(this.editor.getTable().down('tbody').getElementsByTagName('tr')).each(function(row)
		{
			cells.unshift(row.down('td', this.index));
		});

		return cells;
	},

	getHeaderCell: function()
	{

	},

	changeRowSpan: function(delta)
	{
		this.getCells().each(function(cell)
		{
			cell.rowSpan += delta;
		});

		if (this.index > 0)
		{
			this.editor.getTypeByIndex(this.index - 1).changeRowSpan(delta);
		}
	},

	delete: function()
	{

	},

	addVariation: function(data)
	{

	},

	deleteVariation: function()
	{

	}
}

Backend.ProductVariationVar = function(data, type)
{
	this.data = data;
	this.type = type;
}

Backend.ProductVariationVar.prototype =
{
	getType: function()
	{
		return this.type;
	}
}

/**
 *	Actual child product or child product container
 */
Backend.ProductVariationItem = function(data, variation)
{
	this.data = data;
	this.variation = variation;
}

Backend.ProductVariationItem.prototype =
{
	subItems: {},

	createRow: function()
	{
		var index = this.variation.getType().getIndex();
		var typeCnt = this.getEditor().getTypeCount();

		// the item is not created for the last column, so it needs to be done recursively
		if (index < typeCnt - 1)
		{
			for (var k = index; k < typeCnt; k++)
			{
				this.subItems[k] = new Backend.ProductVariationItem
			}
		}
		else
		{

		}
	},

	getEditor: function()
	{
		return this.variation.getType().getEditor();
	}
}