Backend.Product = 
{
	productTabCopies: new Array(),

	formTabCopies: new Array(),
	
	showAddForm: function(container, categoryID)
	{
		this.productTabCopies[categoryID] = container;
		
		tabContainer = container.parentNode;
		
		// product form has already been downloaded
		if (this.formTabCopies[categoryID])
		{
			tabContainer.replaceChild(this.formTabCopies[categoryID], container);
		}
		
		// retrieve product form
		else
		{
			var url = Backend.Category.links.addProduct.replace('_id_', categoryID);	
			new LiveCart.AjaxUpdater(url, container.parentNode, document.getElementsByClassName('progressIndicator', container)[0]);
		}
		
		this.initAddForm(categoryID);
	},
	
	cancelAddProduct: function(categoryID, container)
	{
		var textareas = container.getElementsByTagName('textarea');
		for (k = 0; k < textareas.length; k++)
		{
			tinyMCE.execCommand('mceRemoveControl', true, textareas[k].id);
		}

		this.formTabCopies[categoryID] = container;	
		container.parentNode.replaceChild(this.productTabCopies[categoryID], container);
	},
	
	resetAddForm: function(form)
	{
		textareas = form.getElementsByTagName('textarea'); 
		for(k = 0; k < textareas.length; k++) 
		{ 
			tinyMCE.execInstanceCommand(textareas[k].id, 'mceSetContent', true, '', true); 
		}		
	},
	
	initAddForm: function(categoryID)
	{
		tinyMCE.idCounter = 0;
		var textareas = $('tabProductsContent_' + categoryID).getElementsByTagName('textarea');
		for (k = 0; k < textareas.length; k++)
		{
			tinyMCE.execCommand('mceAddControl', true, textareas[k].id);
		}

		var expander = new SectionExpander();	  
		
		// specField entry logic (multiple value select)
		var containers = document.getElementsByClassName('multiValueSelect', $('tabProductsContent_' + categoryID));
		for (k = 0; k < containers.length; k++)
		{
			new Backend.Product.specFieldEntryMultiValue(containers[k]);  
		}		
		
		// single value select
		var specFieldContainer = document.getElementsByClassName('specification', $('tabProductsContent_' + categoryID))[0];

		if (specFieldContainer)
		{
			var selects = specFieldContainer.getElementsByTagName('select');
			for (k = 0; k < selects.length; k++)
			{
				new Backend.Product.specFieldEntrySingleSelect(selects[k]);  
			}						  
		}
	},

	toggleSkuField: function(checkbox)
	{
	  	var skuField = checkbox.form.elements.namedItem('sku');
	  	skuField.disabled = checkbox.checked;
	  	if (checkbox.checked)
	  	{
		    skuField.value = '';
		}
		else
		{
		  	skuField.focus();
		}
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
		var saveHandler = new Backend.Product.saveHandler(form);
		new LiveCart.AjaxRequest(form, 'tabProductsIndicator', saveHandler.saveComplete.bind(saveHandler));
	},
	
   updateHeader: function ( liveGrid, offset ) {
      $('bookmark').innerHTML = "Listing products " + (offset+1) + " - " + (offset+liveGrid.metaData.getPageSize()) + " of " + 
      liveGrid.metaData.getTotalRows();
      var sortInfo = "";
      if (liveGrid.sortCol) {
         sortInfo = "&data_grid_sort_col=" + liveGrid.sortCol + "&data_grid_sort_dir=" + liveGrid.sortDir;
      }
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
				this.form.reset();  
				this.form.namedItem('name').focus();
				new Backend.SaveConfirmationMessage(this.form.getElementsByClassName('productSaveConf')[0]);
			}

			// product customization content  	
			else
			{
			  
			}
			
		}
	}
}

Backend.Product.specFieldEntrySingleSelect = Class.create();
Backend.Product.specFieldEntrySingleSelect.prototype = 
{
	field: null,
	
	initialize: function(field)
	{
	  	this.field = field;
	  	this.field.onchange = this.handleChange.bindAsEventListener(this);	  	
	},
	
	handleChange: function(e)
	{
		var otherInput = this.field.parentNode.getElementsByTagName('input')[0];
		otherInput.style.display = ('other' == this.field.value) ? '' : 'none';
		
		if ('none' != otherInput.style.display)
		{
			otherInput.focus();  	
		}
	}	
}

