// Rico.LiveGridMetaData -----------------------------------------------------

Rico.LiveGridMetaData = Class.create();

Rico.LiveGridMetaData.prototype = {

	initialize: function( pageSize, totalRows, columnCount, options ) {
		this.pageSize  = pageSize;
		this.totalRows = totalRows;
		this.setOptions(options);
		this.ArrowHeight = 16;
		this.columnCount = columnCount;
	},

	setOptions: function(options) {
		this.options = {
		 largeBufferSize	: 3.0,	// 3 pages
		 nearLimitFactor	: 0.3	// 30% of buffer
		};
		Object.extend(this.options, options || {});
	},

	getPageSize: function() {
		return this.pageSize;
	},

	getTotalRows: function() {
		return this.totalRows ? this.totalRows : 15;
	},

	setTotalRows: function(n) {
		this.totalRows = n;
	},

	getLargeBufferSize: function() {
		return parseInt(this.options.largeBufferSize * this.pageSize);
	},

	getLimitTolerance: function() {
		return parseInt(this.getLargeBufferSize() * this.options.nearLimitFactor);
	}
};

// Rico.LiveGridScroller -----------------------------------------------------

Rico.LiveGridScroller = Class.create();

Rico.LiveGridScroller.prototype = {

	initialize: function(liveGrid, viewPort) {
		this.isIE = navigator.userAgent.toLowerCase().indexOf("msie") >= 0;

		this.liveGrid = liveGrid;
		this.liveGrid.scroller = this;
		this.metaData = liveGrid.metaData;
		this.createScrollBar();

		this.scrollTimeout = null;
		this.lastScrollPos = 0;
		this.viewPort = viewPort;
		this.rows = new Array();
	},

	isUnPlugged: function() {
		return this.scrollerDiv.onscroll == null;
	},

	plugin: function() {
		this.scrollerDiv.onscroll = this.handleScroll.bindAsEventListener(this);
	},

	unplug: function() {
		this.scrollerDiv.onscroll = null;
	},

	sizeIEHeaderHack: function() {
		if ( !this.isIE ) return;
		var headerTable = $(this.liveGrid.tableId + "_header");
		if ( headerTable )
		 headerTable.rows[0].cells[0].style.width =
			(headerTable.rows[0].cells[0].offsetWidth + 1) + "px";
	},

	createScrollBar: function() {
		var visibleHeight = this.liveGrid.viewPort.visibleHeight();
		// create the outer div...
		this.scrollerDiv  = document.createElement("div");
		this.scrollerDiv.className = 'activeGridScroller';

		var scrollerStyle = this.scrollerDiv.style;
		scrollerStyle.float	= "right";
		/*scrollerStyle.left		= this.isIE ? "-6px" : "-4px";*/
		/*scrollerStyle.width		 = "19px";*/
		scrollerStyle.height		= (visibleHeight - 30) + "px";

		// create the inner div...
		this.heightDiv = document.createElement("div");
		this.heightDiv.style.width  = "1px";

		this.heightDiv.style.height = (parseInt(visibleHeight *
						this.metaData.getTotalRows()/this.metaData.getPageSize()) - 3) + "px" ;

		this.scrollerDiv.appendChild(this.heightDiv);
		this.scrollerDiv.onscroll = this.handleScroll.bindAsEventListener(this);

	 var table = $(this.liveGrid.table);
	 table.parentNode.parentNode.insertBefore( this.scrollerDiv, table.parentNode.nextSibling );

		// mouse scroll
		var eventName = this.isIE ? "mousewheel" : "DOMMouseScroll";
		Event.observe(table, eventName,
					function(evt) {
						 if (evt.wheelDelta>=0 || evt.detail < 0) //wheel-up
							this.scrollerDiv.scrollTop -= (2*this.viewPort.rowHeight);
						 else
							this.scrollerDiv.scrollTop += (2*this.viewPort.rowHeight);
						 this.handleScroll(false);
					}.bindAsEventListener(this),
					false);

		// keyboard scroll
		table.tabIndex = 0;

		if (!this.isIE)
		{
			Event.observe(table.down('tbody'), 'click', table.focus.bind(table));
		}

		Event.observe(table, 'keypress', this.handleKeyboardScroll.bind(this));
	 },

	handleKeyboardScroll: function(e)
	{
		if (e.keyCode == Event.KEY_UP)
		{
			this.scrollerDiv.scrollTop -= (2*this.viewPort.rowHeight);
		}
		else if (e.keyCode == Event.KEY_DOWN)
		{
			this.scrollerDiv.scrollTop += (2*this.viewPort.rowHeight);
		}

		this.handleScroll(false);
	},

	updateSize: function() {
		var table = this.liveGrid.table;
		var visibleHeight = this.viewPort.visibleHeight();
		this.heightDiv.style.height = parseInt(visibleHeight *
									this.metaData.getTotalRows()/this.metaData.getPageSize()) + "px";
	},

	rowToPixel: function(rowOffset) {
		return (rowOffset / this.metaData.getTotalRows()) * this.heightDiv.offsetHeight
	},

	moveScroll: function(rowOffset) {
		this.scrollerDiv.scrollTop = this.rowToPixel(rowOffset);
		if ( this.metaData.options.onscroll )
		 this.metaData.options.onscroll( this.liveGrid, rowOffset );
	},

	handleScroll: function(noRefresh) {

	if ( this.scrollTimeout )
		 clearTimeout( this.scrollTimeout );

	var scrollDiff = this.lastScrollPos-this.scrollerDiv.scrollTop;
	if (scrollDiff != 0.00) {
		 var r = this.scrollerDiv.scrollTop % this.viewPort.rowHeight;
		 if (r != 0) {
			this.unplug();
			if ( scrollDiff < 0 ) {
			 this.scrollerDiv.scrollTop += (this.viewPort.rowHeight-r);
			} else {
			 this.scrollerDiv.scrollTop -= r;
			}
			this.plugin();
		 }
	}
	var contentOffset = parseInt(this.scrollerDiv.scrollTop / this.viewPort.rowHeight);

	if (typeof(noRefresh) == 'object')
	{
		this.liveGrid.requestContentRefresh(contentOffset);
	}

	this.viewPort.scrollTo(this.scrollerDiv.scrollTop);

	if ( this.metaData.options.onscroll )
		 this.metaData.options.onscroll( this.liveGrid, contentOffset );

	this.scrollTimeout = setTimeout(this.scrollIdle.bind(this), 300 );
	this.lastScrollPos = this.scrollerDiv.scrollTop;

	},

	scrollIdle: function() {
		if ( this.metaData.options.onscrollidle )
		 this.metaData.options.onscrollidle();
	},

	getOffset: function()
	{
		return parseInt(this.scrollerDiv.scrollTop / this.viewPort.rowHeight);
	}
};

