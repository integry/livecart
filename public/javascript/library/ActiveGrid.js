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

	quickEditUrlTemplate: null,

	quickEditIdToken : null,

	quickEditContainerState: "hidden",

	activeGridInstanceID : null,

	initialize: function(tableInstance, dataUrl, totalCount, loadIndicator, rowCount, filters)
	{
		this.tableInstance = tableInstance;
		this.activeGridInstanceID=this.tableInstance.id;
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
			Event.observe(rows[k], 'click', this.selectRow.bindAsEventListener(this));

			var cells = rows[k].getElementsByTagName('td');
			for (i = 0; i < cells.length; i++)
			{
				Event.observe(cells[i], 'mouseover', this.highlightRow.bindAsEventListener(this));
			}

			Event.observe(rows[k], 'mouseout', this.removeRowHighlight.bindAsEventListener(this));
		}
	},

	initAdvancedSearch: function(id, availableColumns, advancedSearchColumns, properties)
	{
		this.advancedSearchHandler = new ActiveGridAdvancedSearch(id);
		this.advancedSearchHandler.createAvailableColumnConditions(advancedSearchColumns);
		this.advancedSearchHandler.createAvailableColumnConditions(availableColumns, properties);
		this.advancedSearchHandler.findNodes();
		this.advancedSearchHandler.bindEvents();
	},

	getAdvancedSearchHandler: function()
	{
		return this.advancedSearchHandler;
	},

	initQuickEdit: function(urlTemplate, idToken)
	{
		this.quickEditUrlTemplate = urlTemplate;
		this.quickEditIdToken = idToken;

		$A(this.tableInstance.down('tbody').getElementsByTagName('tr')).each(function(row)
		{
			Event.observe(row, 'mouseover',
				function(e)
				{
					window.lastQuickEditNode = Event.element(e);
					window.setTimeout(function() { this.quickEdit(e); }.bind(this), 200);
				}.bindAsEventListener(this) );
		}.bind(this));

		Event.observe(this.tableInstance.down('tbody'), 'mouseout', function() { window.lastQuickEditNode = null; } );
		Event.observe(document.body, 'mouseover', this.quickEditMouseover.bindAsEventListener(this) );
		Event.observe(this._getQuickEditContainer(), 'click', this.quickEditContainerClicked.bindAsEventListener(this) );
	},

	quickEdit: function(event)
	{
		var
			node = Event.element(event),
			recordID = null,
			m;

		if (window.lastQuickEditNode != node)
		{
			return;
		}

		if (node.tagName.toLowerCase != "tr")
		{
			node = node.up("tr");
		}

		do {
			input = node.down("input");
			if (input && input.name)
			{
				m = node.down("input").name.match(/item\[(\d+)\]/);
			}
			else
			{
				m = [];
			}
			if (m && m.length == 2)
			{
				recordID = m[1];
			}
			else
			{
				node=$(node.up("tr"));
			}
		} while(recordID == null && node);

		if (recordID == null)
		{
			return;
		}

		this.node = node;

		new LiveCart.AjaxRequest(
			this.quickEditUrlTemplate.replace(this.quickEditIdToken, recordID),
			null,
			function(transport)
			{
				var container = this._getQuickEditContainer();
				if(container)
				{
					container.innerHTML = transport.responseText;
					var pos = Position.cumulativeOffset(this.node);

					// translate from grid upper/left corner to page upper/left corner
					var offset = Position.cumulativeOffset(this.node.up(".activeGridContainer"));
					offset[0] *= -1;
					offset[1] *= -1;
					pos = [pos[0] + offset[0], pos[1] + offset[1]];
					container.style.left=(pos[0])+"px";
					container.style.top=(pos[1])+"px";
					container.show();

					if (this.quickEditContainerState == "hidden") // ignore "changed" state!
					{
						this.quickEditContainerState = "shown";
					}
				}

			}.bind(this)
		);
	},

	quickEditMouseover: function(event)
	{
		if (this.quickEditContainerState != "shown")
		{
			return;
		}
		element = Event.element(event);
		if(element.up(".activeGridContainer") == null)
		{
			this.hideQuickEditContainer();
		}
	},

	quickEditContainerClicked: function(event)
	{
		// any click (except on cancel link) in quick edit container set container to clicked state
		if (Event.element(event).hasClassName("cancel") == false)
		{
			this.quickEditContainerState = "clicked";
		}
		else
		{
			this.quickEditContainerState = "shown";
		}
	},

	hideQuickEditContainer : function()
	{
		var container = this._getQuickEditContainer();
		if (!container)
		{
			return;
		}

		container.innerHTML = "";
		container.hide();
		this.containerState = "hidden";
	},

	updateQuickEditGrid: function(jsonData)
	{
		var
			buffer = this.ricoGrid.buffer,
			i,
			rows,
			row,
			done = false;

		rows = this.getRows(jsonData);
		row = rows.data[0];

		for(i=0; i<buffer.rows.length; i++)
		{
			if(row.ID == buffer.rows[i].id)
			{
				buffer.rows[i] = row;
				break;
			}
		}
		for(page in buffer.rowCache)
		{
			if(done)
			{
				break;
			}
			for(rowNr in buffer.rowCache[page])
			{
				if("id" in buffer.rowCache[page][rowNr] == false)
				{
					continue;
				}
				if(done)
				{
					break;
				}

				if(buffer.rowCache[page][rowNr].id == row.id)
				{
					buffer.rowCache[page][rowNr] = row;
					done=true;
				}
			}
		}

		// redraw grid
		this.ricoGrid.viewPort.bufferChanged();
		this.ricoGrid.viewPort.refreshContents(this.ricoGrid.viewPort.lastRowPos);

		$A(document.getElementsByName("item["+row.id+"]")).each(function(input) {
			new Effect.Highlight($(input).up("tr"));
		});
	},

	_getQuickEditContainer: function()
	{
		var parent = $(this.tableInstance), node=null, i=0;

		while (i<25 && parent && parent.hasClassName("activeGridContainer") == false)
		{
			i++;
			parent = $(parent.up("div"));
		}
		if (parent)
		{
			node = parent.getElementsByClassName("quickEditContainer");
		}

		if(node == null || node.length!=1)
		{
			return null;
		}
		return $(node[0]);
	},

	setInitialData: function(data)
	{
		if (data)
		{
			this.ricoGrid.buffer.update(data, 0);
			this.ricoGrid.viewPort.bufferChanged();
			this.ricoGrid.viewPort.refreshContents(0);
		}
		else
		{
			this.ricoGrid.requestContentRefresh(0);
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

					var value = this.dataFormatter.formatValue(data['columns'][i], data['data'][k][i], id) || '';
					data['data'][k][i] = '<span>' + value + '</span>';
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
				this.ricoGrid.options.requestParameters[i++] = 'filters[' + k.substr(7, 1000) + ']' + '=' + encodeURIComponent(this.filters[k]);
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
		var cell = this._getTargetCell(event);
		var row = cell ? cell.parentNode : this._getTargetRow(event);
		Element.addClassName(row, 'activeGrid_highlight');

		if (cell)
		{
			var value = cell.down('span');
			if (value && value.offsetWidth > cell.offsetWidth)
			{
				if (!this.cellContentContainer)
				{
					var cont = cell.up('.activeGridContainer');
					this.cellContentContainer = cont.down('.activeGridCellContent');
				}

				var xPos = Event.pointerX(event) - 50 - window.scrollX;
				var yPos = Event.pointerY(event) + 25 - window.scrollY;
				this.cellContentContainer.innerHTML = value.innerHTML;

				// remove progress indicator
				var pI = this.cellContentContainer.down('.progressIndicator');
				if (pI)
				{
					pI.parentNode.removeChild(pI);
				}

				this.cellContentContainer.style.visibility = 'none';
				this.cellContentContainer.style.display = 'block';

				PopupMenuHandler.prototype.getByElement(this.cellContentContainer, xPos, yPos);

				this.cellContentContainer.style.visibility = 'visible';
			}
		}
	},

	/**
	 *	Remove row highlighting when mouse is moved out of the row
	 */
	removeRowHighlight: function(event)
	{
		if (this.cellContentContainer)
		{
			// hide() not used intentionally
			this.cellContentContainer.style.display = 'none';
		}

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

	/**
	 *	Return event target cell element
	 */
	_getTargetCell: function(event)
	{
		return Event.element(event).up('td');
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
		// this.element.onchange = this.setFilterValue.bindAsEventListener(this);
		this.element.onchange = this.filterOnChange.bindAsEventListener(this);
		this.element.onkeyup = this.checkExit.bindAsEventListener(this);

		this.element.filter = this;

   		Element.addClassName(this.element, 'activeGrid_filter_blur');

		this.element.columnName = this.element.value;
	},

	filterOnChange: function(e)
	{
		var
			element = Event.element(e),
			th = element.up("th"),
			drd = th.down(".dateRange")
		if (th.hasClassName("cellt_date"))
		{
			if("daterange" == element.value.substr(0, 9) && element.tagName.toLowerCase() == "select")
			{
				drd.show();
				return;
			}
			else
			{
				drd.hide();
			}

		}
		this.setFilterValue();
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
		if (27 == e.keyCode || (13 == e.keyCode && !this.element.value))
		{
			this.element.value = '';

			if (this.activeGridInstance.getFilterValue(this.getFilterName()))
			{
				this.setFilterValue();
				this.filterBlur();
			}

			this.element.blur();
		}

		else if (13 == e.keyCode)
		{
			this.filterBlur();
			this.setFilterValue();
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

	updateDateRangeFilter: function(element) // calendar does not generate event, therefore passing node
	{
		var
			element = $(element).up("div.dateRange"),
			// format: "daterange [<datefrom>] | [<dateto>]"
			queryValue = ["daterange", $(element.down(".min").id+"_real").value , "|", $(element.down(".max").id+"_real").value].join(" ").replace(/\s{2,}/, " "),
			select = element.up("th").down("select");

		// find option with value daterange.*, and set its value to queryValue
		$A(select.getElementsByTagName("option")).find(
			function(element)
			{
				return element.value.substr(0,9) == "daterange";
			}
		).value = queryValue;

		select.filter.setFilterValue();
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
	cancelLink: null,
	cancelUrl: '',

	grid: null,
	pid: null,

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
		if (!this.valueEntryContainer)
		{
			return false;
		}

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

		var indicator = this.handlerMenu.down('.massIndicator');
		if (!indicator)
		{
			indicator = this.handlerMenu.down('.progressIndicator');
		}

		this.formerLength = 0;

		if ('blank' == this.actionSelector.options[this.actionSelector.selectedIndex].getAttribute('rel'))
		{
			this.form.target = '_blank';
			this.form.submit();
			return;
		}

		this.request = new LiveCart.AjaxRequest(this.form, indicator , this.dataResponse.bind(this),  {onInteractive: this.dataResponse.bind(this) });

		this.progressBarContainer = this.handlerMenu.up('div').down('.activeGrid_massActionProgress');
		this.cancelLink = this.progressBarContainer.down('a.cancel');
		this.cancelUrl = this.cancelLink.href;
		this.cancelLink.onclick = this.cancel.bind(this);

		this.progressBarContainer.show();
		this.progressBar = new Backend.ProgressBar(this.progressBarContainer);

		this.grid.resetSelection();
	},

	dataResponse: function(originalRequest)
	{
		var response = originalRequest.responseText.substr(this.formerLength + 1);
		this.formerLength = originalRequest.responseText.length;

		var portions = response.split('|');

		for (var k = 0; k < portions.length; k++)
		{
			if (!portions[k])
			{
				continue;
			}

			if ('}' == portions[k].substr(-1))
			{
				if ('{' != portions[k].substr(0, 1))
				{
					portions[k] = '{' + portions[k];
				}

				this.submitCompleted(eval('(' + portions[k] + ')'));

				return;
			}

			response = eval('(' + decode64(portions[k]) + ')');

			// progress
			if (response.progress != undefined)
			{
				this.progressBar.update(response.progress, response.total);
				this.pid = response.pid;
			}
		}
	},

	cancel: function(e)
	{
		this.request.request.transport.abort();
		new LiveCart.AjaxRequest(Backend.Router.setUrlQueryParam(this.cancelUrl, 'pid', this.pid), null, this.completeCancel.bind(this));
		Event.stop(e);
	},

	completeCancel: function(originalRequest)
	{
		var resp = originalRequest.responseData;

		if (resp.isCancelled)
		{
			var progress = this.progressBar.getProgress();
			this.cancelLink.hide();
			this.progressBar.rewind(progress, this.progressBar.getTotal(), Math.round(progress/50), this.submitCompleted.bind(this));
		}
	},

	submitCompleted: function(responseData)
	{
		if (responseData)
		{
			this.request.showConfirmation(responseData);
		}

		this.progressBarContainer.hide();
		this.cancelLink.show();

		this.grid.reloadGrid();
		this.blurButton();

		if (this.params && this.params.onComplete)
		{
			this.params.onComplete();
		}

		if (this.customComplete)
		{
			this.customComplete();
		}
	},

	blurButton: function()
	{
		this.button.disable();
		this.button.enable();
	}
}

ActiveGrid.QuickEdit =
{
	onSubmit: function(obj)
	{
		var form;
		form = $(obj).up("form");
		if(validateForm(form))
		{
			new LiveCart.AjaxRequest(form, null, function(transport) {
				var response = eval( "("+transport.responseText + ")");
				if(response.status == "success")
				{
					this.instance._getGridInstaceFromControl(this.obj).updateQuickEditGrid(transport.responseText);
					this.instance.onCancel(this.obj);
				}
				else
				{
					ActiveForm.prototype.setErrorMessages(this.obj.up("form"), response.errors)
				}
			}.bind({instance: this, obj:obj}));
		}
		return false;
	},

	onCancel: function(obj)
	{
		var gridInstance = this._getGridInstaceFromControl(obj);
		gridInstance.hideQuickEditContainer();
		return false;
	},

	_getGridInstaceFromControl: function(control)
	{
		try {
			// up 3 div's, then get all elements with class name activeGrid,
			// first table should be grid instance.
			// This works for current uses, some future cases may require to rewrite this function.
			return $A($(control).up("div",3).getElementsByClassName("activeGrid")).find(
				function(node)
				{
					return node.tagName.toLowerCase() == "table";
				}
			).gridInstance;

		} catch(e) {
			return null;
		}
	}
}

ActiveGridAdvancedSearch = Class.create();
ActiveGridAdvancedSearch.prototype =
{
	addCondition: function(condition)
	{
		this.conditions[condition.getId()] = condition;
	},

	initialize: function(id)
	{
		this.id = id;
		this.conditions = $H({});
		this.filterString = "";
		if(ActiveGridAdvancedSearch.prototype.initCallbacks)
		{
			$A(ActiveGridAdvancedSearch.prototype.initCallbacks).each(
				function(callback)
				{
					callback(this);
				}.bind(this)
			);
		}
	},

	createAvailableColumnConditions: function(availableColumns, properties)
	{
		var
			conditionProperties;
		this.availableColumns = $H(availableColumns);
		this.availableColumns.each(function(item)
		{
			if (item[1].type == null || item[0] == 'hiddenType')
			{
				return;
			}
			conditionProperties = {type: item[1].type};
			if(conditionProperties.type == 'date')
			{
				// date type filter values (same as in ActiveGrid)
				conditionProperties.dateFilterValues = properties.dateFilterValues;
			}
			this.addCondition
			(
				new ActiveGridAdvancedSearchCondition(item[0], item[1].name, conditionProperties)
			);
		}.bind(this));
	},

	findNodes: function()
	{
		if (typeof this.nodes == "undefined")
		{
			this.nodes = {};
		}
		this.nodes.root = $(this.id + "_AdvancedSearch");
		this.nodes.queryContainer = this.nodes.root.down(".advancedSearchQueryContainer");
		this.nodes.searchLink = this.nodes.root.down(".advancedSearchLink");

		this.nodes.queryItems = this.nodes.root.down(".advancedQueryItems");
	},

	bindEvents: function()
	{
		Event.observe(this.nodes.searchLink, "click", this.linkClicked.bindAsEventListener(this));
		Event.observe(this.nodes.queryItems, "change", this.conditionItemChanged.bindAsEventListener(this));
		Event.observe(this.nodes.queryItems, "click", this.conditionItemClick.bindAsEventListener(this))
	},

	getCondition: function(id)
	{
		return typeof this.conditions[id] == "undefined"
			? null
			: this.conditions[id];
	},

	linkClicked: function()
	{
		this.appendCondition();
	},

	appendCondition: function()
	{
		var li = this.appendConditionPlaceholder();
		this.getCondition(li.down(".condition").value).draw(li);
	},

	conditionItemChanged: function(event)
	{
		var
			element = Event.element(event),
			condition = this.getCondition( element.up("li").down(".condition").value );
		if(element.hasClassName('condition'))
		{
			condition.draw(element.up("li"));
		}
		if(element.hasClassName('comparision'))
		{
			condition.comparisionChanged(element.up("li"));
		}
		this.appendConditionIfLastFilled(element);
		this.setActiveGridFilterValues();
	},

	conditionItemClick: function(event)
	{
		var element = Event.element(event);
		if(element.hasClassName("deleteCross"))
		{
			this.removeConditionPlaceholder(element);
		}
	},

	appendConditionIfLastFilled: function(element)
	{
		var
			container = element.up('li'),
			condition = this.getCondition(container.down('.condition').value);

		if(this.lastConditionContainer == container && condition.isFilled(container))
		{
			this.appendCondition();
		}
	},

	setActiveGridFilterValues: function()
	{
		var
			gridInstance = window.activeGrids[this.id],
			containers = $A(this.nodes.queryItems.getElementsByTagName("li")).findAll(function(itemContainer, item) { return itemContainer == item.parentNode; }.bind(this, this.nodes.queryItems)), // only 'top' level child nodes.
			condition,
			key, value, comparision,
			z = [];

		gridInstance.filters = {};

		while(container = $(containers.shift()))
		{
			condition = this.getCondition(container.down(".condition").value );
			if(condition && condition.isFilled(container))
			{
				key = 'filter_' + condition.getId();
				comparision = condition.getComparision(container);
				if(comparision == '><')
				{
					comparision = '';
				}
				value = comparision + condition.getValue(container);
				gridInstance.setFilterValue(key, value);
				z.push(key+'__'+value);
			}
		}

		// if something changed in filter, reload
		value = z.join('|');
		if(value != this.filterString)
		{
			gridInstance.reloadGrid();
			this.filterString = value
		}
	},

	appendConditionPlaceholder: function()
	{
		var
			li = document.createElement("li"),
			select = document.createElement("select"),
			a = document.createElement("a");
		this.nodes.queryItems.appendChild(li);
		li.appendChild(a);
		a.addClassName("deleteCross");
		a.href="javascript:void(0);";
		li.appendChild(select);

		this.conditions.each(
			function(select, item)
			{
				var condition = item[1];
				addOption(select, condition.getId(), condition.getName());
			}.bind(this, select)
		);
		select.addClassName("condition");
		this.lastConditionContainer = $(li);
		return this.lastConditionContainer;
	},

	removeConditionPlaceholder: function(element)
	{
		element.up("ul").removeChild(element.up("li"));
		this.setActiveGridFilterValues();
	},

	registerInitCallback: function(callback)
	{
		if(!ActiveGridAdvancedSearch.prototype.initCallbacks)
		{
			ActiveGridAdvancedSearch.prototype.initCallbacks = [];
		}
		ActiveGridAdvancedSearch.prototype.initCallbacks.push(callback);
	}
}

ActiveGridAdvancedSearchCondition = Class.create();
ActiveGridAdvancedSearchCondition.prototype =
{
	TEXT: 'text',
	NUMERIC: 'numeric',
	BOOL: 'bool',
	DATE: 'date',

	initialize: function(id, name, properties)
	{
		this.id = id;
		this.name = name;
		this.properties = properties;
	},

	getName: function()
	{
		return this.name;
	},

	getId: function()
	{
		return this.id;
	},

	getProperty: function(key)
	{
		if(typeof this.properties == "undefined")
		{
			this.properties = $H({});
		}
		if(key == 'type' && this.properties[key] == "number") // 'number' and 'numeric' means the same.
		{
			this.properties[key] = this.NUMERIC;
		}
		return typeof this.properties[key] == "undefined"
			? arguments.length <= 2 ? arguments[1] : null
			: this.properties[key];
	},

	setType: function(type)
	{
		if(this.getProperty('type') != type) // getProperty() also initializes this.properties to hashmap, if it is broken.
		{
			if(type == 'number')
			{
				type = this.NUMERIC;
			}
			this.properties['type'] = type;
		}
	},

	draw: function(container) // This drawing is for 'field value' conditions. Replace with custom draw.
	{
		this.container = container;
		var
			comparision = container.down(".comparision"),
			value = container.down(".value"),
			value2 = container.down(".value2"),
			type;
		if (comparision /* not found */) { } else
		{
			comparision = document.createElement('select');
			container.appendChild(comparision);
			comparision = $(comparision);
			comparision.addClassName('comparision');
			value = document.createElement('input');
			container.appendChild(value);
			value = $(value);
			value.addClassName('value');
			value2 = document.createElement('input');
			container.appendChild(value2);
			value2 = $(value2);
			value2.addClassName('value2');
		}

		// change comparision dropdown for this condition
		comparision.innerHTML = '';
		comparision.show();
		value.show();
		value2.hide();
		type = this.getProperty('type');
		if (type == 'text')
		{
			addOption(comparision, '=', $T("_grid_equals"));
			comparision.hide();
			value.focus();
		}
		else if(type == 'numeric')
		{
			value.show();
			addOption(comparision, '=',  $T("_grid_equals"));
			addOption(comparision, '<>', $T("_grid_not_equal"));
			addOption(comparision, '>',  $T("_grid_greater"));
			addOption(comparision, '<',  $T("_grid_less"));
			addOption(comparision, '>=', $T("_grid_greater_or_equal"));
			addOption(comparision, '<=', $T("_grid_less_or_equal"));
			addOption(comparision, '><', $T("_grid_range"));
			value.focus();
		}
		else if(type == 'bool')
		{
			addOption(comparision, '1', $T("_yes"));
			addOption(comparision, '0', $T("_no"));
			comparision.focus();
			value.hide();
		}
		else if(type == 'date')
		{
			$H(this.getProperty('dateFilterValues')).each(function(comparision, f) {
				var key = 0, value=1
				addOption(comparision, f[value], $T(f[key]));
			}.bind(this, comparision));
			comparision.focus();
			value.hide();
		}
	},

	comparisionChanged: function(container)
	{
		if(this.getComparision(container) == '><')
		{
			container.down(".value2").show();
		}
		else
		{
			container.down(".value2").hide();
		}
	},

	isFilled: function(container)
	{
		type = this.getProperty('type');

		// ???
		if(this.getComparision(container) == '><')
		{
			return !!container.down(".value").value &&  !!container.down(".value2").value;
		}
		// ???

		else if (type == this.TEXT || type == this.NUMERIC)
		{
			return !!container.down(".value").value;
		}
		else if(type == this.BOOL || type == this.DATE)
		{
			return true;
		}
	},

	getValue: function(container)
	{
		if(this.getComparision(container) == '><')
		{
			return '>=' + container.down(".value").value +' <=' +container.down(".value2").value;
		}
		else
		{
			return container.down(".value").value;
		}
	},

	getComparision: function(container)
	{
		var v = container.down(".comparision").value;
		if(v == '=')
		{
			v = '';
		}
		return v ? v : "";
	}
}

function $T()
{
	if(arguments.length == 2)
	{
		this[arguments[0]] = arguments[1];
	}
	else
	{
		return this[arguments[0]] ? this[arguments[0]] : arguments[0];
	}
}

function addOption(dropdown, value, text)
{
	var option = document.createElement('option');
	dropdown.appendChild(option);
	option.value = value;
	option.innerHTML = text;
	return option;
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