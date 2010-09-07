/**
 * @author Integry Systems
 */

Backend.QuickSearch = {
	// timeout values
	TIMEOUT_WHEN_WAITING : 500,
	TIMEOUT_WHEN_TYPING : 1000,

	// --
	query : "",
	previousQuery: null,
	timer : {typing: null, waiting:null},
	popupHideObserved: false,
	nodes : null,
	hasResponse : false,
	isDisplayed: false,

	initNodes : function()
	{
		if(this.nodes == null)
		{
			this.nodes = {};
			$A([
				"QuickSearchClass",
				"QuickSearchFrom",
				"QuickSearchTo",
				"QuickSearchDirection",
				"QuickSearchForm",
				"QuickSearchQuery",
				"QuickSearchResult",
				"QuickSearchContainer",
				"QuickSearchResultOuterContainer"
			]).each(
				function(id)
				{
					this.nodes[id]=$(id);
				}.bind(this)
			);
		}
	},

	onKeyUp:function(obj)
	{
		this.query=obj.value;

		// instant requests
		if(this.TIMEOUT_WHEN_TYPING == 0)
		{
			this.doRequest();
			return;
		}

		// When stop typing make request after TIMEOUT_WHEN_WAITING.
		if(this.timer.waiting)
		{
			window.clearTimeout(this.timer.waiting);
		}
		this.timer.waiting = window.setTimeout(this.doRequest.bind(this), this.TIMEOUT_WHEN_WAITING);

		// When typing make requests every TIMEOUT_WHEN_TYPING.
		if(this.timer.typing == null)
		{
			// using setTimeout instead of setInterval allows to restart interval
			// and make request every time user start typing after pause.
			this.timer.typing = window.setTimeout(
				function()
				{
					if(this.timer.typing)
					{
						window.clearTimeout(this.timer.typing);
						this.timer.typing = null;
					}
				}.bind(this),
				this.TIMEOUT_WHEN_TYPING
			);
			this.doRequest();
		}
	},

	doRequest: function(t)
	{
		if(this.query.length < 3)
		{
			return;
		}

		this.initNodes(); // move to 'constructor'

		if(this.query != this.previousQuery)
		{
			this._setFormOptions({});
			new LiveCart.AjaxRequest(
				this.nodes.QuickSearchForm,
				this.nodes.QuickSearchQuery,
				this.onResponse.bind(this)
			);
			this.previousQuery = this.query;
		}
	},

	onResponse: function(transport)
	{
		this.initNodes(); // move to 'constructor'
		this.nodes.QuickSearchResult.innerHTML = transport.responseText;
		this.hasResponse = true; // flag needed to allow reopen closed result popup when clicking on search query input field.
		this.showResultContainer();
	},

	showResultContainer: function()
	{
		if(this.hasResponse == false)
		{
			return;
		}

		this.initNodes();
		this.nodes.QuickSearchResultOuterContainer.show();
		if(this.popupHideObserved == false)
		{
			Event.observe(document, 'click', this.handleOutsideMenuClick.bindAsEventListener(this));
			//Event.observe(this.nodes.QuickSearchContainer, 'click', function(event){ Event.stop(event);});
			Event.observe(this.nodes.QuickSearchQuery, 'focus', this.showResultContainer.bindAsEventListener(this));
			this.popupHideObserved = true;
		}

		this.isDisplayed = true;
	},

	handleOutsideMenuClick: function(e)
	{
		if (!this.isDisplayed || $(Event.element(e)).up('#' + this.nodes.QuickSearchContainer.id))
		{
			return;
		}

		Event.stop(e);
		this.hideResultContainer();
	},

	hideResultContainer: function()
	{
		this.isDisplayed = false;
		this.initNodes();
		this.nodes.QuickSearchResultOuterContainer.hide();
	},

	next: function(obj, cn)
	{
		this.changePage(obj, cn, 'next');
	},

	previous: function(obj, cn)
	{
		this.changePage(obj, cn, 'previous');
	},

	changePage: function(obj, cn, direction)
	{
		var
			container = $(obj).up("div")
			from = parseInt($A(container.getElementsByClassName("qsFromCount"))[0].innerHTML, 10);
			to = parseInt($A(container.getElementsByClassName("qsToCount"))[0].innerHTML, 10);

		this.initNodes();
		this._setFormOptions({
			cn:cn,
			from:from,
			to:to,
			direction:direction
		});
		new LiveCart.AjaxRequest(
			this.nodes.QuickSearchForm,
			this.nodes.QuickSearchQuery,
			function(transport)
			{
				this.classContainer.innerHTML=transport.responseText;
			}.bind({instance:this, classContainer:container.up("div")})
		);
	},

	_setFormOptions: function(options)
	{
		this.nodes.QuickSearchClass.value=options.cn ? options.cn : "";
		this.nodes.QuickSearchFrom.value=options.from ? options.from : "";
		this.nodes.QuickSearchTo.value=options.to ? options.to: "";
		this.nodes.QuickSearchDirection.value=options.direction ? options.direction : "";
	}
}