// Rico.LiveGridBuffer -----------------------------------------------------

Rico.LiveGridBuffer = Class.create();

Rico.LiveGridBuffer.prototype = {

	initialize: function(metaData, viewPort) {
		this.startPos = 0;
		this.size	 = 0;
		this.metaData = metaData;
		this.rows	 = new Array();
		this.rowCache = new Object();
		this.updateInProgress = false;
		this.viewPort = viewPort;
		this.maxBufferSize = metaData.getLargeBufferSize() * 2;
		this.maxFetchSize = metaData.getLargeBufferSize();
		this.lastOffset = 0;
	},

	getBlankRow: function() {
		if (!this.blankRow ) {
		 this.blankRow = new Array();
		 for ( var i=0; i < this.metaData.columnCount ; i++ )
			this.blankRow[i] = "&nbsp;";
	 }
	 return this.blankRow;
	},

	loadRows: function(data)
	{
		var data = this.viewPort.liveGrid.activeGrid.getRows(data);

		var newRows = data['data'];

		if ((data["totalCount"] != undefined) && (data["totalCount"] != null))
		{
			this.viewPort.liveGrid.setTotalRows(data["totalCount"]);
			this.size = data["totalCount"];
		}

		// Check if user has set a onRefreshComplete function
		var onRefreshComplete = this.viewPort.liveGrid.options.onRefreshComplete;
		if (onRefreshComplete != null)
		{
			onRefreshComplete();
		}

		return newRows;
	},

	update: function(ajaxResponse, start)
	{
		var newRows = this.loadRows(ajaxResponse);

		var bufferSize = this.metaData.getLargeBufferSize();

		var chunks = this.getChunkIDs(start);
		var chunk;

		if (newRows.length > bufferSize)
		{
			chunks[1]++;
		}

		for (k = 0; k <= 1; k++)
		{
			if ((1 == k) && (chunks[0] == chunks[1]))
			{
				continue;
			}

			if (!this.isCached(chunks[k]))
			{
				chunk = new Array();
				i = -1;
				for (c = (k * bufferSize); i <= bufferSize; c++)
				{
					if (!newRows[c])
					{
						break;
					}

					i++;
					chunk[i] = newRows[c];
				}

				if (chunk.length > bufferSize)
				{
					chunk = chunk.slice(0, bufferSize);
				}
				//chunk = newRows.slice((k * bufferSize), bufferSize);

				// do not store incomplete chunks
				if (0 == k || (chunk.length == bufferSize))
				{
					this.setCache(chunks[k], chunk);
				}
			}
		}

		this.startPos = 0;

		this.rows = this.getRows(start, this.viewPort.rows.length);
	},

	clear: function()
	{
		this.rows = new Array();
		this.rowCache = new Object();
		this.startPos = 0;
		this.size = 0;
	},

	isOverlapping: function(start, size) {
		return ((start < this.endPos()) && (this.startPos < start + size)) || (this.endPos() == 0)
	},

	isNearingTopLimit: function(position)
	{
		var chunks = getChunkIDs(position);
		return (this.isCached(chunks[0]) && this.isCached(chunks[1]));
	},

	endPos: function() {
		return this.startPos + this.rows.length;
	},

	isNearingBottomLimit: function(position) {
		return this.endPos() - (position + this.metaData.getPageSize()) < this.metaData.getLimitTolerance();
	},

	isAtTop: function() {
		return this.startPos == 0;
	},

	isAtBottom: function() {
		return this.endPos() == this.metaData.getTotalRows();
	},

	isNearingLimit: function(position) {
		return ( !this.isAtTop()	&& this.isNearingTopLimit(position)) ||
			 ( !this.isAtBottom() && this.isNearingBottomLimit(position) )
	},

	getFetchSize: function(offset)
	{
		// determine which chunks are required
		var chunks = this.getChunkIDs(offset);

		var bufferSize = this.metaData.getLargeBufferSize();

		var size = $H();

		for (k = 0; k <= 1; k++)
		{
			if (!this.isCached(chunks[k]))
			{
				size[chunks[k]] = bufferSize;
			}
		}

		var totalSize = 0;
		size.each(function(k)
		{
			totalSize += k.value;
		});

		return totalSize;
	},

	getFetchOffset: function(offset)
	{
		// determine which chunks are required
		var chunks = this.getChunkIDs(offset);

		// check if this offset hasn't been cached already
		offset = -1;

		// determine offset start
		for (k = 1; k >= 0; k--)
		{
			if (!this.isCached(chunks[k]))
			{
				offset = this.getChunkOffset(chunks[k]);
				if (this.size && (offset >= this.size))
				{
					offset = -1;
				}
			}
		}

		this.lastOffset = offset;
		return offset;
	},

	/**
	 *  Determine if the chunk has already been cached
	 */
	isCached: function(chunkID)
	{
		return (this.rowCache[chunkID] != undefined);
	},

	getChunkOffset: function(chunkID)
	{
		return chunkID * this.metaData.getLargeBufferSize();
	},

	/**
	 *  Get grid data chunk IDs by offset row ID
	 */
	getChunkIDs: function(offset)
	{
		if (offset >= this.size && this.size > 0)
		{
			offset = this.size - this.metaData.getPageSize() - 1;
		}

		var bufferSize = this.metaData.getLargeBufferSize();

		if (offset < 0)
		{
			offset = 0;
		}

		var startBufferId = Math.floor(offset / bufferSize);

		var endOffset = offset + this.metaData.getPageSize();

		if (endOffset > this.size)
		{
			endOffset = this.size - 1;
		}

		if (endOffset < 0)
		{
			endOffset = 0;
		}

		var endBufferId = Math.floor(endOffset / bufferSize);

		return new Array(startBufferId, endBufferId);
	},

	/**
	 *  Store chunk data to cache
	 */
	setCache: function(chunkID, rowData)
	{
		this.rowCache[chunkID] = rowData;
	},

	getChunk: function(chunkID)
	{
		return this.rowCache[chunkID];
	},

	getRows: function(start, count)
	{
		if (this.size <= start)
		{
			start = this.size - this.metaData.getLargeBufferSize();
			//start = start - this.metaData.getLargeBufferSize();
		}

		var chunks = this.getChunkIDs(start);

		// make sure the chunks are cached
		for (k = 0; k <= 1; k++)
		{
			if (!this.isCached(chunks[k]))
			{
				this.viewPort.liveGrid.requestContentRefresh(start);
				return new Array();
			}
		}

		var rows = $H();

		for (k = 0; k <= 1; k++)
		{
			var chunkOffset = start - this.getChunkOffset(chunks[k]);

			var chunk = this.getChunk(chunks[k]);

			if (chunkOffset >= 0)
			{
				rows[chunks[k]] = chunk.slice(chunkOffset);
			}
			else
			{
				rows[chunks[k]] = chunk.slice(0, 15 + chunkOffset);
			}
		}

		var allRows = new Array();
		rows.each(function(k)
		{
			allRows = allRows.concat(k.value);
		});

		return allRows;
	}
};