Backend.Product.specFieldEntryMultiValue = Class.create();
Backend.Product.specFieldEntryMultiValue.prototype = 
{
	container: null,
	
	isNumeric: false,
	
	initialize: function(container)
	{		
		Event.observe(container.getElementsByClassName('deselect')[0], 'click', this.reset.bindAsEventListener(this));
		
		this.isNumeric = Element.hasClassName(container, 'multiValueNumeric');
		
		this.container = document.getElementsByClassName('other', container)[0];
		var inp = this.container.getElementsByTagName('input')[0];
		this.bindField(inp);  	
	},
	
	bindField: function(field)
	{
		field.onkeyup = this.handleChange.bindAsEventListener(this);  
		field.onblur = this.handleBlur.bindAsEventListener(this); 

		if (this.isNumeric)
		{
			Event.observe(field, 'keyup', this.filterNumeric.bindAsEventListener(this));			  	
		}

		field.value = ''; 
	},
	
	handleChange: function(e)
	{
		var fields = this.container.getElementsByTagName('input');
		var foundEmpty = false;
		for (k = 0; k < fields.length; k++)
		{
		  	if ('' == fields[k].value)
		  	{
			    foundEmpty = true;
			}
		}
		
		if (!foundEmpty)
		{
		  	this.createNewField();
		}
	},
	
	handleBlur: function(e)
	{
		var element = Event.element(e);
		if (!element.value && this.getFieldCount() > 1)
		{
			element.parentNode.parentNode.removeChild(element.parentNode);
		}  
	},

	getFieldCount: function()
	{
		return this.container.getElementsByTagName('input').length;  
	},
		
	createNewField: function()
	{
		var tpl = this.container.getElementsByTagName('p')[0].cloneNode(true);
		this.bindField(tpl.getElementsByTagName('input')[0]);
		this.container.appendChild(tpl);
	},

	reset: function()
	{
		var nodes = this.container.getElementsByTagName('p');
		var ln = nodes.length;
		for (k = 1; k < ln; k++)
		{
		  	nodes[1].parentNode.removeChild(nodes[1]);
		}

		nodes[0].getElementsByTagName('input')[0].value = '';
	},
	
	filterNumeric: function(e)
	{
	  	NumericFilter(Event.element(e));
	}
}

Backend.Product.Editor = Class.create();
Backend.Product.Editor.prototype = 
{    
    __currentId__: null,
    __instances__: {},
    
    initialize: function(id, url)
  	{
	    this.url = url;
	    this.id = id;
        this.__nodes__();
        this.__bind__();
	},
	
	__nodes__: function(parent)
    {
        this.nodes = {};
    },
    
    __bind__: function(args)
    {
        
    },
    
    __init__: function(args)
    {
        Backend.Product.Editor.prototype.setCurrentProductId(this.id);
        this.tabControl = TabControl.prototype.getInstance("productManagerContainer", Backend.Product.Editor.prototype.craftProductUrl, Backend.Product.Editor.prototype.craftProductId);
    },
    
    craftProductUrl: function(url)
    {
        return url.replace(/_categoryID_/, Backend.Category.treeBrowser.getSelectedItemId()).replace(/_id_/, Backend.Product.Editor.prototype.getCurrentProductId());
    },
    
    craftProductId: function(tabId)
    {
        return tabId + '_' +  Backend.Product.Editor.prototype.getCurrentProductId() + 'Content'
    },
    
    getCurrentProductId: function()
    {
        return Backend.Product.Editor.prototype.__currentId__;
    },
    
    setCurrentProductId: function(id)
    {
        Backend.Product.Editor.prototype.__currentId__ = id;
    },
    
    getInstance: function(id, url)
    {
        if(!Backend.Product.Editor.prototype.__instances__[id])
        {
            Backend.Product.Editor.prototype.__instances__[id] = new Backend.Product.Editor(id, url);
        }
        
        Backend.Product.Editor.prototype.__instances__[id].__init__();
        return Backend.Product.Editor.prototype.__instances__[id];
    },
    
    showProductForm: function(args)
    {
        Backend.Product.Editor.prototype.setCurrentProductId(this.id);
        this.hideCategoriesContainer();
    },
    
    hideProductForm: function(args)
    {
        this.showCategoriesContainer();
    },
    
    hideCategoriesContainer: function(args)
    {
        Element.hide($("managerContainer"));
        Element.show($("productManagerContainer"));
    },
    
    showCategoriesContainer: function(args)
    {
        Element.hide($("productManagerContainer"));
        Element.show($("managerContainer"));
    }
}