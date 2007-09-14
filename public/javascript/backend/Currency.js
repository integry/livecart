Backend.Currency = Class.create();
Backend.Currency.prototype = 
{
	formUrl: false,
	
	addUrl: false,

	statusUrl: false,
	
	initialize: function()  
	{
	  
	},
	
	getTabUrl: function(url)
	{
		return url;
	},
	
	getContentTabId: function(id)
	{		
		return id + 'Content';
	},

	showAddForm: function()
	{
		new LiveCart.AjaxRequest(this.formUrl, 'currAddMenuLoadIndicator', this.doShowAddForm);		  
	},
	
	hideNewForm: function()
	{
        var menu = new ActiveForm.Slide("currPageMenu");
        menu.hide("addNewCurrency", 'addCurr')     
	},
	
	doShowAddForm: function(request)
	{
		$('addCurr').innerHTML = request.responseText;
		
		var menu = new ActiveForm.Slide("currPageMenu");
		menu.show("addNewCurrency", 'addCurr') 	
	},	
	
	renderList: function(data)
	{
		var template = $('currencyList_template');
	  	var list = $('currencyList');

		for (k = 0; k < data.length; k++)
	  	{			
			z = template.cloneNode(true);
			z = this.renderItem(data[k], z);
			
			list.appendChild(z);
		}		 
	},
	
	renderItem: function(itemData, node)
	{
		node.id = 'currencyList_' + itemData.ID;
		node.style.display = 'block';
		
		checkbox = node.getElementsByTagName('input')[0];
		
		if (1 == itemData.isEnabled)
		{
		  	node.removeClassName('disabled');
			node.getElementsByClassName('listLink')[0].href += itemData.ID;
			checkbox.checked = true;
		}
		else
		{
			checkbox.checked = false;		  
		}
		
		if (1 != itemData.isDefault)
		{
		  	node.removeClassName('default');		  
		  	node.removeClassName('activeList_remove_delete');		  
		  	checkbox.disabled = false;
		  	checkbox.onclick = function() {curr.setEnabled(this); }
		}
		
		node.getElementsByClassName('currTitle')[0].innerHTML = itemData.name;
				
		return node;  
	},
	
	add: function(form)
	{
	  	// deactivate submit button and display feedback
	  	button = $('addCurr').getElementsByTagName('input')[0];
	  	button.disabled = true;
		  
		new LiveCart.AjaxRequest(form, 'addCurrIndicator', this.addToList.bind(this));
	},
	
	addToList: function(originalRequest)
	{
		// activate submit button and hide feedback
	  	button = $('addCurr').getElementsByTagName('input')[0];
	  	button.disabled = false;

		// hide menu..
		Backend.Currency.prototype.hideNewForm();

 	    var itemData = eval('(' + originalRequest.responseText + ")");
		
	  	var template = $('currencyList_template');
	  	
        var activeList = ActiveList.prototype.getInstance('currencyList');
		activeList.addRecord(itemData['ID'], this.renderItem(itemData, template.cloneNode(true)));
        this.resetRatesContainer();
	},	
	
	updateItem: function(originalRequest)
	{
 	    var response = eval('(' + originalRequest.responseText + ")");
		var itemData = response.currency;
        
		var node = $('currencyList_' + itemData.ID);
	  	var template = $('currencyList_template');
		var cl = template.cloneNode(true);
	  	
		node.parentNode.replaceChild(cl, node);
	  	
		this.renderItem(itemData, cl);

        var activeList = ActiveList.prototype.getInstance('currencyList');
        activeList.decorateItems();
        activeList.createSortable();

		new Effect.Highlight(cl, {startcolor:'#FBFF85', endcolor:'#EFF4F6'})
        this.resetRatesContainer();
	},
	
	setEnabled: function(node) 
	{
		p = node;
		while (p.tagName != 'LI')
		{
		  	p = p.parentNode;
		}
		currId = p.id.substr(p.id.length - 3, 3);
		
		url = this.statusUrl + "?id=" + currId + "&status=" + (node.checked - 1 + 1);

		img = document.createElement('img');
		img.src = 'image/indicator.gif';
		img.className = 'activateIndicator';
										
		node.parentNode.replaceChild(img, node);

		new LiveCart.AjaxRequest(url, img, this.updateItem.bind(this));
	},			
	
	resetRatesContainer: function()
	{
		TabControl.prototype.getInstance('tabContainer').resetContent($('tabRates'));
	},
	
	showNoCurrencyMessage: function()
	{
		$('noCurrencies').style.display = ($('currencyList').childNodes.length > 0) ? 'none' : 'block';
	},
	
/************************************
	EDIT
*************************************/
    saveFormat: function(form)
    {
        new LiveCart.AjaxRequest(form, null, this.completeSaveFormat.bind(form)); 
    },

    completeSaveFormat: function()
    {
		var li = this.parentNode.up('li');
        this.parentNode.innerHTML = '';
		ActiveList.prototype.highlight(li, 'yellow');    
    },

/************************************
	RATES
*************************************/

	saveRates: function(form)
	{
		new LiveCart.AjaxRequest(form, null, this.updateRatesForm);
	},
	
	updateRatesForm: function(request)
	{		
		try 
		{
			var rates = request.responseData.values;
			for (k in request.responseData.values)
			{
			  	if ($('rate_' + k))
			  	{
					$('rate_' + k).value = rates[k];
				}
			}	
		}
		catch (e)
		{
			console.log(e);
		}
	},
	
	checkDisabledFields: function()
	{
		form = $('options');
		
		if (form.onchange != null)
		{
			form.onchange = curr.checkDisabledFields;

			formElements = form.getElementsByTagName('input');
			for (k = 0; k < formElements.length; k++)
			{
			  	formElements[k].onclick = form.onchange;
			}
	
			form.onchange = null;
		}

		// enable/disable options container
		if (form.elements.namedItem('updateCb').checked)
		{
			$('feedOptions').removeClassName('disabled');  	
		}	  
		else
		{
			$('feedOptions').addClassName('disabled');  			  
		}
		
		for (k = 0; k < form.elements.length; k++)
		{
			if ((form.elements[k].name != null) && (form.elements[k].name.substr(0, 5) == 'curr_'))
		  	{
				if (form.elements[k].checked)
				{
					Element.removeClassName(form.elements[k].parentNode, 'disabled');
				}
				else
				{
					Element.addClassName(form.elements[k].parentNode, 'disabled');
				}
			}
		}
	},
	
/************************************
	OPTIONS
*************************************/
	
	saveOptions: function(form)
	{
		new LiveCart.AjaxRequest(form, 'optsSaveIndicator', this.optsSaveConfirmation);
	},
	
	optsSaveConfirmation: function()
	{
		new Backend.SaveConfirmationMessage('optsConf');	  
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