//Rico.GridViewPort --------------------------------------------------
Rico.GridViewPort = Class.create();

Rico.GridViewPort.prototype = {

	initialize: function(table, rowHeight, visibleRows, buffer, liveGrid) {
		this.lastDisplayedStartPos = 0;
		this.div = table.parentNode;
		this.table = table
		this.rowHeight = rowHeight;
		this.div.style.height = (this.rowHeight * visibleRows) + "px";
//		this.div.style.overflow = "hidden";
		this.buffer = buffer;
		this.buffer.viewPort = this;
		this.liveGrid = liveGrid;
		this.visibleRows = visibleRows + 1;
		this.lastPixelOffset = 0;
		this.startPos = 0;

		this.rows = this.table.down('tbody').getElementsByTagName('tr');
	},

	populateRow: function(htmlRow, row)
	{
		if (!htmlRow || !row)
		{
			return false;
		}

		if (!htmlRow.checkBox && row[0])
		{
			htmlRow.cells[0].innerHTML = row[0];
			htmlRow.checkBox = htmlRow.cells[0].down('input');
		}

		if (row[0] != '&nbsp;')
		{
			if (!htmlRow.cells[0].firstChild)
			{
				htmlRow.cells[0].appendChild(htmlRow.checkBox);
			}
		}
		else
		{
			htmlRow.cells[0].innerHTML = '';
		}

		for (var j=1; j < row.length; j++)
		{
			htmlRow.cells[j].innerHTML = row[j]
		}

		if (row.id)
		{
			htmlRow.recordId = row.id;
			htmlRow.checkBox.name = 'item[' + row.id + ']';
		}
	},

	bufferChanged: function() {
		this.refreshContents( parseInt(this.lastPixelOffset / this.rowHeight));
	},

	clearRows: function() {
		if (!this.isBlank) {
		 this.liveGrid.table.className = this.liveGrid.options.loadingClass;
		 for (var i=0; i < this.visibleRows; i++)
			this.populateRow(this.rows[i], this.buffer.getBlankRow());
		 this.isBlank = true;
		}
	},

	clearContents: function() {
		this.clearRows();
		this.scrollTo(0);
		this.startPos = 0;
		this.lastStartPos = -1;
	},

	refreshContents: function(startPos) {

		this.isBlank = false;
		var viewPrecedesBuffer = this.buffer.startPos > startPos
		var contentStartPos = viewPrecedesBuffer ? this.buffer.startPos: startPos;
		var contentEndPos = (this.buffer.startPos + this.buffer.size < startPos + this.visibleRows)
								 ? this.buffer.startPos + this.buffer.size
								 : startPos + this.visibleRows;
		var rowSize = contentEndPos - contentStartPos;
		var rows = this.buffer.getRows(contentStartPos, rowSize );
		var blankSize = this.visibleRows - rowSize;
		var blankOffset = viewPrecedesBuffer ? 0: rowSize;
		var contentOffset = viewPrecedesBuffer ? blankSize: 0;

		for (var i=0; i < rows.length; i++) {//initialize what we have
		this.populateRow(this.rows[i + contentOffset], rows[i]);
		}

		for (var i=0; (i < blankSize + 5) && (i < this.rows.length); i++) {// blank out the rest
		this.populateRow(this.rows[i + blankOffset], this.buffer.getBlankRow());
		}

		this.isPartialBlank = blankSize > 0;
		this.lastRowPos = startPos;

		 this.liveGrid.table.className = this.liveGrid.options.tableClass;
		 this.liveGrid.onUpdate();

		// there are some problems with cell content alignment in Firefox,
		// which can be fixed by redrawing the table

		if (this.rows[0] && this.rows[0].style)
		{
			this.rows[0].style.border = '3px solid grey';
			this.rows[0].style.border = '';
		}
	},

	scrollTo: function(pixelOffset) {
		if (this.lastPixelOffset == pixelOffset)
		 return;

		this.refreshContents(parseInt(pixelOffset / this.rowHeight))

		this.lastPixelOffset = pixelOffset;
	},

	visibleHeight: function() {
		return parseInt(RicoUtil.getElementsComputedStyle(this.div, 'height'));
	}

};


