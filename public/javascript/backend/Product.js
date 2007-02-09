Backend.Product = 
{
	productTabCopies: new Array(),
	
	showAddForm: function(container, categoryID)
	{
		this.createProductTabCopy(categoryID, container);
		var url = Backend.Category.links.addProduct.replace('_id_', categoryID);

		new LiveCart.AjaxUpdater(url, container, document.getElementsByClassName('progressIndicator', container)[0]);

	},
	
	cancelAddProduct: function(categoryID, container)
	{
		this.restoreProductTab(categoryID, container);  
	},
	
	createProductTabCopy: function(categoryID, container)
	{
		this.productTabCopies[categoryID] = container.cloneNode(true);
	},
	
	restoreProductTab: function(categoryID, container)
	{
		container.parentNode.replaceChild(this.productTabCopies[categoryID], container);
	},

	initAddForm: function(categoryID)
	{
		var textareas = $('tabProductsContent_' + categoryID).getElementsByTagName('textarea');
		for (k = 0; k < textareas.length; k++)
		{
			tinyMCE.execCommand('mceAddControl', true, textareas[k].id);
		}

		var expander = new SectionExpander();	  
	},

	generateHandle: function(titleElement)
	{
	  	handleElement = titleElement.form.elements.namedItem('handle');
	  	handleElement.value = ActiveForm.prototype.generateHandle(titleElement.value);
	},
	
	multiValueSelect: function(anchor, state)
	{
	  	while (('FIELDSET' != anchor.tagName) && (undefined != anchor.parentNode))
	  	{
		    anchor = anchor.parentNode;
		}

		checkboxes = anchor.getElementsByTagName('input');
		
		for (k = 0; k < checkboxes.length; k++)
		{
		  	checkboxes[k].checked = state;
		}
		
	},
	
	getWeightMultipliers: function(form)
	{
		var unitsType = (form.elements.namedItem('unitsType').value == 'english') ? 'english' : 'metric';
		
		if ('english' == unitsType)
		{
		  	hiMultiplier = 453.59237;
		  	loMultiplier = 28.3495231;
		}	
		else
		{
		  	hiMultiplier = 1000;
		  	loMultiplier = 1;	  
		}
	
		var res = new Array(2);
		res[0] = hiMultiplier;
		res[1] = loMultiplier;
		return res;
	},
	
	updateShippingWeight: function(field)
	{
	  	// get parent form
		var form = field.form;

		var multipliers = this.getWeightMultipliers(form);
		
		var hiValue = form.elements.namedItem('shippingHiUnit').value;
		var loValue = form.elements.namedItem('shippingLoUnit').value;	
		
		form.elements.namedItem('shippingWeight').value = (hiValue * multipliers[0]) + (loValue * multipliers[1]);	  	
	},
	
	switchUnitTypes: function(anchor)
	{
	  	// get parent form
		form = anchor;		
		while (('FORM' != form.tagName) && (undefined != form.parentNode))
	  	{
		    form = form.parentNode;
		}

		var unitsType = (form.elements.namedItem('unitsType').value == 'english') ? 'metric' : 'english';

		form.elements.namedItem('unitsType').value = unitsType;

		// change captions
		var unitsType = (form.elements.namedItem('unitsType').value == 'english') ? 'metric' : 'english';
		anchor.parentNode.getElementsByTagName('A')[0].innerHTML = document.getElementsByClassName(unitsType + '_title', form)[0].innerHTML;
		form.getElementsByClassName('shippingUnit_hi', form)[0].innerHTML = document.getElementsByClassName(unitsType + '_hi', form)[0].innerHTML;
		form.getElementsByClassName('shippingUnit_lo', form)[0].innerHTML = document.getElementsByClassName(unitsType + '_lo', form)[0].innerHTML;

		var weight = form.elements.namedItem('shippingWeight').value;
		var multipliers = this.getWeightMultipliers(form);
		
		var hiValue = Math.floor(weight / multipliers[0]);
		var loValue = (weight - (hiValue * multipliers[0])) / multipliers[1];
		loValue = Math.round(loValue * 1000) / 1000;
		
		if ('english' == unitsType)
		{
		  	loValue = loValue.toFixed(0);
		}
		
		form.elements.namedItem('shippingHiUnit').value = hiValue;
		form.elements.namedItem('shippingLoUnit').value = loValue;		
	},
	
	saveForm: function(form)
	{
	  	tinyMCE.triggerSave();
		var saveHandler = new Backend.Product.saveHandler(form);
		new LiveCart.AjaxRequest(form, 'tabProductsIndicator', saveHandler.saveComplete.bind(saveHandler));
	}
}

Backend.Product.saveHandler = Class.create();
Backend.Product.saveHandler.prototype = 
{
  	initialize: function(form)
  	{
	    this.form = form;
	},
	
	saveComplete: function(originalRequest)
	{
	  	ActiveForm.prototype.resetErrorMessages(this.form);
		eval('var response = ' + originalRequest.responseText);
	  	
		if (response.errors)
		{
			ActiveForm.prototype.setErrorMessages(this.form, response.errors);  
		}
		else
		{
			// reset form and add more products
			if (response.addmore)
			{
				console.log('resetting form');
				this.form.reset();  
			}

			// product customization content  	
			else
			{
			  
			}
			
		}
	}
}