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
		try
        {
            var textareas = container.getElementsByTagName('textarea');
    		for (k = 0; k < textareas.length; k++)
    		{
    			tinyMCE.execCommand('mceRemoveControl', true, textareas[k].id);
    		}

    		this.formTabCopies[categoryID] = container;

    		container.parentNode.replaceChild(this.productTabCopies[categoryID], container);
        }
        catch(e)
        {
            console.info(e);
        }
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

		new SectionExpander();

		// specField entry logic (multiple value select)
		var containers = document.getElementsByClassName('multiValueSelect', $('tabProductsContent_' + categoryID));
        try
        {
    		for (k = 0; k < containers.length; k++)
    		{
    			new Backend.Product.specFieldEntryMultiValue(containers[k]);
    		}
        }
        catch(e)
        {
            console.info(e);
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
		    skuField._backedUpValue = skuField.value;
			skuField.value = '';
		}
		else
		{
		  	if(skuField._backedUpValue) skuField.value = skuField._backedUpValue;
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
		  	hiMultiplier = 1;
		  	loMultiplier = 0.001;
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
		var form = anchor;
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

Backend.Product.massActionHandler = Class.create();
Backend.Product.massActionHandler.prototype = 
{
    handlerMenu: null,    
    actionSelector: null,
    valueEntryContainer: null,
    
    initialize: function(handlerMenu)
    {
        this.handlerMenu = handlerMenu;     
        this.actionSelector = handlerMenu.getElementsByTagName('select')[0];
        this.valueEntryContainer = document.getElementsByClassName('bulkValues')[0];

        this.actionSelector.onchange = this.actionSelectorChange.bind(this);

        console.log(handlerMenu);
    },
    
    actionSelectorChange: function()
    {
        for (k = 0; k < this.valueEntryContainer.childNodes.length; k++)
        {
            if (this.valueEntryContainer.childNodes[k].style)
            {
                this.valueEntryContainer.childNodes[k].style.display = 'none';                    
            }
        }
        
        if (this.actionSelector.form.elements.namedItem(this.actionSelector.value))
        {
            this.actionSelector.form.elements.namedItem(this.actionSelector.value).style.display = '';            
        }
                
        this.valueEntryContainer.style.display = '';
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
		var response = eval('(' + originalRequest.responseText + ")");

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
                try
                {
                    document.getElementsByClassName('product_sku', this.form)[0].disabled = false;
    				Form.focusFirstElement(this.form);

				    new Backend.SaveConfirmationMessage(this.form.getElementsByClassName('productSaveConf')[0]);
                }
                catch(e)
                {
                    console.info(e);
                }
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
		otherInput.style.display = ('other' == this.field.value) ? 'block' : 'none';

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

        this.fieldStatus = document.getElementsByClassName("fieldStatus", container.parentNode)[0];
		this.container = document.getElementsByClassName('other', container)[0];

		var inp = this.container.getElementsByTagName('input')[0];
		this.bindField(inp);
	},

	bindField: function(field)
	{
		var self = this;
        Event.observe(field, "keyup", function(e) { self.handleChange(e); });
        Event.observe(field, "blur", function(e) { self.handleBlur(e); });

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
		if (element.parentNode && element.parentNode.parentNode &&!element.value && this.getFieldCount() > 1)
		{
			Element.remove(element.parentNode);
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

    initialize: function(id)
  	{
	    this.id = id;

        this.__nodes__();
        this.__bind__();
        
        Form.State.backup(this.nodes.form);
        
        var self = this;

	},

	__nodes__: function()
    {
        this.nodes = {};
        this.nodes.parent = $("productBasic_" + this.id + "Content");
        this.nodes.form = this.nodes.parent.down("form");
		this.nodes.cancel = this.nodes.form.down('a.cancel');
		this.nodes.submit = this.nodes.form.down('input.submit');
    },

    __bind__: function(args)
    {
		var self = this;
		Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancelForm()});
    },

    __init__: function(args)
    {	
		Backend.Product.Editor.prototype.setCurrentProductId(this.id);
        $('productIndicator_' + id).style.display = 'none';
        this.showProductForm();
        this.tabControl = TabControl.prototype.getInstance("productManagerContainer", Backend.Product.Editor.prototype.craftProductUrl, Backend.Product.Editor.prototype.craftProductId);


		var textareas = this.nodes.parent.getElementsByTagName('textarea');
		for (k = 0; k < textareas.length; k++)
		{
			tinyMCE.execCommand('mceAddControl', true, textareas[k].id);
		}
        
		new SectionExpander(this.nodes.parent);
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

    getInstance: function(id, doInit)
    {
		if(!Backend.Product.Editor.prototype.__instances__[id])
        {
            Backend.Product.Editor.prototype.__instances__[id] = new Backend.Product.Editor(id);
        }

        if(doInit !== false) Backend.Product.Editor.prototype.__instances__[id].__init__();
        
        return Backend.Product.Editor.prototype.__instances__[id];
    },

    hasInstance: function(id)
    {
        return this.__instances__[id] ? true : false;
    },

    showProductForm: function(args)
    {
		this.hideCategoriesContainer();
    },

    cancelForm: function()
    {      
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.restore(this.nodes.form);
    },

    submitForm: function()
    {
		var self = this;
		new Ajax.Request(this.nodes.form.action + "/" + this.id,
		{
           method: this.nodes.form.method,
           parameters: Form.serialize(self.nodes.form),
           onSuccess: function(responseJSON) {
				ActiveForm.prototype.resetErrorMessages(self.nodes.form);
				var responseObject = eval("(" + responseJSON.responseText + ")");
				self.afterSubmitForm(responseObject);
		   }
		});
    },
	
	afterSubmitForm: function(response)
	{
		if(!response.errors || 0 == response.errors.length)
		{
			new Backend.SaveConfirmationMessage(this.nodes.form.down('.pricesSaveConf'));
			Form.State.backup(this.nodes.form);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
	},

    hideCategoriesContainer: function(args)
    {
        Element.hide($("managerContainer"));
        Element.show($("productManagerContainer"));
    },

    showCategoriesContainer: function(args)
    {       
        if($("productManagerContainer")) Element.hide($("productManagerContainer"));
        if($("managerContainer")) Element.show($("managerContainer"));
    }
}

Backend.Product.Prices = Class.create();
Backend.Product.Prices.prototype =
{
    __instances__: {},

    initialize: function(parent, product)
    {
        this.product = product;

        this.__nodes__($(parent));
        this.__bind__();

        Form.State.backup(this.nodes.form);
    },

    getInstance: function(parent, product)
    {
        var parentNode = $(parent);
        if(!Backend.Product.Prices.prototype.__instances__[parentNode.id])
        {
            Backend.Product.Prices.prototype.__instances__[parentNode.id] = new Backend.Product.Prices(parentNode.id, product);
        }

        Backend.Product.Prices.prototype.__instances__[parentNode.id].__init__();
        return Backend.Product.Prices.prototype.__instances__[parentNode.id];
    },

	__nodes__: function(parent)
    {
        this.nodes = {};
        this.nodes.parent = parent;
        this.nodes.form = parent;

        this.nodes.submit = this.nodes.parent.down("input.submit");
        this.nodes.cancel = this.nodes.parent.down("a.cancel");
    },

    __bind__: function(args)
    {
        var self = this;
		Event.observe(this.nodes.cancel, "click", function(e) {
			Event.stop(e);
			self.resetForm();
		});
    },

    __init__: function(args)
    {
    },

    submitForm: function()
    {
        var self = this;
        new Ajax.Request(this.nodes.form.action + "/" + this.product.ID, {
           method: this.nodes.form.method,
           parameters: Form.serialize(self.nodes.form),
           onSuccess: function(responseJSON) {
				ActiveForm.prototype.resetErrorMessages(self.nodes.form);
				var responseObject = eval("(" + responseJSON.responseText + ")");
				self.afterSubmitForm(responseObject);
		   }
        });
    },

    resetForm: function(response)
    {
		ActiveForm.prototype.resetErrorMessages(this.nodes.form);
		Form.State.restore(this.nodes.form);
    },

    afterSubmitForm: function(response)
    {
		if('success' == response.status)
		{
			new Backend.SaveConfirmationMessage(this.nodes.form.down('.pricesSaveConf'));
			var self = this;
			$H(response.prices).each(function(price) {
				self.nodes.form.elements.namedItem(price.key).value = price.value;
			});

			Form.State.backup(this.nodes.form);
		}
		else
		{
			ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors)
		}
    }
}

