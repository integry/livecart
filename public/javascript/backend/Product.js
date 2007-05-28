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

		//this.initAddForm(categoryID);
	},

	cancelAddProduct: function(categoryID, container)
	{
		try
        {
            ActiveForm.prototype.destroyTinyMceFields(container);
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
        ActiveForm.prototype.resetTinyMceFields(form);
	},

	initAddForm: function(categoryID)
	{
        tinyMCE.idCounter = 0;
        ActiveForm.prototype.initTinyMceFields($('tabProductsContent_' + categoryID));

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

	updateHeader: function ( activeGrid, offset ) 
	{
		var liveGrid = activeGrid.ricoGrid;
		
		var totalCount = liveGrid.metaData.getTotalRows();
		var from = offset + 1;
		var to = offset + liveGrid.metaData.getPageSize();
		
		if (to > totalCount)
		{
			to = totalCount;		
		}
		  
		var categoryID = activeGrid.tableInstance.id.split('_')[1];		
		var cont = $('productCount_' + categoryID);
		var countElement = document.getElementsByClassName('rangeCount', cont)[0];
		var notFound = document.getElementsByClassName('notFound', cont)[0];
								
		if (totalCount > 0)
		{
			if (!countElement.strTemplate)
			{
				countElement.strTemplate = countElement.innerHTML;	
			}		
			
			var str = countElement.strTemplate;
			str = str.replace(/%from/, from);
			str = str.replace(/%to/, to);
			str = str.replace(/%count/, totalCount);
									
			countElement.innerHTML = str;
			notFound.style.display = 'none';
			countElement.style.display = '';					
		}
		else
		{
			notFound.style.display = '';
			countElement.style.display = 'none';					
		}
    },
    
    openProduct: function(id, e) 
    {
		try 
		{
			window.opener.selectProductPopup.getSelectedProduct(id);	
		}
		catch (exc)
		{   
            Backend.Product.Editor.prototype.setCurrentProductId(id); 
	        $('productIndicator_' + id).style.display = '';
			var tabControl = TabControl.prototype.getInstance('productManagerContainer', Backend.Product.Editor.prototype.craftProductUrl, Backend.Product.Editor.prototype.craftProductId, {
                afterClick: function()
                {
                    if(Backend.RelatedProduct.SelectProductPopup.prototype.popup) {
                        Backend.RelatedProduct.SelectProductPopup.prototype.popup.opener.focus();    
                        Backend.RelatedProduct.SelectProductPopup.prototype.popup.close();
                    }
                }
            }); 
            
            tabControl.activateTab();
            
	        if(Backend.Product.Editor.prototype.hasInstance(id)) 
			{
				Backend.Product.Editor.prototype.getInstance(id);			
			}			
		}
        Event.stop(e);
    }
}

Backend.Product.massActionHandler = Class.create();
Backend.Product.massActionHandler.prototype = 
{
    handlerMenu: null,    
    actionSelector: null,
    valueEntryContainer: null,
    form: null,
        
    grid: null,
    
    initialize: function(handlerMenu, activeGrid)
    {
        this.handlerMenu = handlerMenu;     
        this.actionSelector = handlerMenu.getElementsByTagName('select')[0];
        this.valueEntryContainer = document.getElementsByClassName('bulkValues', handlerMenu)[0];
        this.form = this.actionSelector.form;

        this.actionSelector.onchange = this.actionSelectorChange.bind(this);
        Event.observe(this.actionSelector.form, 'submit', this.submit.bind(this));
            
        this.grid = activeGrid;
    },
    
    actionSelectorChange: function()
    {
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
            Element.show(this.form.elements.namedItem(this.actionSelector.value));
            this.form.elements.namedItem(this.actionSelector.value).focus();
        }    
        else if (document.getElementsByClassName(this.actionSelector.value, this.handlerMenu))
        {
			var el = document.getElementsByClassName(this.actionSelector.value, this.handlerMenu)[0];
			Element.show(el);
		}
    },
    
    submit: function()
    {
        if ('delete' == this.actionSelector.value)
        {
			if (!confirm(this.deleteConfirmMessage))
			{
				return false;
			}
		}
		
		this.form.elements.namedItem('filters').value = this.grid.getFilters().toJSONString();
        this.form.elements.namedItem('selectedIDs').value = this.grid.getSelectedIDs().toJSONString();
        this.form.elements.namedItem('isInverse').value = this.grid.isInverseSelection() ? 1 : 0;
        new LiveCart.AjaxRequest(this.form, document.getElementsByClassName('progressIndicator', this.handlerMenu)[0], this.submitCompleted.bind(this));

        this.grid.resetSelection();   
    },
    
    submitCompleted: function()
    {
        this.grid.reloadGrid();   
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
        try
        {
            this.id = id;
    
            this.__nodes__();
            this.__bind__();
            
            Form.State.backup(this.nodes.form);
            
            var self = this;
        }
        catch(e)
        {
            console.info(e);
        }

	},

	__nodes__: function()
    {
        this.nodes = {};
        this.nodes.parent = $("tabProductBasic_" + this.id + "Content");
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
        $('productIndicator_' + this.id).style.display = 'none';
        this.showProductForm();

        this.tabControl = TabControl.prototype.getInstance("productManagerContainer", false);
        this.addTinyMce();
        this.setTabCounters();
        
		new SectionExpander(this.nodes.parent);
    },
    
    setTabCounters: function()
    {
        try
        {
            var $this = this;
            if(!$this.tabControl.restoreAllCounters($this.id))
            {
                new Ajax.Request(Backend.Product.Editor.prototype.links.countTabsItems + "/" + $this.id, {
                   method: 'get',
                   onSuccess: function(reply) {
                       var counters = eval("(" + reply.responseText + ")")
                       $this.tabControl.setAllCounters(counters, $this.id);
                   } 
                });
            }
        } 
        catch(e)
        {
            console.info(e);
        }
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
    },
    
    removeTinyMce: function()
    {
        ActiveForm.prototype.destroyTinyMceFields(this.nodes.parent);
    },
    
    addTinyMce: function()
    {
		
        ActiveForm.prototype.initTinyMceFields(this.nodes.parent);
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

Backend.Product.GridFormatter = 
{
	getClassName: function(field, value)
	{
		
	},
	
	formatValue: function(field, value, id)
	{
		if ('Product.name' == field && Backend.Product.productsMiscPermision)
		{
			value = '<span>' + 
                        '<span class="progressIndicator" id="productIndicator_' + id + '" style="display: none;"></span>' + 
                    '</span>' + 
                    '<a href="#edit" id="product_' + id + '" onclick="Backend.Product.openProduct(' + id + ', event); return false;">' + 
                        value + 
                    '</a>';	
		}
		
		return value;
	}
}