Rico.LiveGridRequest = Class.create();
Rico.LiveGridRequest.prototype = {
	initialize: function( requestOffset, options ) {
		this.requestOffset = requestOffset;
	}
};

// Rico.LiveGrid -----------------------------------------------------

Rico.LiveGrid = Class.create();

Rico.LiveGrid.prototype = {

	fetchRequests: new Object(),

	initialize: function( tableId, visibleRows, totalRows, url, options, ajaxOptions ) {

	 this.options = {
				tableClass:			 $(tableId).className,
				loadingClass:		 $(tableId).className,
				scrollerBorderRight: '1px solid #ababab',
				bufferTimeout:		20000,
				sortAscendImg:		'images/sort_asc.gif',
				sortDescendImg:		 'images/sort_desc.gif',
				ajaxSortURLParms:	 [],
				onRefreshComplete:	null,
				requestParameters:	null,
				inlineStyles:		 true
				};
		Object.extend(this.options, options || {});

		this.ajaxOptions = {parameters: null};
		Object.extend(this.ajaxOptions, ajaxOptions || {});

		this.sort = new Rico.LiveGridSort(tableId, this.options)

		this.tableId	 = tableId;
		this.table		 = $(tableId);

		this.addLiveGridHtml();

		var columnCount  = this.sort.headerTable.rows[0].cells.length;
		this.metaData	= new Rico.LiveGridMetaData(visibleRows, totalRows, columnCount, options);
		this.buffer		= new Rico.LiveGridBuffer(this.metaData);

		var rowCount = this.table.down('tbody').rows.length;
		this.viewPort =  new Rico.GridViewPort(this.table,
											'30',
											visibleRows,
											this.buffer, this);

		this.scroller	= new Rico.LiveGridScroller(this,this.viewPort);
		this.options.sortHandler = this.sortHandler.bind(this);

		this.processingRequest = null;
		this.unprocessedRequest = null;

		this.url = url;
	},

	init: function()
	{
		this.initAjax(this.url);
		if ( this.options.prefetchBuffer || this.options.prefetchOffset > 0) {
		 var offset = 0;
		 if (this.options.offset ) {
			offset = this.options.offset;
			this.scroller.moveScroll(offset);
			this.viewPort.scrollTo(this.scroller.rowToPixel(offset));
		 }
		 if (this.options.sortCol) {
			 this.sortCol = options.sortCol;
			 this.sortDir = options.sortDir;
		 }
		}
	},

	onUpdate: function()
	{
	},

	onBeginDataFetch: function()
	{
	},

	addLiveGridHtml: function() {

/*
	 // Check to see if need to create a header table.
	 if (this.table.getElementsByTagName("thead").length > 0 && !'this code sucks'){
		 // Create Table this.tableId+'_header'
		 var tableHeader = this.table.cloneNode(true);
		 tableHeader.setAttribute('id', this.tableId+'_header');
		 tableHeader.setAttribute('class', this.table.className+'_header');

		 // Clean up and insert
		 for( var i = 0; i < tableHeader.tBodies.length; i++ )
		 tableHeader.removeChild(tableHeader.tBodies[i]);
		 this.table.deleteTHead();
		 this.table.parentNode.insertBefore(tableHeader,this.table);
	 }
*/
	this.table.setAttribute('id', this.tableId+'_header');
	this.table.setAttribute('class', this.table.className+'_header');

	new Insertion.Before(this.table, "<div id='"+this.tableId+"_container'></div>");
	this.table.previousSibling.appendChild(this.table);
	new Insertion.Before(this.table,"<div id='"+this.tableId+"_viewport' class='activeGrid_viewport' style='float:left;'></div>");
	this.table.previousSibling.appendChild(this.table);
	},


	resetContents: function() {
		this.scroller.moveScroll(0);
		this.buffer.clear();
		this.viewPort.clearContents();
		this.fetchRequests = new Object()
	},

	sortHandler: function(column) {
		if(!column) return ;
		this.sortCol = column.name;
		this.sortDir = column.currentSort;

		$A(this.table.getElementsByClassName('sortedColumn')).each
		(
			function(element)
			{
				element.removeClassName('sortedColumn');
			}
		);

		var cells = $A(this.table.getElementsByClassName('cell_' + column.name.replace('.', '_')));
		var sortDirection = this.sortDir == 'ASC' ? 0 : cells.length;
		cells.each
		(
			function(element, index)
			{
				$A(element.className.match(/sort[0-9]+/g)).each
				(
					function(className)
					{
						element.removeClassName(className);
					}
				)

				element.addClassName('sortedColumn');
				element.addClassName('sort' + Math.abs(sortDirection - index));
			}
		);

		this.resetContents();
		this.requestContentRefresh(0)
	},

	adjustRowSize: function() {

	},

	setTotalRows: function( newTotalRows ) {
		this.metaData.setTotalRows(newTotalRows);
		this.resetContents();
		this.scroller.updateSize();
		this.scroller.handleScroll(true);
	},

	initAjax: function(url) {
		ajaxEngine.registerRequest( this.tableId + '_request', url );
		ajaxEngine.registerAjaxObject( this.tableId + '_updater', this );
	},

	invokeAjax: function() {
	},

	handleTimedOut: function() {
		//server did not respond in 4 seconds... assume that there could have been
		//an error or something, and allow requests to be processed again...
		this.processingRequest = null;
		//this.processQueuedRequest();
	},

	getQueryString: function(fetchSize, bufferStartPos)
	{
		var queryString = null;

		if (this.options.requestParameters)
		{
			queryString = this._createQueryString(this.options.requestParameters, 0);
		}

		queryString = (queryString == null) ? '' : queryString+'&';

		queryString = queryString+'id='+this.tableId;

		if (fetchSize)
		{
			queryString += '&page_size=' + fetchSize;
		}

		if (bufferStartPos)
		{
			queryString += '&offset=' + bufferStartPos;
		}

		if (this.sortCol)
		{
			queryString = queryString+'&sort_col='+escape(this.sortCol)+'&sort_dir='+this.sortDir;
		}

		return queryString;
	},

	fetchBuffer: function(offset)
	{
		var bufferStartPos = this.buffer.getFetchOffset(offset);

		if (bufferStartPos < 0)
		{
			return false;
		}

		if (this.fetchRequests[bufferStartPos])
		{
			return false;
		}

		var fetchSize = this.buffer.getFetchSize(offset);
		var partialLoaded = false;

		var queryString = this.getQueryString(fetchSize, bufferStartPos);

		if (fetchSize < 1)
		{
			return false;
		}

		this.ajaxOptions.parameters = queryString;
		this.ajaxOptions.method = 'post';

		var url = ajaxEngine.requestURLS[this.tableId + '_request'];

		this.fetchRequests[bufferStartPos] = new RicoGridUpdate(url, this, bufferStartPos);

		this.timeoutHandler = setTimeout( this.handleTimedOut.bind(this), this.options.bufferTimeout);
	},

	onRequestComplete: function(ajaxRequest, bufferStartPos)
	{
		this.fetchRequests[bufferStartPos] = 0;
		this.ajaxUpdate(ajaxRequest, bufferStartPos);
	},

	setRequestParams: function() {
		this.options.requestParameters = [];
		for ( var i=0 ; i < arguments.length ; i++ )
		 this.options.requestParameters[i] = arguments[i];
	},

	requestContentRefresh: function(contentOffset) {
		this.fetchBuffer(contentOffset);
	},

	ajaxUpdate: function(ajaxResponse, bufferStartPos) {
		try {
		 clearTimeout( this.timeoutHandler );
		 this.buffer.update(ajaxResponse.responseText, bufferStartPos);
		 this.viewPort.bufferChanged();
		}
		catch(err) {
		 //console. log(err);
		}
	},

	_createQueryString: function( theArgs, offset ) {
		var queryString = ""
		if (!theArgs)
			return queryString;

		for ( var i = offset ; i < theArgs.length ; i++ ) {
			if ( i != offset )
			queryString += "&";

			var anArg = theArgs[i];

			if ( anArg.name != undefined && anArg.value != undefined ) {
			queryString += anArg.name +  "=" + escape(anArg.value);
			}
			else {
			 var ePos  = anArg.indexOf('=');
			 var argName  = anArg.substring( 0, ePos );
			 var argValue = anArg.substring( ePos + 1 );
			 queryString += argName + "=" + escape(argValue);
			}
		}
		return queryString;
	}

};

