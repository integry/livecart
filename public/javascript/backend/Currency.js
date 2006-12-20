Backend.Currency = Class.create();
Backend.Currency.prototype = 
{
	formUrl: false,
	
	addUrl: false,

	statusUrl: false,
	
	initialize: function()  
	{
	  
	},
	
	showAddForm: function()
	{
		document.getElementById('currAddMenuLoadIndicator').style.display = 'block';
		new Ajax.Request(
		  			this.formUrl,
					{
					  method: 'get',
					  onComplete: this.doShowAddForm
					}	  										  
					);		  
	},
	
	doShowAddForm: function(request)
	{
		document.getElementById('currAddMenuLoadIndicator').style.display = 'none';
		cont = document.getElementById('addCurr');
		cont.innerHTML = request.responseText;
		slideForm('addCurr', 'currPageMenu');	  	
	},	
	
	add: function(code)
	{
	  	// deactivate submit button and display feedback
	  	button = document.getElementById('addCurr').getElementsByTagName('input')[0];
	  	button.disabled = true;

		document.getElementById('addCurrIndicator').style.display = 'inline';
		  
		new Ajax.Request(
		  			this.addUrl,
					{
					  method: 'get',
					  parameters: 'id=' + code,
					  onComplete: this.addToList
					}	  										  
					);

	},
	
	addToList: function(request)
	{
		// activate submit button and hide feedback
	  	button = document.getElementById('addCurr').getElementsByTagName('input')[0];
	  	button.disabled = false;

		// hide menu
		restoreMenu('addCurr', 'currPageMenu');

		// add currency to list
		item = xml2HtmlElement(request.responseXML.firstChild);

		document.getElementById('addCurrIndicator').style.display = 'none';
		list = document.getElementById('currencyList');

		item.style.display = 'none';
		list.appendChild(item);
		initCurrencyList();
		item.style.display = '';
								
		new Effect.Highlight(item, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});
	},	
	
	setEnabled: function(id, status) 
	{
		url = this.statusUrl + "?id=" + id + "&status=" + status;

		img = document.createElement('img');
		img.src = 'image/indicator.gif';
		img.className = 'activateIndicator';
										
		checkBox = document.getElementById('currencyList_enable_' + id);
		checkBox.parentNode.replaceChild(img, checkBox);
		
		var updater = new Ajax.Updater('currencyList_container_' + id, url);
		this.resetRatesContainer();
	},			
	
	resetRatesContainer: function()
	{
		rateCont = document.getElementById('tabRatesContent');
		while (rateCont.firstChild)
		{
			rateCont.removeChild(rateCont.firstChild);  	
		}  	
	},
	
	setFormUrl: function(url)
	{
		this.formUrl = url;
	},
	
	setAddUrl: function(url)
	{
	  	this.addUrl = url;
	},

	setStatusUrl: function(url)
	{
	  	this.statusUrl = url;
	}	
	  
}