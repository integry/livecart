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
		if(this.query != this.previousQuery)
		{
			// console.log("[AJAX REQUEST] find: " + this.query );
			new LiveCart.AjaxRequest(
				$("QuickSearchForm"),
				null,
				this.onResponse.bind(this)
			);
			this.previousQuery = this.query;
		}
	},
	
	onResponse: function(transport)
	{
		$("QuickSearchResult").innerHTML = transport.responseText;
		$("QuickSearchResult").show();
	}
}