//-------------------- ricoLiveGridSort.js
Rico.LiveGridSort = Class.create();

Rico.LiveGridSort.prototype = {

	initialize: function(headerTableId, options) {
		this.headerTableId = headerTableId;
		this.headerTable	= $(headerTableId);
		this.options = options;

		this.setOptions();
		this.applySortBehavior();

		if ( this.options.sortCol ) {
		 this.setSortUI( this.options.sortCol, this.options.sortDir );
		}
	},

	setSortUI: function( columnName, sortDirection ) {
		var cols = this.options.columns;
		for ( var i = 0 ; i < cols.length ; i++ ) {
		 if ( cols[i].name == columnName ) {
			this.setColumnSort(i, sortDirection);
			break;
		 }
		}
	},

	setOptions: function() {
		// preload the images...
		new Image().src = this.options.sortAscendImg;
		new Image().src = this.options.sortDescendImg;

		this.sort = this.options.sortHandler;
		if ( !this.options.columns )
		 this.options.columns = this.introspectForColumnInfo();
		else {
		 // allow client to pass { columns: [ ["a", true], ["b", false] ] }
		 // and convert to an array of Rico.TableColumn objs...
		 this.options.columns = this.convertToTableColumns(this.options.columns);
		}
	},

	applySortBehavior: function() {
		var headerRow	= this.headerTable.rows[0];
		var headerCells = headerRow.cells;

		for ( var i = 0 ; i < headerCells.length ; i++ ) {
		 this.addSortBehaviorToColumn( i, headerCells[i] );
		}
	},

	addSortBehaviorToColumn: function( n, cell ) {
		cell = $(cell);
		if ( this.options.columns[n].isSortable() ) {
		 cell.id			= this.headerTableId + '_' + n;
		 cell.style.cursor  = 'pointer';
		 cell.onclick		 = this.headerCellClicked.bindAsEventListener(this);

		 var sortImg = document.createElement('div');
		 sortImg.innerHTML = '<span class="sortImg" id="' + this.headerTableId + '_img_' + n + '"></span>';

		 cell.appendChild(sortImg.firstChild);
		 //cell.firstDescendant().appendChild(sortImg.firstChild);

		}
	},

	/**
	 *  Handles onclick event for header cell - triggers list sorting
	 */
	headerCellClicked: function(evt)
	{
		var eventTarget = Event.element(evt);
		while ('TH' != eventTarget.tagName)
		{
			eventTarget = eventTarget.parentNode;
		}

		var cellId = eventTarget.id;
		var columnNumber = parseInt(cellId.substring( cellId.lastIndexOf('_') + 1 ));
		var sortedColumnIndex = this.getSortedColumnIndex();

		if ( sortedColumnIndex != -1 )
		{
			if ( sortedColumnIndex != columnNumber )
			{
				this.removeColumnSort(sortedColumnIndex);
				this.setColumnSort(columnNumber, Rico.TableColumn.SORT_ASC);
			}
			else
			{
				this.toggleColumnSort(sortedColumnIndex);
			}
		}
		else
		{
			this.setColumnSort(columnNumber, Rico.TableColumn.SORT_ASC);
		}

		if (this.options.sortHandler)
		{
			this.options.sortHandler(this.options.columns[columnNumber]);
		}
	},

	removeColumnSort: function(n) {
		$(this.headerTableId + '_' + n).removeClassName('sorted');
		this.options.columns[n].setUnsorted();
		this.setSortImage(n);
	},

	setColumnSort: function(n, direction) {
		if(isNaN(n)) return ;
		$(this.headerTableId + '_' + n).addClassName('sorted');
		this.options.columns[n].setSorted(direction);
		this.setSortImage(n);
	},

	toggleColumnSort: function(n) {
		this.options.columns[n].toggleSort();
		this.setSortImage(n);
	},

	setSortImage: function(n) {
		var sortDirection = this.options.columns[n].getSortDirection();

		var sortImageSpan = $( this.headerTableId + '_img_' + n );
		if ( sortDirection == Rico.TableColumn.UNSORTED )
		 sortImageSpan.innerHTML = '';
		else if ( sortDirection == Rico.TableColumn.SORT_ASC )
		 sortImageSpan.innerHTML = '<img src="'	+ this.options.sortAscendImg + '"/>';
		else if ( sortDirection == Rico.TableColumn.SORT_DESC )
		 sortImageSpan.innerHTML = '<img src="'	+ this.options.sortDescendImg + '"/>';
	},

	getSortedColumnIndex: function() {
		var cols = this.options.columns;
		for ( var i = 0 ; i < cols.length ; i++ ) {
		 if ( cols[i].isSorted() )
			return i;
		}

		return -1;
	},

	introspectForColumnInfo: function() {
		var columns = new Array();
		var headerRow	= this.headerTable.rows[0];
		var headerCells = headerRow.cells;
		for ( var i = 0 ; i < headerCells.length ; i++ )
		 columns.push( new Rico.TableColumn( this.deriveColumnNameFromCell(headerCells[i],i), true ) );
		return columns;
	},

	convertToTableColumns: function(cols) {
		var columns = new Array();
		for ( var i = 0 ; i < cols.length ; i++ )
		 columns.push( new Rico.TableColumn( cols[i][0], cols[i][1] ) );
		return columns;
	},

	deriveColumnNameFromCell: function(cell,columnNumber) {
		if (document.getElementsByClassName('fieldName', cell).length > 0)
		{
			return document.getElementsByClassName('fieldName', cell)[0].firstChild.nodeValue;
		}
	}
};

