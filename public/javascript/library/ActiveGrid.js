/**
 *	Requires rico.js
 *
 */
ActiveGrid = Class.create();

ActiveGrid.prototype = 
{
  	/**
  	 *	Data table element instance
  	 */
  	tableInstance: null,
  	
  	/**
  	 *	Select All checkbox instance
  	 */
  	selectAllInstance: null,

  	/**
  	 *	Data feed URL
  	 */
  	dataUrl: null,
  	
  	/**
  	 *	Rico LiveGrid instance
  	 */
	ricoGrid: null,
  	
  	/**
  	 *	Array containing IDs of selected rows
  	 */
	selectedRows: new Object,
	
  	/**
  	 *	Set to true when Select All is used (so all records are selected by default)
  	 */
	inverseSelection: false,
  	
	initialize: function(tableInstance, dataUrl, totalCount, options)
  	{
		this.tableInstance = tableInstance;
		this.dataUrl = dataUrl;

		this.ricoGrid = new Rico.LiveGrid(this.tableInstance.id, 15, totalCount, dataUrl, 
								{
								  prefetchBuffer: true, 
								  onscroll: this.onScroll.bind(this),  
								  sortAscendImg: 'http://openrico.org/images/sort_asc.gif',
						          sortDescendImg: 'http://openrico.org/images/sort_desc.gif' 
								}
							);		
	
		var headerRow = this._getHeaderRow();
		this.selectAllInstance = headerRow.getElementsByTagName('input')[0];
		this.selectAllInstance.onclick = this.selectAll.bindAsEventListener(this); 
			
		this.onScroll(this.ricoGrid, 0);
	},
	
	onScroll: function(liveGrid, offset) 
	{
		var rows = this.tableInstance.getElementsByTagName('tr');
		for (k = 0; k < rows.length; k++)
		{
		  	rows[k].onclick = this.selectRow.bindAsEventListener(this);
		  	rows[k].onmouseover = this.highlightRow.bindAsEventListener(this);
		  	rows[k].onmouseout = this.removeRowHighlight.bindAsEventListener(this);
		}
		
		// make header row cells the same width as table cells
		if (rows.length > 0)
		{
			this._levelColumns(rows[0], this._getHeaderRow());	  
		}
		
		Backend.Product.updateHeader(liveGrid, offset);
		
		this._markSelectedRows();
	},
	
	/**
	 *	Select all rows
	 */
	selectAll: function(e)
	{
		this.selectedRows = new Object;		
		this.inverseSelection = this.selectAllInstance.checked;		
		this._markSelectedRows();
	},
	
	/**
	 *	Mark rows checkbox when a row is clicked
	 */
	selectRow: function(e)
	{
		var row = this._getTargetRow(e);
		var inp = row.getElementsByTagName('input')[0];
		
		id = this._getRecordId(row);
		
		if (!this.selectedRows[id])
		{
			this.selectedRows[id] = 0;  
		}
		
		this.selectedRows[id] = !this.selectedRows[id];
		
		this._selectRow(row);

		console.log(this.selectedRows[id]);
	},

	/**
	 *	Highlight a row when moving a mouse over it
	 */
	highlightRow: function(event)
	{
		Element.addClassName(this._getTargetRow(event), 'activeGrid_highlight');
	},

	/**
	 *	Remove row highlighting when mouse is moved out of the row
	 */
	removeRowHighlight: function(event)
	{
		Element.removeClassName(this._getTargetRow(event), 'activeGrid_highlight');	  
	},
	
	_markSelectedRows: function()
	{
		var rows = this.tableInstance.getElementsByTagName('tr');
		for (k = 0; k < rows.length; k++)
		{
			this._selectRow(rows[k]);  
		}	  	
	},
	
	_selectRow: function(rowInstance)
	{
		var id = this._getRecordId(rowInstance);
		var inp = rowInstance.getElementsByTagName('input')[0];
		
		if (inp)
		{
			var checked = this.selectedRows[id];
			if (this.inverseSelection)
			{
			  	checked = !checked;
			}
			
			inp.checked = checked;
		}
	},
	
	_getRecordId: function(rowInstance)
	{
		var inp = rowInstance.getElementsByTagName('input')[0];
		if (!inp)
		{
		  	return 0;
		}
		
		var nameParts = inp.name.split('[');
		var id = nameParts[nameParts.length - 1];
		return id.substr(0, id.length - 1);	  
	},
	
	/**
	 *	Make header and table content columns the same width
	 */
	_levelColumns: function(tableRow, headerRow)
	{
		tableCells = tableRow.getElementsByTagName('td');  
		headerCells = headerRow.getElementsByTagName('th'); 
		
		for (k = 0; k < tableCells.length; k++)
		{
		  	headerCells[k].style.width = tableCells[k].clientWidth + 'px';
		}
	},
	
	/**
	 *	Return event target row element
	 */
	_getTargetRow: function(event)
	{
		target = Event.element(event);

		while (target.tagName != 'TR')  
		{
		  	target = target.parentNode;
		}
		
		return target;
	},
	
	_getHeaderRow: function()
	{
		return $(this.tableInstance.id + '_header').getElementsByTagName('tr')[0];
	}
}