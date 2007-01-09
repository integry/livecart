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
					  onComplete: this.addToList.bind(this)
					}	  										  
					);

	},
	
	addToList: function(originalRequest)
	{
		// activate submit button and hide feedback
	  	button = document.getElementById('addCurr').getElementsByTagName('input')[0];
	  	button.disabled = false;

		// hide menu
		restoreMenu('addCurr', 'currPageMenu');

 	    eval('var itemData = ' + originalRequest.responseText);
		
	  	var template = $('currencyList_template');
	  	
	  	var list = $('currencyList');
		var node = template.cloneNode(true);
		node = this.renderItem(itemData, node);
		list.appendChild(node);
  	
		initCurrencyList();
				
		new Effect.Highlight(node, {startcolor:'#FBFF85', endcolor:'#EFF4F6'});
	},	
	
	updateItem: function(originalRequest)
	{
 	    eval('var itemData = ' + originalRequest.responseText);
		
		var node = $('currencyList_' + itemData.ID);
	  	var template = $('currencyList_template');
		var cl = template.cloneNode(true);
	  	
		node.parentNode.replaceChild(cl, node);
	  	
		this.renderItem(itemData, cl);
		initCurrencyList();
		new Effect.Highlight(cl, {startcolor:'#FBFF85', endcolor:'#EFF4F6'})
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
		rateCont = document.getElementById('tabRatesContent');
		while (rateCont.firstChild)
		{
			rateCont.removeChild(rateCont.firstChild);  	
		}  	
	},
	
	showNoCurrencyMessage: function()
	{
		$('noCurrencies').style.display = ($('currencyList').childNodes.length > 0) ? 'none' : 'block';	 	 	  	
	},
	
/************************************
	RATES
*************************************/

	saveRates: function(form)
	{
		new LiveCart.AjaxRequest(form, 'rateSaveIndicator', this.updateRatesForm);
	},
	
	updateRatesForm: function(request)
	{		
		try 
		{
			eval('var rates = ' + request.responseText);
			for (k in rates)
			{
			  	if ($('rate_' + k))
			  	{
					$('rate_' + k).value = rates[k];
				}
			}	

			document.getElementById('rateSaveIndicator').style.display = 'none';	
			new Backend.SaveConfirmationMessage('rateConf');
		}
		catch (e)
		{
			//addlog(e);
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