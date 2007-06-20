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
	selectedRows: {},
	
  	/**
  	 *	Set to true when Select All is used (so all records are selected by default)
  	 */
	inverseSelection: false,
	
  	/**
  	 *	Object that handles data transformation for presentation
  	 */
	dataFormatter: null,

	filters: {},
	
	loadIndicator: null,
  	
	initialize: function(tableInstance, dataUrl, totalCount, loadIndicator)
  	{
		this.tableInstance = tableInstance;
		this.dataUrl = dataUrl;
		this.setLoadIndicator(loadIndicator);
		this.filters = {};

		this.ricoGrid = new Rico.LiveGrid(this.tableInstance.id, 15, totalCount, dataUrl, 
								{
								  prefetchBuffer: true, 
								  onscroll: this.onScroll.bind(this),  
								  sortAscendImg: 'image/silk/bullet_arrow_up.png',
						          sortDescendImg: 'image/silk/bullet_arrow_down.png' 
								}
							);	
							
		this.ricoGrid.activeGrid = this;	
	
		var headerRow = this._getHeaderRow();
		this.selectAllInstance = headerRow.getElementsByTagName('input')[0];
		this.selectAllInstance.onclick = this.selectAll.bindAsEventListener(this); 
		this.selectAllInstance.parentNode.onclick = function(e){Event.stop(e);}.bindAsEventListener(this); 
                		                        	
		this.ricoGrid.onUpdate = this.onUpdate.bind(this);
		this.ricoGrid.onBeginDataFetch = this.showFetchIndicator.bind(this);
		this.ricoGrid.options.onRefreshComplete = this.hideFetchIndicator.bind(this);
				
		this.onScroll(this.ricoGrid, 0);
		
		this.ricoGrid.init();
		
		var rows = this.tableInstance.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
		for (k = 0; k < rows.length; k++)
		{
		  	rows[k].onclick = this.selectRow.bindAsEventListener(this);
		  	rows[k].onmouseover = this.highlightRow.bindAsEventListener(this);
		  	rows[k].onmouseout = this.removeRowHighlight.bindAsEventListener(this);
		}		
	},
	
	getRows: function(data)
	{
		var HTML = '';
		var rowHTML = '';
				
		var data = eval('(' + data + ')');
				
		for(k = 0; k < data['data'].length; k++)
		{
			var id = data['data'][k][0];
			data['data'][k][0] = '<input type="checkbox" class="checkbox" name="item[' + id + ']" />';
			
			if (this.dataFormatter)
			{
				for(i = 0; i < data['data'][k].length; i++)
				{
					data['data'][k][i] = this.dataFormatter.formatValue(data['columns'][i], data['data'][k][i], id);
				}
			}
		}
				
		return data;
	},	
	
	setDataFormatter: function(dataFormatterInstance)
	{
		this.dataFormatter = dataFormatterInstance;
	},
	
	setLoadIndicator: function(indicatorElement)
	{
		this.loadIndicator = $(indicatorElement);	
	},
	
	onScroll: function(liveGrid, offset) 
	{        	
		this.ricoGrid.onBeginDataFetch = this.showFetchIndicator.bind(this);	

		if(Backend.Product)
        {
            Backend.Product.updateHeader(this, offset);
		}
        
		if(Backend.UserGroup)
        {
            Backend.UserGroup.prototype.updateHeader(this, offset);
		}
        
		if(Backend.CustomerOrder)
        {
            Backend.CustomerOrder.prototype.updateHeader(this, offset);
		}
        
		this._markSelectedRows();
	},
	
	onUpdate: function()
	{
		this._markSelectedRows();		
	},
	
	reloadGrid: function()
	{
    	this.ricoGrid.options.requestParameters = [];
        var i = 0;
        
        for (k in this.filters)
    	{
            if (k.substr(0, 7) == 'filter_')
            {
                this.ricoGrid.options.requestParameters[i++] = 'filters[' + k.substr(7, 1000) + ']' + '=' + this.filters[k];
            }
        }
        this.ricoGrid.buffer.clear();
        this.ricoGrid.resetContents();
        this.ricoGrid.requestContentRefresh(0, true);    
        this.ricoGrid.fetchBuffer(0, false, true);
        
		this._markSelectedRows();        
    },
	
	getFilters: function()
	{
        var res = {};

        for (k in this.filters)
    	{
            if (k.substr(0, 7) == 'filter_')
            {
                res[k.substr(7, 1000)] = this.filters[k];
            }
        }      
          
        return res;
    },
    
    getSelectedIDs: function()
    {
        var selected = [];
        
        for (k in this.selectedRows)
        {
            if (true == this.selectedRows[k])
            {
                selected[selected.length] = k;
            }
        }
        
        return selected;
    },
	
    isInverseSelection: function()
    {
        return this.inverseSelection;
    },

	/**
	 *	Select all rows
	 */
	selectAll: function(e)
	{
		this.selectedRows = new Object;		
		this.inverseSelection = this.selectAllInstance.checked;		
		this._markSelectedRows();
    
        e.stopPropagation();
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

    setFilterValue: function(key, value)
    {
		this.filters[key] = value;
    },

	showFetchIndicator: function()
	{
		this.loadIndicator.style.display = '';	
	},

	hideFetchIndicator: function()
	{
		this.loadIndicator.style.display = 'none';	
	},

    resetSelection: function()
    {
		this.selectedRows = new Object;		
		this.inverseSelection = false;	        
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
	 *	Return event target row element
	 */
	_getTargetRow: function(event)
	{
		var target = Event.element(event);

		while (target.tagName != 'TR' && target)  
		{
		  	target = target.parentNode;
		}
		
		return target;
	},
	
	_getHeaderRow: function()
	{
		return this.tableInstance.getElementsByTagName('tr')[0];
	}
}

ActiveGridFilter = Class.create();

ActiveGridFilter.prototype = 
{
    element: null,
    
    activeGridInstance: null,
    
    initialize: function(element, activeGridInstance)
    {
        this.element = element;
        this.activeGridInstance = activeGridInstance;

        this.element.onclick = this.filterFocus.bindAsEventListener(this);
        this.element.onblur = this.filterBlur.bindAsEventListener(this);        
        this.element.onchange = this.setFilterValue.bindAsEventListener(this);  
        
        this.element.filter = this;
        
   		Element.addClassName(this.element, 'activeGrid_filter_blur');          
    },

	filterFocus: function(e)
	{
		if (this.element.columnName == undefined)
		{
			this.element.columnName = this.element.value;	
		}
		
		if (this.element.value == this.element.columnName)
		{
			this.element.value = '';
		}
		
  		Element.removeClassName(this.element, 'activeGrid_filter_blur');
		Element.addClassName(this.element, 'activeGrid_filter_select');		
		
		Event.stop(e);
	},

	filterBlur: function()
	{
		if ('' == this.element.value)
		{
			this.setFilterValue();
			this.element.value = this.element.columnName;
		}

		if (this.element.value == this.element.columnName)
		{
    		Element.addClassName(this.element, 'activeGrid_filter_blur');
			Element.removeClassName(this.element, 'activeGrid_filter_select');
		}
	},
	
	setFilterValue: function()
	{        
		var filterName = this.element.id;
        filterName = filterName.substr(0, filterName.indexOf('_', 7));  
        this.setFilterValueManualy(filterName, this.element.value);
    },
	
	setFilterValueManualy: function(filterName, value)
	{
        this.activeGridInstance.setFilterValue(filterName, value);
		this.activeGridInstance.reloadGrid();        
    },
    
    initFilter: function(e)
    {
        Event.stop(e);
        
        var element = Event.element(e);
        if ('LI' != element.tagName && element.up('li'))
        {
            element = element.up('li');
        }
        
        this.filterFocus(e);
        
		if (element.attributes.getNamedItem('symbol'))
		{
			this.element.value = element.attributes.getNamedItem('symbol').nodeValue;			
		}

	    // range fields
		var cont = element.up('th');
		var min = document.getElementsByClassName('min', cont)[0];
        var max = document.getElementsByClassName('max', cont)[0];
	        
        // show/hide input fields
        if ('><' == this.element.value)
        {
            Element.hide(this.element);
            Element.show(this.element.next('div.rangeFilter'));
            min.focus();
        }
        else
        {
            Element.show(this.element);
            Element.hide(this.element.next('div.rangeFilter'));           

			min.value = '';
			max.value = '';
        	this.element.focus();
        
	        if ('' == this.element.value)
	        {
	            this.element.blur();
	        }        
		}
                      
        // hide menu
        if (element.up('div.filterMenu'))
        {
			Element.hide(element.up('div.filterMenu'));
	        window.setTimeout(function() { Element.show(this.up('div.filterMenu')); }.bind(element), 200);         
		}
    },
    
    updateRangeFilter: function(e)
    {
        var cont = Event.element(e).up('div.rangeFilter');        
        var min = document.getElementsByClassName('min', cont)[0];
        var max = document.getElementsByClassName('max', cont)[0];
		        
		this.element.value = (min.value.length > 0 ? '>=' + min.value + ' ' : '') + (max.value.length > 0 ? '<=' + max.value : '');
		
		this.element.filter.setFilterValue();
		
		if ('' == this.element.value)
		{
			this.initFilter(e);
		}
		
    },
    
    rangeBlur: function(e)
    {
			
	}
}