Rico.TableColumn = Class.create();

Rico.TableColumn.UNSORTED  = 0;
Rico.TableColumn.SORT_ASC  = "ASC";
Rico.TableColumn.SORT_DESC = "DESC";

Rico.TableColumn.prototype = {
	initialize: function(name, sortable) {
		this.name		= name;
		this.sortable	= sortable;
		this.currentSort = Rico.TableColumn.UNSORTED;
	},

	isSortable: function() {
		return this.sortable;
	},

	isSorted: function() {
		return this.currentSort != Rico.TableColumn.UNSORTED;
	},

	getSortDirection: function() {
		return this.currentSort;
	},

	toggleSort: function() {
		if ( this.currentSort == Rico.TableColumn.UNSORTED || this.currentSort == Rico.TableColumn.SORT_DESC )
		 this.currentSort = Rico.TableColumn.SORT_ASC;
		else if ( this.currentSort == Rico.TableColumn.SORT_ASC )
		 this.currentSort = Rico.TableColumn.SORT_DESC;
	},

	setUnsorted: function(direction) {
		this.setSorted(Rico.TableColumn.UNSORTED);
	},

	setSorted: function(direction) {
		// direction must by one of Rico.TableColumn.UNSORTED, .SORT_ASC, or .SORT_DESC...
		this.currentSort = direction;
	}

};

