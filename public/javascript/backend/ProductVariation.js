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

	itemInstances: {},

	variationInstances: {},

	columnCount: 0,

	idCounter: 0,

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
		Event.observe(this.addLink, 'click', this.createType.bindAsEventListener(this));
	},

	initializeEditor: function()
	{
		var table = this.tableTemplate.cloneNode(true);
		this.table = table;
		this.mainForm.appendChild(table);

		var rowTemplate = table.down('tbody').down('tr');
		this.rowTemplate = rowTemplate;
		this.variationCellTemplate = this.rowTemplate.down('td.variation');
		rowTemplate.parentNode.removeChild(rowTemplate);

		var typeHeaderCell = table.down('thead').down('th.variationType');
		typeHeaderCell.parentNode.removeChild(typeHeaderCell);
		this.typeHeaderCell = typeHeaderCell;

		table.id = '';

		if (0 == this.types.length)
		{
			// add default type and value
			var type = new Backend.ProductVariationType({}, this);
			var value = new Backend.ProductVariationVar({}, type);
		}
		else
		{
			// add created types
			//this.types.each();
		}

	},

	registerType: function(type)
	{
		this.typeInstances[type.getID()] = type;
	},

	registerVariation: function(variation)
	{
		this.variationInstances[variation.getID()] = variation;
	},

	registerItem: function(item)
	{
		this.itemInstances[item.getID()] = item;
	},

	getTypeByIndex: function(index)
	{
		var foundType = null;

		$H(this.typeInstances).each(function(value)
		{
			var type = value[1];
			if (type.index == index)
			{
				foundType = type;
				return;
			}
		});

		return foundType;
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
		// header
		var tr = this.table.down('thead').down('tr');
		tr.insertBefore(this.typeHeaderCell.cloneNode(true), tr.down('th', this.columnCount));

		// body
		var rows = this.table.down('tbody').getElementsByTagName('tr');
		for (var k = 0; k < rows.length; k++)
		{
			var clonedCell = rows[k].down('td');

			var cell = clonedCell.cloneNode(true);
			rows[k].insertBefore(cell, clonedCell);
		}

		return this.columnCount++;
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

	createType: function(e)
	{
		Event.stop(e);

		var type = new Backend.ProductVariationType({}, this);
	},

	getTable: function()
	{
		return this.table;
	},

	getLastRow: function()
	{
		return this.table.down('tbody').getElementsByTagName('tr').pop();
	},

	getRowTemplate: function()
	{
		return this.rowTemplate;
	},

	getUniqueID: function()
	{
		return 'new_' + ++this.idCounter;
	},

	getTypes: function()
	{
		return this.typeInstances;
	},

	getLastTypeIndex: function()
	{
		return this.columnCount - 1;
	},

	getVariationCombinations: function(skipType)
	{
		var combinations = [];

		for (var k = 0; k < this.typeInstances.length; k++)
		{
			var type = this.typeInstances[k];
			var combined = [];

			if (type != skipType)
			{
				var variations = type.getVariations();

				if (combinations.length > 0)
				{
					for (var c = 0; c < combinations.length; c++)
					{
						for (var v = 0; v < variations.length; v++)
						{
							var variation = variations[v];
							variation.push(combinations[c]);
							combined.push(variation);
						}
					}
				}
				else
				{
					combined = variations;
				}

				combinations = combined;
			}
		}

		return combinations;
	},

	createItems: function(variations)
	{
		var parentTypeVariations = [];
		for (var k = 0; k < variations.length; k++)
		{
			if (variations[k].getType().getIndex() < this.columnCount)
			{
				parentTypeVariations.push(variations[k]);
			}
		}

		if (parentTypeVariations.length)
		{
			var item = this.getItemsByVariations(parentTypeVariations).pop();
		}

		new Backend.ProductVariationItem({}, variations, item ? item.getRow() : null);
console.log(item);
		if (item)
		{
			//this.changeRowSpan(1);
		}
	},

	getItemsByVariations: function(variations)
	{
		var items = [];
		$H(this.itemInstances).each(function(value)
		{
			var item = value[1];
			var intersect = item.getVariations().findAll( function(token){ return variations.include(token) } );
			if (intersect.length == variations.length)
			{
				items.unshift(item);
			}
		});

		return items;
	}
}

Backend.ProductVariationType = function(data, editor)
{
	this.data = data;
	this.editor = editor;
	this.id = this.data['ID'] ? this.data['ID'] : this.getEditor().getUniqueID();

	this.create();
	this.editor.registerType(this);
}

Backend.ProductVariationType.prototype =
{
	variations: {},

	getEditor: function()
	{
		return this.editor;
	},

	create: function()
	{
		this.index = this.editor.createColumn();

		Event.observe(this.getHeaderCell(), 'click', this.createNewVariation.bind(this));
	},

	getIndex: function()
	{
		return this.index;
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
		return this.editor.getTable().down('thead').down('th', this.index);
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

	createNewVariation: function(e)
	{
		Event.stop(e);

		new Backend.ProductVariationVar({}, this);
	},

	registerVariation: function(variation)
	{
		this.variations[variation.getID()] = variation;
		this.editor.registerVariation(variation);
	},

	getSubTypes: function()
	{
		var sub = [];
		var index = this.getIndex();
		$A(this.getEditor().getTypes()).each(function(type)
		{
			if (type.getIndex() > index)
			{
				sub.unshift(type);
			}
		});

		return sub;
	},

	getID: function()
	{
		return this.id;
	}
}

/**
 * 	Product variation (for example: small, medium, large - for sizes, red, green, blue - for colors, etc.)
 */
Backend.ProductVariationVar = function(data, type)
{
	this.data = data;
	this.type = type;
	this.id = this.data['ID'] ? this.data['ID'] : this.type.getEditor().getUniqueID();

	this.type.registerVariation(this);

	var combinations = this.type.getEditor().getVariationCombinations(this.type);
	if (0 == combinations.length)
	{
		console.log(combinations);
		combinations = [[]];
	}

	for (var k = 0; k < combinations.length; k++)
	{
		combinations[k].push(this);
		this.type.getEditor().createItems(combinations[k]);
	}

	console.log(combinations);
}

Backend.ProductVariationVar.prototype =
{
	getType: function()
	{
		return this.type;
	},

	getID: function()
	{
		return this.id;
	},

	getItems: function()
	{

	}
}

/**
 *	Actual child product
 */
Backend.ProductVariationItem = function(data, variations, parentRow)
{
	this.data = data;
	this.variations = variations;
	this.id = this.data['ID'] ? this.data['ID'] : this.getEditor().getUniqueID();

	this.createRow(parentRow);
}

Backend.ProductVariationItem.prototype =
{
	createRow: function(parentRow)
	{
		var editor = this.getEditor();
		this.row = editor.getRowTemplate().cloneNode(true);

		if (parentRow)
		{
			parentRow.parentNode.insertBefore(this.row, parentRow.nextSibling);
		}
		else
		{
			editor.getTable().down('tbody').appendChild(this.row);
		}

		editor.registerItem(this);
	},

	getEditor: function()
	{
		return this.variations[0].getType().getEditor();
	},

	getID: function()
	{
		return this.id;
	},

	getVariations: function()
	{
		return this.variations;
	},

	getRow: function()
	{
		return this.row;
	}
}