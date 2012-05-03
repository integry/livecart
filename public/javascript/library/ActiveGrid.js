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

	controller: null,

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
		this.selectAllInstance = $(headerRow).down('input');
		this.selectAllInstance.onclick = this.selectAll.bindAsEventListener(this);
		this.selectAllInstance.parentNode.onclick = function(e){Event.stop(e);}.bindAsEventListener(this);

		this.ricoGrid.onUpdate = this.onUpdate.bind(this);
		this.ricoGrid.onBeginDataFetch = this.showFetchIndicator.bind(this);
		this.ricoGrid.options.onRefreshComplete = this.hideFetchIndicator.bind(this);

		this.onScroll(this.ricoGrid, 0);

		this.setRequestParameters();
		this.ricoGrid.init();

		var rows = $(this.tableInstance).down('tbody').getElementsByTagName('tr');
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

		this.initColumnWidths();

		// column sorting
		jQuery(this.tableInstance).addClass('draggable forget-ordering');
		dragtable.init();

		// dragtable messes up the input focus
		jQuery('th input', this.tableInstance).click(function()
		{
			this.focus();
		});

		this.initColumnResize();
	},

	setController: function(controller)
	{
		this.controller = controller;
	},

	setColumnWidths: function(widths)
	{
		var usableWidth = this.getUsableWidth();

		jQuery('th.cell_cb', this.tableInstance).width('20px');

		jQuery.each(widths, function(key, value)
		{
			jQuery('th.cell_' + key, this.tableInstance).width((Math.round(usableWidth * value / 100) - 10) + 'px');
		});

		this.initColumnResize();
	},

	getUsableWidth: function()
	{
		var totalWidth = jQuery(this.tableInstance).closest('.activeGrid_viewport').width();
		var usableWidth = totalWidth;
		var usableWidth = usableWidth - jQuery('th.cell_cb', this.tableInstance).width();

		return usableWidth;
	},

	onColumnSort: function()
	{
		var fields = jQuery.map(jQuery('thead .fieldName', this.tableInstance), function(el) { return el.innerHTML; });
		new LiveCart.AjaxRequest(Router.createUrl(this.controller, 'sortColumns', {columns: fields.toJSON()}));
		this.initColumnResize();

		var buffer = this.ricoGrid.buffer;
		buffer.rows = new Array();
		buffer.rowCache = new Object();
	},

	onColumnResize: function(e)
	{
		var usableWidth = this.getUsableWidth();

		var width = {};
		jQuery('th:visible', this.tableInstance).not('.cell_cb').each(function()
		{
			var column = this.className.match(/cell_([^ ]+)/);
			var w = (jQuery(this).width() / usableWidth) * 100;
			width[column[1]] = Math.round(w * Math.pow(10, 2)) / Math.pow(10, 2);
		});

		new LiveCart.AjaxRequest(Router.createUrl(this.controller, 'saveColumnWidth', {width: JSON.stringify(width)}));
	},

	initColumnResize: function()
	{
		jQuery('th.cell_cb', this.tableInstance).width('20px');
		jQuery(this.tableInstance).colResizable({disable: true}).colResizable({onResize: this.onColumnResize.bind(this)});
	},

	initColumnWidths: function()
	{
		var totalWidth = jQuery(this.tableInstance).width();
		jQuery('thead', this.tableInstance).find('.cellt_numeric, .cellt_bool').width(100);
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

		$A($(this.tableInstance).down('tbody').getElementsByTagName('tr')).each(function(row)
		{
			Event.observe(row, 'mouseover',
				function(e)
				{
					window.lastQuickEditNode = Event.element(e);
					window.setTimeout(function() { this.quickEdit(e); }.bind(this), 200);
				}.bindAsEventListener(this) );
		}.bind(this));

		Event.observe($(this.tableInstance).down('tbody'), 'mouseout', function() { window.lastQuickEditNode = null; } );
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

		if (this.activeRow)
		{
			this.activeRow.removeClassName('activeGrid_highlightQuickEdit');
		}

		if (node.tagName.toLowerCase != "tr")
		{
			node = node.up("tr");
		}

		do {
			input = $(node).down("input");
			if (input && input.name)
			{
				m = $(node).down("input").name.match(/item\[(\d+)\]/);
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
					node.addClassName('activeGrid_highlightQuickEdit');
					this.activeRow = node;

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

		if (this.activeRow)
		{
			this.activeRow.removeClassName('activeGrid_highlightQuickEdit');
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

	showColumnMenu: function()
	{
		var container = jQuery(this.tableInstance).data('columnContainer');
		if (!container)
		{
			var container = jQuery(this.tableInstance).closest('.activeGridOuterContainer').find('.activeGridColumnsRoot');
			jQuery(this.tableInstance).data('columnContainer', container);
		}

		jQuery(container).dialog('close');
		jQuery(container).dialog(
			{
				autoOpen: false,
				title: '',
				resizable: false,
				width: 'auto',
				autoResize: true,
			}).dialog('open');

		jQuery('input.checkbox', container).click(function(e)
		{
			var event = jQuery.Event("submit");
			event.checkbox = this;
			jQuery(this).closest('form').trigger(event);
		});

		var title = jQuery(container).closest('.ui-dialog').find('.ui-dialog-title');
		title.html('<input type="text" class="text filter" />');
		var input = title.find('input');

		jQuery(input).click(function(e)
		{
			e.stopPropagation();
			this.focus();
		}).keyup(function()
		{
			var filter = this.value.toLowerCase();
			jQuery(container).find('label').each(function()
			{
				var lab = jQuery(this).html().toLowerCase();
				jQuery(this).closest('p').toggle(lab.indexOf(filter) > -1);
			});
		});
	},

	changeColumns: function(containerClass, e)
	{
		var form = jQuery(e.target).closest('form')[0];
		new LiveCart.AjaxUpdater(form, jQuery(this.tableInstance).closest('.' + containerClass)[0], e.checkbox);
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
			this.countElement = $(this.loadIndicator.parentNode).up('div').down('.rangeCount');
			this.notFound = $(this.loadIndicator.parentNode).up('div').down('.notFound');
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
		Element.addClassName(row, 'ui-state-hover');

		if (cell)
		{
			var value = $(cell).down('span');
			if (value && value.offsetWidth > cell.offsetWidth)
			{
				if (!this.cellContentContainer)
				{
					var cont = cell.up('.activeGridContainer');
					this.cellContentContainer = $(cont).down('.activeGridCellContent');
				}

				var xPos = Event.pointerX(event) - 50 - window.scrollX;
				var yPos = Event.pointerY(event) + 25 - window.scrollY;
				this.cellContentContainer.innerHTML = value.innerHTML;

				// remove progress indicator
				var pI = $(this.cellContentContainer).down('.progressIndicator');
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

		Element.removeClassName(this._getTargetRow(event), 'ui-state-hover');
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
		$(this.loadIndicator.parentNode).up('div').down('.notFound').hide();
		jQuery(this.tableInstance).closest('.activeGrid_viewport').addClass('loading');
	},

	hideFetchIndicator: function()
	{
		this.loadIndicator.style.display = 'none';
		jQuery(this.tableInstance).closest('.activeGrid_viewport').removeClass('loading');
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
			rowInstance.checkBox = $(rowInstance).down('input');
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
				rowInstance.addClassName('ui-state-active');
			}
			else
			{
				rowInstance.removeClassName('ui-state-active');
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
		return $(this.tableInstance).down('tr');
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

		if (jQuery(this.element).hasClass('multiSelect'))
		{
			var title = jQuery('option[value=""]', this.element);
			var titleText = title.html();

			title[0].parentNode.removeChild(title[0]);
			jQuery(this.element).multiselect({noneSelectedText: titleText, selectedList: 3});
		}
	},

	filterOnChange: function(e)
	{
		this.element.blur();

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

			this.element.blur();
		}
	},

	setFilterValue: function()
	{
		if (jQuery(this.element).hasClass('multiSelect'))
		{
			var value = jQuery(this.element).val();
		}
		else
		{
			var value = this.element.value;
		}

		this.setFilterValueManualy(this.getFilterName(), value);
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
		this.button = $(this.form).down('.submit');

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

		var indicator = $(this.handlerMenu).down('.massIndicator');
		if (!indicator)
		{
			indicator = $(this.handlerMenu).down('.progressIndicator');
		}

		this.formerLength = 0;

		if ('blank' == this.actionSelector.options[this.actionSelector.selectedIndex].getAttribute('rel'))
		{
			this.form.target = '_blank';
			this.form.submit();
			return;
		}

		this.request = new LiveCart.AjaxRequest(this.form, indicator , this.dataResponse.bind(this),  {onInteractive: function(func, arg) {window.setTimeout(func.bind(this, arg), 1000); }.bind(this, this.dataResponse.bind(this)) });

		this.progressBarContainer = $(this.handlerMenu).up('div').down('.activeGrid_massActionProgress');
		this.cancelLink = $(this.progressBarContainer).down('a.cancel');
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
		var container = this.nodes.queryContainer;
		jQuery(container).dialog('close');
		jQuery(container).data('originalParent', container.parentNode).dialog(
			{
				autoOpen: false,
				title: Backend.getTranslation('_advanced_search'),
				resizable: false,
				width: 'auto',
				height: 'auto',
				minHeight: 20,
				autoResize: true,
				beforeClose: function(event, ui){
					this.clearEmptyConditions();
			   }.bind(this),
			}).dialog('open');

		this.clearEmptyConditions();
		this.appendCondition();
	},

	appendCondition: function()
	{
		var li = this.appendConditionPlaceholder();
		this.getCondition($(li).down(".condition").value).draw(li);

		jQuery(this.nodes.searchLink).addClass('ui-state-active');
	},

	clearEmptyConditions: function()
	{
		var self = this;

		jQuery('input.value', this.nodes.queryContainer).each(function()
		{
			if (!this.value)
			{
				self.removeConditionPlaceholder(this);
			}
		});

		if (!this.hasConditions())
		{
			jQuery(this.nodes.searchLink).removeClass('ui-state-active');
		}
	},

	hasConditions: function()
	{
		return jQuery('li', this.nodes.queryContainer).length > 0;
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

			if (!this.hasConditions())
			{
				jQuery(this.nodes.queryContainer).dialog('close');
			}
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

		this.nodes.root.addClassName('hasConditions');

		return this.lastConditionContainer;
	},

	removeConditionPlaceholder: function(element)
	{
		if (element.up("ul").getElementsByTagName("li").length > 1)
		{
			this.nodes.root.addClassName('hasConditions');
		}
		else
		{
			this.nodes.root.removeClassName('hasConditions');
		}

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
		this.container = $(container);
		var
			comparision = $(container).down(".comparision"),
			value = $(container).down(".value"),
			value2 = $(container).down(".value2"),
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
			$(container).down(".value2").show();
		}
		else
		{
			$(container).down(".value2").hide();
		}
	},

	isFilled: function(container)
	{
		type = this.getProperty('type');

		// ???
		if(this.getComparision(container) == '><')
		{
			return !!$(container).down(".value").value &&  !!$(container).down(".value2").value;
		}
		// ???

		else if (type == this.TEXT || type == this.NUMERIC)
		{
			return !!$(container).down(".value").value;
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
			return '>=' + $(container).down(".value").value +' <=' +$(container).down(".value2").value;
		}
		else
		{
			return $(container).down(".value").value;
		}
	},

	getComparision: function(container)
	{
		var v = $(container).down(".comparision").value;
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

/*
  dragtable v1.0
  June 26, 2008
  Dan Vanderkam, http://danvk.org/dragtable/
                 http://code.google.com/p/dragtable/

  Instructions:
    - Download this file
    - Add <script src="dragtable.js"></script> to your HTML.
    - Add class="draggable" to any table you might like to reorder.
    - Drag the headers around to reorder them.

  This is code was based on:
    - Stuart Langridge's SortTable (kryogenix.org/code/browser/sorttable)
    - Mike Hall's draggable class (http://www.brainjar.com/dhtml/drag/)
    - A discussion of permuting table columns on comp.lang.javascript

  Licensed under the MIT license.
 */

// Here's the notice from Mike Hall's draggable script:
//*****************************************************************************
// Do not remove this notice.
//
// Copyright 2001 by Mike Hall.
// See http://www.brainjar.com for terms of use.
//*****************************************************************************
dragtable = {
  // How far should the mouse move before it's considered a drag, not a click?
  dragRadius2: 100,
  setMinDragDistance: function(x) {
    dragtable.dragRadius2 = x * x;
  },

  // How long should cookies persist? (in days)
  cookieDays: 365,
  setCookieDays: function(x) {
    dragtable.cookieDays = x;
  },

  // Determine browser and version.
  // TODO: eliminate browser sniffing except where it's really necessary.
  Browser: function() {
    var ua, s, i;

    this.isIE    = false;
    this.isNS    = false;
    this.version = null;
    ua = navigator.userAgent;

    s = "MSIE";
    if ((i = ua.indexOf(s)) >= 0) {
      this.isIE = true;
      this.version = parseFloat(ua.substr(i + s.length));
      return;
    }

    s = "Netscape6/";
    if ((i = ua.indexOf(s)) >= 0) {
      this.isNS = true;
      this.version = parseFloat(ua.substr(i + s.length));
      return;
    }

    // Treat any other "Gecko" browser as NS 6.1.
    s = "Gecko";
    if ((i = ua.indexOf(s)) >= 0) {
      this.isNS = true;
      this.version = 6.1;
      return;
    }
  },
  browser: null,

  // Detect all draggable tables and attach handlers to their headers.
  init: function() {
    // Don't initialize twice
    //if (arguments.callee.done) return;
    //arguments.callee.done = true;
    if (_dgtimer) clearInterval(_dgtimer);
    if (!document.createElement || !document.getElementsByTagName) return;

    dragtable.dragObj.zIndex = 0;
    dragtable.browser = new dragtable.Browser();
    forEach(document.getElementsByTagName('table'), function(table) {
      if (table.className.search(/\bdraggable\b/) != -1) {
        dragtable.makeDraggable(table);
      }
    });
  },

  // The thead business is taken straight from sorttable.
  makeDraggable: function(table) {
    if (table.getElementsByTagName('thead').length == 0) {
      the = document.createElement('thead');
      the.appendChild(table.rows[0]);
      table.insertBefore(the,table.firstChild);
    }

    // Safari doesn't support table.tHead, sigh
    if (table.tHead == null) {
      table.tHead = table.getElementsByTagName('thead')[0];
    }

    var headers = table.tHead.rows[0].cells;
    for (var i = 1; i < headers.length; i++) {
      headers[i].onmousedown = dragtable.dragStart;
    }

		// Replay reorderings from cookies if there are any.
		if (dragtable.cookiesEnabled() && table.id &&
				table.className.search(/\bforget-ordering\b/) == -1) {
			dragtable.replayDrags(table);
		}
  },

  // Global object to hold drag information.
  dragObj: new Object(),

  // Climb up the DOM until there's a tag that matches.
  findUp: function(elt, tag) {
    do {
      if (elt.nodeName && elt.nodeName.search(tag) != -1)
        return elt;
    } while (elt = elt.parentNode);
    return null;
  },

  // clone an element, copying its style and class.
  fullCopy: function(elt, deep) {
    var new_elt = elt.cloneNode(deep);
    new_elt.className = elt.className;
    forEach(elt.style,
        function(value, key, object) {
          if (value == null) return;
          if (typeof(value) == "string" && value.length == 0) return;

          new_elt.style[key] = elt.style[key];
        });
    return new_elt;
  },

  eventPosition: function(event) {
    var x, y;
    if (dragtable.browser.isIE) {
      x = window.event.clientX + document.documentElement.scrollLeft
        + document.body.scrollLeft;
      y = window.event.clientY + document.documentElement.scrollTop
        + document.body.scrollTop;
      return {x: x, y: y};
    }
    return {x: event.pageX, y: event.pageY};
  },

 // Determine the position of this element on the page. Many thanks to Magnus
 // Kristiansen for help making this work with "position: fixed" elements.
 absolutePosition: function(elt, stopAtRelative) {
   var ex = 0, ey = 0;
   do {
     var curStyle = dragtable.browser.isIE ? elt.currentStyle
                                           : window.getComputedStyle(elt, '');
     var supportFixed = !(dragtable.browser.isIE &&
                          dragtable.browser.version < 7);
     if (stopAtRelative && curStyle.position == 'relative') {
       break;
     } else if (supportFixed && curStyle.position == 'fixed') {
       // Get the fixed el's offset
       ex += parseInt(curStyle.left, 10);
       ey += parseInt(curStyle.top, 10);
       // Compensate for scrolling
       ex += document.body.scrollLeft;
       ey += document.body.scrollTop;
       // End the loop
       break;
     } else {
       ex += elt.offsetLeft;
       ey += elt.offsetTop;
     }
   } while (elt = elt.offsetParent);
   return {x: ex, y: ey};
 },

  // MouseDown handler -- sets up the appropriate mousemove/mouseup handlers
  // and fills in the global dragtable.dragObj object.
  dragStart: function(event, id) {
    var el;
    var x, y;
    var dragObj = dragtable.dragObj;

    var browser = dragtable.browser;
    if (browser.isIE)
      dragObj.origNode = window.event.srcElement;
    else
      dragObj.origNode = event.target;
    var pos = dragtable.eventPosition(event);

    // Drag the entire table cell, not just the element that was clicked.
    dragObj.origNode = dragtable.findUp(dragObj.origNode, /T[DH]/);

    // Since a column header can't be dragged directly, duplicate its contents
    // in a div and drag that instead.
    // TODO: I can assume a tHead...
    var table = dragtable.findUp(dragObj.origNode, "TABLE");
    dragObj.table = table;
    dragObj.startCol = dragtable.findColumn(table, pos.x);
    if (dragObj.startCol == -1) return;

    var new_elt = dragtable.fullCopy(table, false);
    new_elt.style.margin = '0';

    // Copy the entire column
    var copySectionColumn = function(sec, col) {
      var new_sec = dragtable.fullCopy(sec, false);
      forEach(sec.rows, function(row) {
        var cell = row.cells[col];
        var new_tr = dragtable.fullCopy(row, false);
        if (row.offsetHeight) new_tr.style.height = row.offsetHeight + "px";
        var new_td = dragtable.fullCopy(cell, true);
        if (cell.offsetWidth) new_td.style.width = cell.offsetWidth + "px";
        new_tr.appendChild(new_td);
        new_sec.appendChild(new_tr);
      });
      return new_sec;
    };

    // First the heading
    if (table.tHead) {
      new_elt.appendChild(copySectionColumn(table.tHead, dragObj.startCol));
    }
    forEach(table.tBodies, function(tb) {
      new_elt.appendChild(copySectionColumn(tb, dragObj.startCol));
    });
    if (table.tFoot) {
      new_elt.appendChild(copySectionColumn(table.tFoot, dragObj.startCol));
    }

    var obj_pos = dragtable.absolutePosition(dragObj.origNode, true);
    new_elt.style.position = "absolute";
    new_elt.style.left = obj_pos.x + "px";
    new_elt.style.top = obj_pos.y + "px";
    new_elt.style.width = dragObj.origNode.offsetWidth + "px";
    new_elt.style.height = dragObj.origNode.offsetHeight + "px";
    new_elt.style.opacity = 0.7;

    // Hold off adding the element until this is clearly a drag.
    dragObj.addedNode = false;
    dragObj.tableContainer = dragObj.table.parentNode || document.body;
    dragObj.elNode = new_elt;

    // Save starting positions of cursor and element.
    dragObj.cursorStartX = pos.x;
    dragObj.cursorStartY = pos.y;
    dragObj.elStartLeft  = parseInt(dragObj.elNode.style.left, 10);
    dragObj.elStartTop   = parseInt(dragObj.elNode.style.top,  10);

    if (isNaN(dragObj.elStartLeft)) dragObj.elStartLeft = 0;
    if (isNaN(dragObj.elStartTop))  dragObj.elStartTop  = 0;

    // Update element's z-index.
    dragObj.elNode.style.zIndex = ++dragObj.zIndex;

    // Capture mousemove and mouseup events on the page.
    if (browser.isIE) {
      document.attachEvent("onmousemove", dragtable.dragMove);
      document.attachEvent("onmouseup",   dragtable.dragEnd);
      window.event.cancelBubble = true;
      window.event.returnValue = false;
    } else {
      document.addEventListener("mousemove", dragtable.dragMove, true);
      document.addEventListener("mouseup",   dragtable.dragEnd, true);
      event.preventDefault();
    }
  },

  // Move the floating column header with the mouse
  // TODO: Reorder columns as the mouse moves for a more interactive feel.
  dragMove: function(event) {
    var x, y;
    var dragObj = dragtable.dragObj;

    // Get cursor position with respect to the page.
    var pos = dragtable.eventPosition(event);

    var dx = dragObj.cursorStartX - pos.x;
    var dy = dragObj.cursorStartY - pos.y;
    if (!dragObj.addedNode && dx * dx + dy * dy > dragtable.dragRadius2) {
      dragObj.tableContainer.insertBefore(dragObj.elNode, dragObj.table);
      dragObj.addedNode = true;
    }

    // Move drag element by the same amount the cursor has moved.
    var style = dragObj.elNode.style;
    style.left = (dragObj.elStartLeft + pos.x - dragObj.cursorStartX) + "px";
    style.top  = (dragObj.elStartTop  + pos.y - dragObj.cursorStartY) + "px";

    if (dragtable.browser.isIE) {
      window.event.cancelBubble = true;
      window.event.returnValue = false;
    } else {
      event.preventDefault();
    }
  },

  // Stop capturing mousemove and mouseup events.
  // Determine which (if any) column we're over and shuffle the table.
  dragEnd: function(event) {
    if (dragtable.browser.isIE) {
      document.detachEvent("onmousemove", dragtable.dragMove);
      document.detachEvent("onmouseup", dragtable.dragEnd);
    } else {
      document.removeEventListener("mousemove", dragtable.dragMove, true);
      document.removeEventListener("mouseup", dragtable.dragEnd, true);
    }

    // If the floating header wasn't added, the mouse didn't move far enough.
    var dragObj = dragtable.dragObj;
    if (!dragObj.addedNode) {
      return;
    }
    dragObj.tableContainer.removeChild(dragObj.elNode);

    // Determine whether the drag ended over the table, and over which column.
    var pos = dragtable.eventPosition(event);
    var table_pos = dragtable.absolutePosition(dragObj.table);
    if (pos.y < table_pos.y ||
        pos.y > table_pos.y + dragObj.table.offsetHeight) {
      return;
    }
    var targetCol = dragtable.findColumn(dragObj.table, pos.x);
	if (targetCol > 0 && targetCol != dragObj.startCol) {
      dragtable.moveColumn(dragObj.table, dragObj.startCol, targetCol);
      dragObj.table.gridInstance.onColumnSort();
      if (dragObj.table.id && dragtable.cookiesEnabled() &&
					dragObj.table.className.search(/\bforget-ordering\b/) == -1) {
        dragtable.rememberDrag(dragObj.table.id, dragObj.startCol, targetCol);
      }
    }
  },

  // Which column does the x value fall inside of? x should include scrollLeft.
  findColumn: function(table, x) {
    var header = table.tHead.rows[0].cells;
    for (var i = 0; i < header.length; i++) {
      //var left = header[i].offsetLeft;
      var pos = dragtable.absolutePosition(header[i]);
      //if (left <= x && x <= left + header[i].offsetWidth) {
      if (pos.x <= x && x <= pos.x + header[i].offsetWidth) {
        return i;
      }
    }
    return -1;
  },

  // Move a column of table from start index to finish index.
  // Based on the "Swapping table columns" discussion on comp.lang.javascript.
  // Assumes there are columns at sIdx and fIdx
  moveColumn: function(table, sIdx, fIdx) {
    var row, cA;
    var i=table.rows.length;
    while (i--){
      row = table.rows[i]
      var x = row.removeChild(row.cells[sIdx]);
      if (fIdx < row.cells.length) {
        row.insertBefore(x, row.cells[fIdx]);
      } else {
        row.appendChild(x);
      }
    }

    // For whatever reason, sorttable tracks column indices this way.
    // Without a manual update, clicking one column will sort on another.
    var headrow = table.tHead.rows[0].cells;
    for (var i=0; i<headrow.length; i++) {
      headrow[i].sorttable_columnindex = i;
    }
  },

  // Are cookies enabled? We should not attempt to set cookies on a local file.
  cookiesEnabled: function() {
    return (window.location.protocol != 'file:') && navigator.cookieEnabled;
  },

  // Store a column swap in a cookie for posterity.
  rememberDrag: function(id, a, b) {
    var cookieName = "dragtable-" + id;
    var prev = dragtable.readCookie(cookieName);
    var new_val = "";
    if (prev) new_val = prev + ",";
    new_val += a + "/" + b;
    dragtable.createCookie(cookieName, new_val, dragtable.cookieDays);
  },

	// Replay all column swaps for a table.
	replayDrags: function(table) {
		if (!dragtable.cookiesEnabled()) return;
		var dragstr = dragtable.readCookie("dragtable-" + table.id);
		if (!dragstr) return;
		var drags = dragstr.split(',');
		for (var i = 0; i < drags.length; i++) {
			var pair = drags[i].split("/");
			if (pair.length != 2) continue;
			var a = parseInt(pair[0]);
			var b = parseInt(pair[1]);
			if (isNaN(a) || isNaN(b)) continue;
			dragtable.moveColumn(table, a, b);
		}
	},

  // Cookie functions based on http://www.quirksmode.org/js/cookies.html
  // Cookies won't work for local files.
  cookiesEnabled: function() {
    return (window.location.protocol != 'file:') && navigator.cookieEnabled;
  },

  createCookie: function(name,value,days) {
    if (days) {
      var date = new Date();
      date.setTime(date.getTime()+(days*24*60*60*1000));
      var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";

		var path = document.location.pathname;
    document.cookie = name+"="+value+expires+"; path="+path
  },

  readCookie: function(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
  },

  eraseCookie: function(name) {
    dragtable.createCookie(name,"",-1);
  }

}

/* ******************************************************************
   Supporting functions: bundled here to avoid depending on a library
   ****************************************************************** */

// Dean Edwards/Matthias Miller/John Resig
// has a hook for dragtable.init already been added? (see below)
var dgListenOnLoad = false;

/* for Mozilla/Opera9 */
if (document.addEventListener) {
  dgListenOnLoad = true;
  document.addEventListener("DOMContentLoaded", dragtable.init, false);
}

/* for Internet Explorer */
/*@cc_on @*/
/*@if (@_win32)
  dgListenOnLoad = true;
  document.write("<script id=__dt_onload defer src=//0)><\/script>");
  var script = document.getElementById("__dt_onload");
  script.onreadystatechange = function() {
    if (this.readyState == "complete") {
      dragtable.init(); // call the onload handler
    }
  };
/*@end @*/

/* for Safari */
if (/WebKit/i.test(navigator.userAgent)) { // sniff
  dgListenOnLoad = true;
  var _dgtimer = setInterval(function() {
    if (/loaded|complete/.test(document.readyState)) {
      dragtable.init(); // call the onload handler
    }
  }, 10);
}

/* for other browsers */
/* Avoid this unless it's absolutely necessary (it breaks sorttable) */
if (!dgListenOnLoad) {
  window.onload = dragtable.init;
}

// Dean's forEach: http://dean.edwards.name/base/forEach.js
/*
  forEach, version 1.0
  Copyright 2006, Dean Edwards
  License: http://www.opensource.org/licenses/mit-license.php
*/

// array-like enumeration
if (!Array.forEach) { // mozilla already supports this
  Array.forEach = function(array, block, context) {
    for (var i = 0; i < array.length; i++) {
      block.call(context, array[i], i, array);
    }
  };
}

// generic enumeration
Function.prototype.forEach = function(object, block, context) {
  for (var key in object) {
    if (typeof this.prototype[key] == "undefined") {
      block.call(context, object[key], key, object);
    }
  }
};

// character enumeration
String.forEach = function(string, block, context) {
  Array.forEach(string.split(""), function(chr, index) {
    block.call(context, chr, index, string);
  });
};

// globally resolve forEach enumeration
var forEach = function(object, block, context) {
  if (object) {
    var resolve = Object; // default
    if (object instanceof Function) {
      // functions have a "length" property
      resolve = Function;
    } else if (object.forEach instanceof Function) {
      // the object implements a custom forEach method so use that
      object.forEach(block, context);
      return;
    } else if (typeof object == "string") {
      // the object is a string
      resolve = String;
    } else if (typeof object.length == "number") {
      // the object is array-like
      resolve = Array;
    }
    resolve.forEach(object, block, context);
  }
};

/**
               _ _____           _          _     _
              | |  __ \         (_)        | |   | |
      ___ ___ | | |__) |___  ___ _ ______ _| |__ | | ___
     / __/ _ \| |  _  // _ \/ __| |_  / _` | '_ \| |/ _ \
    | (_| (_) | | | \ \  __/\__ \ |/ / (_| | |_) | |  __/
     \___\___/|_|_|  \_\___||___/_/___\__,_|_.__/|_|\___|

	v 1.3 - a jQuery plugin by Alvaro Prieto Lauroba

	Licences: MIT & GPL
	Feel free to use or modify this plugin as far as my full name is kept

	If you are going to use this plugin in production environments it is
	strongly recomended to use its minified version: colResizable.min.js

*/


(function ($) {
	var d = $(document),
	F = !1,
	N=null,
	drag = N,
	tables = [],
	count = 0,
	ID = "id",
	PX = "px",
	SIGNATURE = "CRZ",
	I = parseInt,
	M = Math,
	ie = $.browser.msie,
	width = "width",
	attr = "attr",
	divClass='<div class="',
	style="<style type='text/css'>",
	push = "push",
	append = "append",
	removeClass = "removeClass",
	addClass = "addClass",
	removeAttr = "removeAttr",
	bind = "bind",
	extend = "extend",
	mousemove ='mousemove.',
	mouseup = 'mouseup.',
	currentTarget = "currentTarget",
	left = 'left',
	position = 'position',
	styleEnd ="}</style>",
	absolute = ':absolute;',
	imp = '!important;',
	pa = 'padding-',
	zi =':0px'+imp,
	S,
	h = $("head")[append](style+".CRZ{table-layout:fixed;}.CRZ td,.CRZ th{"+pa+left+zi+pa+"right"+zi+"overflow:hidden}.CRC{height:0px;"+position+":relative;}.CRG{margin-left:-5px;"+position+absolute+"z-index:5;}.CRG .CRZ{"+position+absolute+"background-color:red;filter:alpha(opacity=1);opacity:0;width:10px;height:100%;top:0px}.CRL{"+position+absolute+"width:1px}.CRD{ border-left:1px dotted black"+styleEnd);

	try {
		S = sessionStorage;
	} catch (e) {}


	function init(tb, options) {
		var t = $(tb), marginLeft = "marginLeft", marginRight="marginRight", currentStyle ="currentStyle", border="border";
		if (options.disable)
			return destroy(t);
		var id = t.id = t[attr](ID) || SIGNATURE + count++;
		t.p = options.postbackSafe;
		if (!t.is("table") || tables[id])
			return;
		t[addClass](SIGNATURE)[attr](ID, id).before(divClass+'CRC"/>');
		t.opt = options;
		t.g = [];
		t.c = [];
		t.w = t[width]();
		t.gc = t.prev();

		if (options[marginLeft])
			t.gc.css(marginLeft, options[marginLeft]);
		if (options[marginRight])
			t.gc.css(marginRight, options[marginRight]);
		t.cs = I(ie ? tb.cellSpacing || tb[currentStyle][border+"Spacing"] : t.css(border+'-spacing')) || 2;
		t.b = I(ie ? tb[border] || tb[currentStyle][border+"LeftWidth"] : t.css(border+ '-'+ left+'-'+width)) || 1;
		tables[id] = t;
		createGrips(t);
	}

	function destroy(t) {
		var id = t[attr](ID),
		t = tables[id];
		if (!t || !t.is("table"))
			return;
		t[removeClass](SIGNATURE).gc.remove();
		delete tables[id];
	}

	function createGrips(t) {
		var find="find",th = t[find](">thead>tr>th,>thead>tr>td").not(':first'), length ="length";
		if (!th[length])
			th = t[find](">tbody>tr:first>th,>tr:first>th,>tbody>tr:first>td,>tr:first>td").not(':first');
		t.cg = t[find]("col");
		t.ln = th[length];
		if (t.p && S && S[t.id])
			memento(t, th);
		th.each(function (i) {
			var c = $(this);
			var g = $(t.gc[append](divClass+'CRG"></div>')[0].lastChild);
			g.t = t;
			g.i = i;
			g.c = c;
			c.w = c[width]();
			t.g[push](g);
			t.c[push](c);
			c[width](c.w)[removeAttr](width);
			if (i < t.ln - 1)
				g.mousedown(onGripMouseDown)[append](t.opt.gripInnerHtml)[append](divClass + SIGNATURE + '" style="cursor:' + t.opt.hoverCursor + '"></div>');
			else
				g[addClass]("CRL")[removeClass]("CRG");
			g.data(SIGNATURE, {
				i : i,
				t : t[attr](ID)
			});
		});
		t.cg[removeAttr](width);
		syncGrips(t);
		t[find]('td, th').not(th).not('table th, table td').each(function () {
			$(this)[removeAttr](width);
		});
	}

	function memento(t, th) {
		var w,
		m = 0,
		i = 0,
		aux = [];
		if (th) {
			t.cg[removeAttr](width);
			if (t.opt.flush) {
				S[t.id] = "";
				return;
			}
			w = S[t.id].split(";");
			for (; i < t.ln; i++) {
				aux[push](100 * w[i] / w[t.ln] + "%");
				th.eq(i).css(width, aux[i]);
			}
			for (i = 0; i < t.ln; i++)
				t.cg.eq(i).css(width, aux[i]);
		} else {
			S[t.id] = "";
			for (i in t.c) {
				w = t.c[i][width]();
				S[t.id] += w + ";";
				m += w;
			}
			S[t.id] += m;
		}
	}

	function syncGrips(t) {
		t.gc[width](t.w);
		for (var i = 0; i < t.ln; i++) {
			var c = t.c[i];
			t.g[i].css({
				left : c.offset().left - t.offset()[left] + c.outerWidth() + t.cs / 2 + PX,
				height : t.opt.headerOnly ? t.c[0].outerHeight() : t.outerHeight()
			});
		}
	}

	function syncCols(t, i, isOver) {
		var inc = drag.x - drag.l,
		c = t.c[i],
		c2 = t.c[i + 1];
		var w = c.w + inc;
		var w2 = c2.w - inc;
		c[width](w + PX);
		c2[width](w2 + PX);
		t.cg.eq(i)[width](w + PX);
		t.cg.eq(i + 1)[width](w2 + PX);
		if (isOver) {
			c.w = w;
			c2.w = w2;
		}
	}


	function onGripDrag(e) {
		if (!drag)
			return;
		var t = drag.t;
		var x = e.pageX - drag.ox + drag.l;
		var mw = t.opt.minWidth,
		i = drag.i;
		var l = t.cs * 1.5 + mw + t.b;
		var max = i == t.ln - 1 ? t.w - l : t.g[i + 1][position]()[left] - t.cs - mw;
		var min = i ? t.g[i - 1][position]()[left] + t.cs + mw : l;
		x = M.max(min, M.min(max, x));
		drag.x = x;
		drag.css(left, x + PX);
		if (t.opt.liveDrag) {
			syncCols(t, i);
			syncGrips(t);
			var cb = t.opt.onDrag;
			if (cb) {
				e[currentTarget] = t[0];
				cb(e);
			}
		}
		return F}

	function onGripDragOver(e) {
		var unbind = "unbind";
		d[unbind](mousemove + SIGNATURE)[unbind](mouseup + SIGNATURE);
		$("head :last-child").remove();
		if (!drag)
			return;
		drag[removeClass](drag.t.opt.draggingClass);
		var t = drag.t,
		cb = t.opt.onResize;
		if (drag.x) {
			syncCols(t, drag.i, 1);
			syncGrips(t);
			if (cb) {
				e[currentTarget] = t[0];
				cb(e);
			}
		}
		if (t.p && S)
			memento(t);
		drag = N;
	}

	function onGripMouseDown(e) {
		var o = $(this).data(SIGNATURE);
		var t = tables[o.t],
		g = t.g[o.i];
		g.ox = e.pageX;
		g.l = g[position]()[left];
		d[bind](mousemove+ SIGNATURE, onGripDrag)[bind](mouseup+ SIGNATURE, onGripDragOver);
		h[append](style+"*{cursor:" + t.opt.dragCursor +imp +styleEnd);
		g[addClass](t.opt.draggingClass);
		drag = g;
		if (t.c[o.i].l)
			for (var i = 0, c; i < t.ln; i++) {
				c = t.c[i];
				c.l = F;
				c.w = c[width]();
			}
		return F;
	}


	function onResize() {
		for (t in tables) {
			var t = tables[t],
			i,
			mw = 0;
			t[removeClass](SIGNATURE);
			if (t.w != t[width]()) {
				t.w = t[width]();
				for (i = 0; i < t.ln; i++)
					mw += t.c[i].w;
				for (i = 0; i < t.ln; i++)
					t.c[i].css(width, M.round(1000 * t.c[i].w / mw) / 10 + "%").l = 1;
			}
			syncGrips(t[addClass](SIGNATURE));
		}
	}

	$(window)[bind]('resize.' + SIGNATURE, onResize);
	$.fn[extend]({
		colResizable : function (options) {
			var defaults = {
				draggingClass : 'CRD',
				gripInnerHtml : '',
				liveDrag : F,
				minWidth : 15,
				headerOnly : F,
				hoverCursor : "e-resize",
				dragCursor : "e-resize",
				postbackSafe : F,
				flush : F,
				marginLeft : N,
				marginRight : N,
				disable : F,
				onDrag : N,
				onResize : N
			}
			var options = $[extend](defaults, options);
			return this.each(function () {
				init(this, options);
			});
		}
	});
})(jQuery);
