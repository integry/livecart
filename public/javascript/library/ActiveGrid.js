/**
 *	@author Integry Systems
 */

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

	rowCount: 15,

	initialize: function(tableInstance, dataUrl, totalCount, loadIndicator, rowCount, filters)
  	{
		this.tableInstance = tableInstance;
		this.tableInstance.gridInstance = this;
		this.dataUrl = dataUrl;
		this.setLoadIndicator(loadIndicator);
		this.filters = {};
		this.selectedRows = {};

		if (!rowCount)
		{
			rowCount = this.rowCount;
		}

        if (filters)
        {
            this.filters = filters;
        }

		this.ricoGrid = new Rico.LiveGrid(this.tableInstance.id, rowCount, totalCount, dataUrl,
								{
								  prefetchBuffer: true,
								  onscroll: this.onScroll.bind(this),
								  sortAscendImg: $("bullet_arrow_up").src,
						          sortDescendImg: $("bullet_arrow_down").src
								}
							);

		this.ricoGrid.activeGrid = this;

		var headerRow = this._getHeaderRow();
		this.selectAllInstance = headerRow.down('input');
		this.selectAllInstance.onclick = this.selectAll.bindAsEventListener(this);
		this.selectAllInstance.parentNode.onclick = function(e){Event.stop(e);}.bindAsEventListener(this);

		this.ricoGrid.onUpdate = this.onUpdate.bind(this);
		this.ricoGrid.onBeginDataFetch = this.showFetchIndicator.bind(this);
		this.ricoGrid.options.onRefreshComplete = this.hideFetchIndicator.bind(this);

		this.onScroll(this.ricoGrid, 0);

        this.setRequestParameters();
		this.ricoGrid.init();

		var rows = this.tableInstance.down('tbody').getElementsByTagName('tr');
		for (k = 0; k < rows.length; k++)
		{
/*
		  	rows[k].onclick = this.selectRow.bindAsEventListener(this);
		  	rows[k].onmouseover = this.highlightRow.bindAsEventListener(this);
		  	rows[k].onmouseout = this.removeRowHighlight.bindAsEventListener(this);
*/

			Event.observe(rows[k], 'click', this.selectRow.bindAsEventListener(this));
			Event.observe(rows[k], 'mouseover', this.highlightRow.bindAsEventListener(this));
			Event.observe(rows[k], 'mouseout', this.removeRowHighlight.bindAsEventListener(this));
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
			data['data'][k].id = id;

			if (this.dataFormatter)
			{
				for(i = 1; i < data['data'][k].length; i++)
				{
					if(i > 0)
					{
                        data['data'][k][i] = stripHtml(data['data'][k][i]);
                    }

                    var filter = this.filters['filter_' + data['columns'][i]];
                    if (filter && data['data'][k][i].replace)
                    {
                        data['data'][k][i] = data['data'][k][i].replace(new RegExp('(' + filter + ')', 'gi'), '<span class="activeGrid_searchHighlight">$1</span>');
                    }
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

        this.updateHeader(offset);

		this._markSelectedRows();
	},

	updateHeader: function (offset)
	{
		var liveGrid = this.ricoGrid;

		var totalCount = liveGrid.metaData.getTotalRows();
		var from = offset + 1;
		var to = offset + liveGrid.metaData.getPageSize();

		if (to > totalCount)
		{
			to = totalCount;
		}

		if (!this.countElement)
		{
			this.countElement = this.loadIndicator.parentNode.up('div').down('.rangeCount');
			this.notFound = this.loadIndicator.parentNode.up('div').down('.notFound');
		}

        if (!this.countElement)
        {
            return false;
        }

		if (totalCount > 0)
		{
			if (!this.countElement.strTemplate)
			{
				this.countElement.strTemplate = this.countElement.innerHTML;
			}

			var str = this.countElement.strTemplate;
			str = str.replace(/\$from/, from);
			str = str.replace(/\$to/, to);
			str = str.replace(/\$count/, totalCount);

			this.countElement.innerHTML = str;
			this.notFound.style.display = 'none';
			this.countElement.style.display = '';
		}
		else
		{
			this.notFound.style.display = '';
			this.countElement.style.display = 'none';
		}
    },

	onUpdate: function()
	{
		this._markSelectedRows();
	},

	setRequestParameters: function()
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
    },

    reloadGrid: function()
	{
        this.setRequestParameters();

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

    getFilterValue: function(key)
    {
        return this.filters[key];
    },

	showFetchIndicator: function()
	{
		this.loadIndicator.style.display = '';
		this.loadIndicator.parentNode.up('div').down('.notFound').hide();
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

		if (!rowInstance.checkBox)
		{
			rowInstance.checkBox = rowInstance.down('input');
		}

		if (rowInstance.checkBox)
		{
			var checked = this.selectedRows[id];
			if (this.inverseSelection)
			{
				checked = !checked;
			}

			rowInstance.checkBox.checked = checked;

			if (checked)
			{
				rowInstance.addClassName('selected');
			}
			else
			{
				rowInstance.removeClassName('selected');
			}
		}
	},

	_getRecordId: function(rowInstance)
	{
		return rowInstance.recordId;
	},

	/**
	 *	Return event target row element
	 */
	_getTargetRow: function(event)
	{
		return Event.element(event).up('tr');
	},

	_getHeaderRow: function()
	{
		return this.tableInstance.down('tr');
	}
}

ActiveGridFilter = Class.create();

ActiveGridFilter.prototype =
{
    element: null,

    activeGridInstance: null,

    focusValue: null,

    initialize: function(element, activeGridInstance)
    {
        this.element = element;
        this.activeGridInstance = activeGridInstance;
        this.element.onclick = Event.stop.bindAsEventListener(this);
        this.element.onfocus = this.filterFocus.bindAsEventListener(this);
        this.element.onblur = this.filterBlur.bindAsEventListener(this);
        this.element.onchange = this.setFilterValue.bindAsEventListener(this);
        this.element.onkeyup = this.checkExit.bindAsEventListener(this);

        this.element.filter = this;

   		Element.addClassName(this.element, 'activeGrid_filter_blur');

		this.element.columnName = this.element.value;
    },

	filterFocus: function(e)
	{
		if (this.element.value == this.element.columnName)
		{
			this.element.value = '';
		}

		this.focusValue = this.element.value;

  		Element.removeClassName(this.element, 'activeGrid_filter_blur');
		Element.addClassName(this.element, 'activeGrid_filter_select');

		Element.addClassName(this.element.up('th'), 'activeGrid_filter_select');

		Event.stop(e);
	},

	filterBlur: function()
	{
		if ('' == this.element.value.replace(/ /g, ''))
		{
			// only update filter value if it actually has changed
            if ('' != this.focusValue)
			{
                this.setFilterValue();
            }

			this.element.value = this.element.columnName;
		}

		if (this.element.value == this.element.columnName)
		{
    		Element.addClassName(this.element, 'activeGrid_filter_blur');
			Element.removeClassName(this.element, 'activeGrid_filter_select');
		    Element.removeClassName(this.element.up('th'), 'activeGrid_filter_select');
		}
	},

	/**
	 *  Clear filter value on ESC key
	 */
	checkExit: function(e)
	{
		if (27 == e.keyCode)
		{
            this.element.value = '';

            if (this.activeGridInstance.getFilterValue(this.getFilterName()))
            {
                this.setFilterValue();
			    this.filterBlur();
            }

			this.element.blur();
		}
	},

	setFilterValue: function()
	{
        this.setFilterValueManualy(this.getFilterName(), this.element.value);
    },

	setFilterValueManualy: function(filterName, value)
	{
        this.activeGridInstance.setFilterValue(filterName, value);
		this.activeGridInstance.reloadGrid();
    },

    getFilterName: function()
    {
        return this.element.id.substr(0, this.element.id.indexOf('_', 7));
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
		var min = cont.down('.min');
        var max = cont.down('.max');

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
	            this.setFilterValue();
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
        var min = cont.down('.min');
        var max = cont.down('.max');

		if ((parseInt(min.value) > parseInt(max.value)) && max.value.length > 0)
		{
            var temp = min.value;
            min.value = max.value;
            max.value = temp;
        }

		this.element.value = (min.value.length > 0 ? '>=' + min.value + ' ' : '') + (max.value.length > 0 ? '<=' + max.value : '');

		this.element.filter.setFilterValue();

		if ('' == this.element.value)
		{
			this.initFilter(e);
		}
    }
}

ActiveGrid.MassActionHandler = Class.create();
ActiveGrid.MassActionHandler.prototype =
{
    handlerMenu: null,
    actionSelector: null,
    valueEntryContainer: null,
    form: null,
    button: null,

    grid: null,

    initialize: function(handlerMenu, activeGrid, params)
    {
        this.handlerMenu = handlerMenu;
        this.actionSelector = handlerMenu.down('select');
        this.valueEntryContainer = handlerMenu.down('.bulkValues');
        this.form = this.actionSelector.form;
        this.form.handler = this;
        this.button = this.form.down('.submit');

        Event.observe(this.actionSelector, 'change', this.actionSelectorChange.bind(this));
        Event.observe(this.actionSelector.form, 'submit', this.submit.bindAsEventListener(this));

        this.grid = activeGrid;
        this.params = params;
        this.paramz = params;
    },

    actionSelectorChange: function()
    {
		for (k = 0; k < this.valueEntryContainer.childNodes.length; k++)
        {
            if (this.valueEntryContainer.childNodes[k].style)
            {
                Element.hide(this.valueEntryContainer.childNodes[k]);
            }
        }

        Element.show(this.valueEntryContainer);

        if (this.actionSelector.form.elements.namedItem(this.actionSelector.value))
        {
            var el = this.form.elements.namedItem(this.actionSelector.value);
            if (el)
            {
                Element.show(el);
                this.form.elements.namedItem(this.actionSelector.value).focus();
            }
        }
        else if (document.getElementsByClassName(this.actionSelector.value, this.handlerMenu))
        {
			var el = document.getElementsByClassName(this.actionSelector.value, this.handlerMenu)[0];
			if (el)
			{
                Element.show(el);
            }
		}
    },

    submit: function(e)
    {
        if (e)
        {
			Event.stop(e);
		}

		if ('delete' == this.actionSelector.value)
        {
			if (!confirm(this.deleteConfirmMessage))
			{
				return false;
			}
		}

        var filters = this.grid.getFilters();
		this.form.elements.namedItem('filters').value = filters ? Object.toJSON(filters) : '';
        this.form.elements.namedItem('selectedIDs').value = Object.toJSON(this.grid.getSelectedIDs());
        this.form.elements.namedItem('isInverse').value = this.grid.isInverseSelection() ? 1 : 0;

        if ((0 == this.grid.getSelectedIDs().length) && !this.grid.isInverseSelection())
        {
            this.blurButton();
            alert(this.nothingSelectedMessage);
            return false;
        }

        var indicator = document.getElementsByClassName('massIndicator', this.handlerMenu)[0];
        if (!indicator)
        {
            indicator = this.handlerMenu.down('.progressIndicator');
        }

        new LiveCart.AjaxRequest(this.form, indicator , this.submitCompleted.bind(this));

        this.grid.resetSelection();
    },

    submitCompleted: function()
    {
        this.grid.reloadGrid();
        this.blurButton();

        if (this.params && this.params.onComplete)
        {
            this.params.onComplete();
        }
    },

    blurButton: function()
    {
        this.button.disable();
        this.button.enable();
    }
}

function RegexFilter(element, params)
{
	var regex = new RegExp(params['regex'], 'gi');
	element.value = element.value.replace(regex, '');
}

function stripHtml(value)
{
	if (!value || !value.replace)
	{
        return value;
    }

    return value.replace(/<[ \/]*?\w+((\s+\w+(\s*=\s*(?:".*?"|'.*?'|[^'">\s]+))?)+\s*|\s*)[ \/]*>/g, '');
}