/**
 *  Grid data download handler
 */
RicoGridUpdate = Class.create();
RicoGridUpdate.prototype =
{
	bufferStartPos: 0,

	grid: null,

	opts: null,

	url: '',

	/**
	 *  @todo	Instead of (or in addition of) using the timeout, check the mouse button click status.
	 *			As soon as the mouse button is released, it means (?) that the grid is no longer being scrolled
	 *			and the data download could start right away
	 */
	initialize: function(url, grid, bufferStartPos)
	{
		this.bufferStartPos = bufferStartPos;
		this.grid = grid;
		this.opts = grid.ajaxOptions;
		this.opts.onComplete = this.onComplete.bind(this);
		this.url = url;

		if (0 == bufferStartPos)
		{
			// initial load - without delay
			this.process();
		}
		else
		{
			// download the data after 0.3 seconds
			setTimeout(this.process.bind(this), 150);
		}
	},

	process: function()
	{
		var currentOffset = this.grid.buffer.getFetchOffset(this.grid.scroller.getOffset());
		var chunk1 = this.grid.buffer.getChunkIDs(currentOffset);
		var chunk2 = this.grid.buffer.getChunkIDs(this.bufferStartPos);

		// make sure the offset hasn't changed already
		if ((chunk1[0] == chunk2[0]) && (chunk1[1] == chunk2[1]))
		{
			this.grid.onBeginDataFetch();
			new Ajax.Request(this.url, this.opts);
		}
		else
		{
			this.grid.fetchRequests[this.bufferStartPos] = null;
		}
	},

	onComplete: function(ajaxRequest)
	{
		this.grid.onRequestComplete(ajaxRequest, this.bufferStartPos);
	}
}