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
		rowTemplate.removeChild(this.variationCellTemplate);

		var typeHeaderCell = table.down('thead').down('th.variationType');
		typeHeaderCell.parentNode.removeChild(typeHeaderCell);
		this.typeHeaderCell = typeHeaderCell;

		table.id = '';

		if (0 == this.types.length)
		{
			// add default type and value
			var type = new Backend.ProductVariationType({}, this);
			var value = new Backend.ProductVariationVar({}, type);
			value.createItems();
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
		/*
		var rows = this.table.down('tbody').getElementsByTagName('tr');
		for (var k = 0; k < rows.length; k++)
		{
			var clonedCell = rows[k].down('td');

			var cell = clonedCell.cloneNode(true);
			rows[k].insertBefore(cell, clonedCell);
		}
		* */

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
		var defVariation = new Backend.ProductVariationVar({}, type);
		defVariation.addToItems();
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

	getVariationCellTemplate: function()
	{
		return this.variationCellTemplate;
	},

	getUniqueID: function()
	{
		return 'new_' + ++this.idCounter;
	},

	getTypes: function()
	{
		return this.typeInstances;
	},

	getTypeCount: function()
	{
		return this.columnCount;
	},

	getVariationCombinations: function(skipType)
	{
		var combinations = [];

		$H(this.typeInstances).each(function(value)
		{
			var type = value[1];
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
							var variation = combinations[c];
							variation.push(variations[v]);
							combined.push(variation);
						}
					}
				}
				else
				{
					variations.each(function(variation)
					{
						combined.push([variation]);
					});
				}

				combinations = combined;
			}
		});

		console.log(combinations);

		return combinations;
	},

	createItem: function(variations)
	{
		var parentTypeVariations = [];
		for (var k = 0; k < variations.length; k++)
		{
			if (variations[k].getType().getIndex() < this.columnCount - 1)
			{
				parentTypeVariations.push(variations[k]);
			}
		}

		if (parentTypeVariations.length)
		{
			var item = this.getItemsByVariations(parentTypeVariations).pop();
		}

		return new Backend.ProductVariationItem({}, variations, item);
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
				items.push(item);
			}
		});

		return items;
	},

	getItemInstances: function()
	{
		return this.itemInstances;
	},

	syncRowspans: function()
	{
		$H(this.getTypes()).each(function(value)
		{
			var cells = value[1].getCells();
			for (var k = 1; k < cells.length; k++)
			{
				cells[k].setAttribute('rowspan', cells[0].getAttribute('rowspan'));
			}
		});
	}
}

Backend.ProductVariationType = function(data, editor)
{
	this.data = data;
	this.editor = editor;
	this.id = this.data['ID'] ? this.data['ID'] : this.getEditor().getUniqueID();

	this.variations = {};
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
		var typeCount = this.getEditor().getTypeCount();
		var cells = [];
		$A(this.editor.getTable().down('tbody').getElementsByTagName('tr')).each(function(row)
		{
			var tds = row.getElementsBySelector('td.variation');
			if (tds.length == typeCount)
			{
				cells.push(tds[this.index]);
			}
		}.bind(this));

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

		var value = new Backend.ProductVariationVar({}, this);
		value.createItems();
	},

	registerVariation: function(variation)
	{
		this.variations[variation.getID()] = variation;
		this.editor.registerVariation(variation);
	},

	getVariations: function()
	{
		var variations = [];
		$H(this.variations).each(function(value)
		{
			variations.push(value[1]);
		});

		return variations;
	},

	getSubTypes: function()
	{
		var sub = [];
		var index = this.getIndex();
		$A(this.getEditor().getTypes()).each(function(type)
		{
			if (type.getIndex() > index)
			{
				sub.push(type);
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
	this.cells = [];

	this.data = data;
	this.type = type;
	this.id = this.data['ID'] ? this.data['ID'] : this.type.getEditor().getUniqueID();

	this.type.registerVariation(this);
}

Backend.ProductVariationVar.prototype =
{
	mainCell: null,

	cells: [],

	createItems: function()
	{
		var combinations = this.type.getEditor().getVariationCombinations(this.type);
		if (0 == combinations.length)
		{
			combinations = [[]];
		}

		for (var k = 0; k < combinations.length; k++)
		{
			combinations[k].push(this);
			this.type.getEditor().createItem(combinations[k]);
		}

		if (this.type.getIndex() > 0)
		{
			this.type.getEditor().getTypeByIndex(this.type.getIndex() - 1).changeRowSpan(1);
		}
	},

	addToItems: function()
	{
		$H(this.type.getEditor().getItemInstances()).each(function(value)
		{
			var item = value[1];
			item.addVariationCell(this);
		}.bind(this));
	},

	getType: function()
	{
		return this.type;
	},

	getID: function()
	{
		return this.id;
	},

	initCell: function(cell)
	{
		if (!this.mainCell)
		{
			cell.addClassName('input');
			this.mainCell = cell;
			Event.observe(this.mainCell.down('input'), 'change', this.changeName.bindAsEventListener(this));
			Event.observe(this.mainCell.down('input'), 'keyup', this.changeName.bindAsEventListener(this));
		}
		else
		{
			cell.removeClassName('input');
			this.cells.push(cell);
		}
	},

	changeName: function(e)
	{
		var name = $F(Event.element(e));

		this.cells.each(function(cell)
		{
			cell.down('span.name').update(name);
		});
	},

	getItems: function()
	{

	}
}

/**
 *	Actual child product
 */
Backend.ProductVariationItem = function(data, variations, parent)
{
	this.data = data;
	this.variations = variations;
	this.id = this.data['ID'] ? this.data['ID'] : this.getEditor().getUniqueID();

	this.createRow(parent);
}

Backend.ProductVariationItem.prototype =
{
	row: null,

	parent: null,

	createRow: function(parent)
	{
		var editor = this.getEditor();
		var parentRow = parent ? parent.getRow() : null;

		this.parent = parent;
		this.row = editor.getRowTemplate().cloneNode(true);

		if (parentRow)
		{
			parentRow.parentNode.insertBefore(this.row, parentRow.nextSibling);
		}
		else
		{
			editor.getTable().down('tbody').appendChild(this.row);
		}

		if (this.parent)
		{
			var variationCells = this._arrayDiff(this.parent.getVariations(), this.variations);
		}
		else
		{
			var variationCells = this.variations;
			variationCells.reverse()
		}

		variationCells.each(function(variation)
		{
			this.addVariationCell(variation);
		}.bind(this));

		editor.registerItem(this);
		editor.syncRowspans();
	},

	_arrayDiff: function(v, c, m)
	{
		var d = [], e = -1, h, i, j, k;
		for(i = c.length, k = v.length; i--;)
		{
			for(j = k; j && (h = c[i] !== v[--j]););
			h && (d[++e] = m ? i : c[i]);
		}
		return d;
	},

	addVariationCell: function(variation)
	{
		var cell = this.getEditor().getVariationCellTemplate().cloneNode(true);
		var lastVariation = this.row.getElementsBySelector('td.variation').pop();
		this.row.insertBefore(cell, lastVariation ? lastVariation.nextSibling : this.row.firstChild);

		variation.initCell(cell);

		return cell;
	},

	getEditor: function()
	{
		return this.variations[0].getType().getEditor();
	},

	getParentItem: function()
	{
		return this.parent;
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