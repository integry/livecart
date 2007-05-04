Backend.DeliveryZone = Class.create();
Backend.DeliveryZone.prototype = 
{
  	Links: {},
    Messages: {},
    
    treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(zones)
	{
        
		Backend.DeliveryZone.prototype.treeBrowser = new dhtmlXTreeObject("deliveryZoneBrowser","","", false);
		Backend.DeliveryZone.prototype.treeBrowser.setOnClickHandler(this.activateZone);
		
		Backend.DeliveryZone.prototype.treeBrowser.def_img_x = 'auto';
		Backend.DeliveryZone.prototype.treeBrowser.def_img_y = 'auto';
				
		Backend.DeliveryZone.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		Backend.DeliveryZone.prototype.treeBrowser.setOnClickHandler(this.activateZone.bind(this));
        
        var self = this;
        Event.observe($("newZoneInputButton"), 'click', function(e){ Event.stop(e); self.addNewZone(); })
        
		this.tabControl = TabControl.prototype.getInstance('deliveryZoneManagerContainer', this.craftTabUrl, this.craftContainerId, {}); 

		Backend.DeliveryZone.prototype.treeBrowser.showFeedback = 
			function(itemId) 
			{
				if (!this.iconUrls)
				{
					this.iconUrls = new Object();	
				}
				
				this.iconUrls[itemId] = this.getItemImage(itemId, 0, 0);
				this.setItemImage(itemId, '../../../image/indicator.gif');
			}
		
		Backend.DeliveryZone.prototype.treeBrowser.hideFeedback = 
			function()
			{
				for (var itemId in this.iconUrls)
				{
					this.setItemImage(itemId, this.iconUrls[itemId]);	
				}				
			}
		
    	this.insertTreeBranch(zones, 0);    
        
        this.bindEvents();
	},

    bindEvents: function()
    {
        var self = this;
        Event.observe($("deliveryZone_delete"), 'click', function(e) 
        {
            Event.stop(e);
            self.deleteZone();
        });
    },
    
    deleteZone: function()
    {
        var $this = this;
        
        if(confirm(Backend.DeliveryZone.prototype.Messages.confirmZoneDelete)) 
        {
		    new Ajax.Request(
    			Backend.DeliveryZone.prototype.Links.remove + '/' + Backend.DeliveryZone.prototype.activeZone,
    			{
				    onComplete: function(response) { 
                        response = eval("(" + response.responseText + ")");
                        if('success' == response.status)
                        {
                            Backend.DeliveryZone.prototype.treeBrowser.deleteItem(Backend.DeliveryZone.prototype.activeZone, true);
                            var firstId = false;
                            if(firstId = parseInt(Backend.DeliveryZone.prototype.treeBrowser._globalIdStorage[1]))
                            {
                                Backend.DeliveryZone.prototype.treeBrowser.selectItem(firstId, true);
                            }
                        }
                    }
			    }
            );
        }
    },
    
	addNewZone: function()
	{
        var self = this;
        
		new Ajax.Request(
			Backend.DeliveryZone.prototype.Links.save,
			{
				method: 'post',
				parameters: 'name=' + $("newZoneInput").value,
				onComplete: function(response) { self.afterNewZoneAdded(eval("(" + response.responseText + ")")); }
			});
	},

	afterNewZoneAdded: function(response)
	{
        Backend.DeliveryZone.prototype.treeBrowser.insertNewItem(0, response.ID, $("newZoneInput").value, 0, 0, 0, 0, 'SELECT');
        $("newZoneInput").value = '';
        this.activateZone(response.ID);
	},
    
    craftTabUrl: function(url)
    {
        return url.replace(/_id_/, Backend.DeliveryZone.prototype.treeBrowser.getSelectedItemId());
    },

    craftContainerId: function(tabId)
    {
        return tabId + '_' +  Backend.DeliveryZone.prototype.treeBrowser.getSelectedItemId() + 'Content';
    },
	
	insertTreeBranch: function(treeBranch, rootId)
	{
		var self = this;
        $A(treeBranch).each(function(node)
		{
            Backend.DeliveryZone.prototype.treeBrowser.insertNewItem(rootId, node.ID, node.name, null, 0, 0, 0, '', 1);
            self.treeBrowser.showItemSign(node.ID, 0);
            var zone = document.getElementsByClassName("standartTreeRow", $("deliveryZoneBrowser")).last();
            zone.id = 'zone_' + node.ID;
            zone.onclick = function()
            {
                Backend.DeliveryZone.prototype.treeBrowser.selectItem(node.ID, true);
            }
		});
	},
	
	activateZone: function(id)
	{
        if(Backend.DeliveryZone.prototype.activeZone && Backend.DeliveryZone.prototype.activeZone != id)
        {
            Backend.DeliveryZone.prototype.activeZone = id;
    		Backend.DeliveryZone.prototype.treeBrowser.showFeedback(id);
            
            Backend.ajaxNav.add('zone_' + id);
            this.tabControl.activateTab($('tabDeliveryZoneCountry'), function() { 
                Backend.DeliveryZone.prototype.treeBrowser.hideFeedback(id);
            });
        }
        
        Backend.DeliveryZone.prototype.activeZone = id;
	},
	
	displayCategory: function(response)
	{
		Backend.DeliveryZone.prototype.treeBrowser.hideFeedback();	
		var cancel = document.getElementsByClassName('cancel', $('deliveryZoneContent'))[0];
		Event.observe(cancel, 'click', this.resetForm.bindAsEventListener(this));
	},
	
	resetForm: function(e)
	{
		var el = Event.element(e);
		while (el.tagName != 'FORM')
		{
			el = el.parentNode;
		}
		
		el.reset();		
	},
	
	save: function(form)
	{
		var indicator = document.getElementsByClassName('progressIndicator', form)[0];
		new LiveCart.AjaxRequest(form, indicator, this.displaySaveConfirmation.bind(this));	
	},
	
	displaySaveConfirmation: function()
	{
		new Backend.SaveConfirmationMessage(document.getElementsByClassName('yellowMessage')[0]);			
	} 
}

Backend.DeliveryZone.CountriesAndStates = Class.create();
Backend.DeliveryZone.CountriesAndStates.prototype = 
{
    Links: {},
    Messages: {},
    
    CallbacksCity: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete))
            {
                return Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteCityMask + "/" + this.getRecordId(li);
            }
        },
        'afterDelete': function(li, response)
        {
            response = eval('(' + response + ')')
            
            if('success' == response.status) {
                this.remove(li);     
            }
        },
        'beforeEdit': function(li) 
        {
            Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li);
        },
        'afterEdit': function(li, response) {}
    },
    CallbacksZip: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete))
            {
                return Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteZipMask + "/" + this.getRecordId(li);  
            }              
        },
        'afterDelete': function(li, response)
        {
            response = eval('(' + response + ')')
            
            if('success' == response.status) {
                this.remove(li);     
            }
        },
        'beforeEdit': function(li) 
        {
            Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li);                
        },
        'afterEdit': function(li, response) {}
    },
    CallbacksAddress: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.CountriesAndStates.prototype.Messages.confirmAddressDelete))
            {
                return Backend.DeliveryZone.CountriesAndStates.prototype.Links.deleteAddressMask + "/" + this.getRecordId(li);
            }
        },
        'afterDelete': function(li, response)
        {
            response = eval('(' + response + ')');
            
            if('success' == response.status) {
                this.remove(li);     
            }
        },
        'beforeEdit': function(li) 
        {
            Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li);
        },
        'afterEdit': function(li, response) {}
    },
    
    
    prefix: 'countriesAndStates_',
    instances: {},
    
    initialize: function(root, zoneID) 
    {
        this.zoneID = zoneID;

        this.findNodes(root);
        this.bindEvents();
        
//        this.sortSelect(this.nodes.inactiveCountries);
//        this.sortSelect(this.nodes.activeCountries);
//        this.sortSelect(this.nodes.inactiveStates);
//        this.sortSelect(this.nodes.activeStates);
        
        new SectionExpander(this.nodes.root);
    },
    
    getInstance: function(root, zoneID) 
    {
        if(!Backend.DeliveryZone.CountriesAndStates.prototype.instances[$(root).id])
        {
            Backend.DeliveryZone.CountriesAndStates.prototype.instances[$(root).id] = new Backend.DeliveryZone.CountriesAndStates(root, zoneID);
        }
        
        return Backend.DeliveryZone.CountriesAndStates.prototype.instances[$(root).id];
    },
    
    findNodes: function(root)
    {
        this.nodes = {};
        this.nodes.root = $(root);
        
        this.nodes.form = this.nodes.root.tagName == 'FORM' ? this.nodes.root : this.nodes.root.down('form');

        this.nodes.name = this.nodes.form.down('.' + this.prefix + 'name');

        this.nodes.addCountryButton     = this.nodes.root.down('.' + this.prefix + 'addCountry');
        this.nodes.removeCountryButton  = this.nodes.root.down('.' + this.prefix + 'removeCountry');
        this.nodes.addStateButton       = this.nodes.root.down('.' + this.prefix + 'addState');
        this.nodes.removeStateButton    = this.nodes.root.down('.' + this.prefix + 'removeState');
        
        this.nodes.inactiveCountries    = this.nodes.root.down('.' + this.prefix + 'inactiveCountries');
        this.nodes.activeCountries      = this.nodes.root.down('.' + this.prefix + 'activeCountries');
        this.nodes.inactiveStates       = this.nodes.root.down('.' + this.prefix + 'inactiveStates');
        this.nodes.activeStates         = this.nodes.root.down('.' + this.prefix + 'activeStates');
        
        this.nodes.cityMasks            = this.nodes.root.down('.' + this.prefix + 'cityMasks');
        this.nodes.cityMasksList        = this.nodes.cityMasks.down('.' + this.prefix + 'cityMasksList');
        this.nodes.cityMaskNew          = this.nodes.cityMasks.down('.' + this.prefix + 'newMask');
        this.nodes.cityMaskNewButton    = this.nodes.cityMasks.down('.' + this.prefix + 'newMaskButton');
        
        this.nodes.zipMasks            = this.nodes.root.down('.' + this.prefix + 'zipMasks');
        this.nodes.zipMasksList        = this.nodes.zipMasks.down('.' + this.prefix + 'zipMasksList');
        this.nodes.zipMaskNew          = this.nodes.zipMasks.down('.' + this.prefix + 'newMask');
        this.nodes.zipMaskNewButton    = this.nodes.zipMasks.down('.' + this.prefix + 'newMaskButton');
        
        this.nodes.addressMasks            = this.nodes.root.down('.' + this.prefix + 'addressMasks');
        this.nodes.addressMasksList        = this.nodes.addressMasks.down('.' + this.prefix + 'addressMasksList');
        this.nodes.addressMaskNew          = this.nodes.addressMasks.down('.' + this.prefix + 'newMask');
        this.nodes.addressMaskNewButton    = this.nodes.addressMasks.down('.' + this.prefix + 'newMaskButton');
        
        this.nodes.zonesAndUnions       = this.nodes.root.down('.' + this.prefix + 'regionsAndUnions').getElementsByTagName('a');
        
        this.nodes.observedElements = document.getElementsByClassName("observed", this.nodes.form);
    },
    
    toggleMask: function(li)
    {
        var self = Backend.DeliveryZone.CountriesAndStates.prototype.getInstance(li.up('form'));
        var input = li.down('input');
        var title = li.down('.maskTitle');
        if(li.down('input').style.display == 'inline')
        {      
            var list = null;     
            if(list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'cityMasksList')) 
            {
                var url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask;
            }
            else if(list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'zipMasksList'))
            {
                var url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask;
            }
            else if(list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'addressMasksList'))
            {
                var url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask;
            }
            
            var activeList = ActiveList.prototype.getInstance(list);
            new Ajax.Request(url + "/" + activeList.getRecordId(li),
            {
    			method: 'post',
    			parameters: 'mask=' + li.down('input').value,
                onSuccess: function(response) {
                    var response = eval('(' + response.responseText + ')');
                    
                    if(response.status == 'success') 
                    {
                        input.style.display = 'none';
                        title.style.display = 'inline';
                        title.update(input.value);
                        console.info(self);
                        ActiveForm.prototype.resetErrorMessage(input);
                    }
                    else
                    {
                        ActiveForm.prototype.setErrorMessage(input, response.errors.mask, true);
                    }
                }
            });
        }
        else
        {
            input.style.display = 'inline';
            title.style.display = 'none';
        }
    },
    
    bindEvents: function()
    {
        var self = this;
        
        Event.observe(this.nodes.addCountryButton, 'click', function(e) { Event.stop(e); self.addCountry(); });
        Event.observe(this.nodes.removeCountryButton, 'click', function(e) { Event.stop(e); self.removeCountry(); });
        Event.observe(this.nodes.addStateButton, 'click', function(e) { Event.stop(e); self.addState(); });
        Event.observe(this.nodes.form, 'submit', function(e) { Event.stop(e); self.save(); });
        Event.observe(this.nodes.removeStateButton, 'click', function(e) { Event.stop(e); self.removeState(); });
        Event.observe(this.nodes.cityMaskNewButton, 'click', function(e) { Event.stop(e); self.addNewCityMask(self.nodes.cityMaskNew); });
        Event.observe(this.nodes.zipMaskNewButton, 'click', function(e) { Event.stop(e); self.addNewZipMask(self.nodes.zipMaskNew); });
        Event.observe(this.nodes.addressMaskNewButton, 'click', function(e) { Event.stop(e); self.addNewAddressMask(self.nodes.addressMaskNew); });

        $A(this.nodes.zonesAndUnions).each(function(zoneOrUnion) {
            Event.observe(zoneOrUnion, 'click', function(e) { Event.stop(e); self.selectZoneOrUnion(this.hash.substring(0,1) == '#' ? this.hash.substring(1) : this.hash); });
        });
        
        $A(this.nodes.observedElements).each(function(element) {
            Event.observe(element, 'blur', function(e) { self.save() });
        });
        
        Event.observe(this.nodes.addressMaskNew, 'keyup', function(e) 
        {
           if(KeyboardEvent.prototype.init(e).isEnter()) 
           {
               if(!e.target) e.target = e.srcElement;
               self.addNewAddressMask(e.target);
           }
        });
        
        Event.observe(this.nodes.zipMaskNew, 'keyup', function(e) 
        {
           if(KeyboardEvent.prototype.init(e).isEnter()) 
           {
               if(!e.target) e.target = e.srcElement;
               self.addNewZipMask(e.target);
           }
        });
        
        Event.observe(this.nodes.cityMaskNew, 'keyup', function(e) 
        {
           if(KeyboardEvent.prototype.init(e).isEnter()) 
           {
               if(!e.target) e.target = e.srcElement;
               self.addNewCityMask(e.target);
           }
        });
        
        document.getElementsByClassName(this.prefix + 'mask').each(function(mask) {
           self.bindMask(mask);
        });
    },
    
    bindMask: function(mask) 
    {
       var self = this;
       Event.observe(mask, 'keyup', function(e) 
       { 
           if(KeyboardEvent.prototype.init(e).isEnter()) {
               if(!e.target) e.target = e.srcElement;
               self.toggleMask(e.target.up('li'));
           }
       });
    },
    
    save: function() {
        this.saving = true;
        
        Backend.DeliveryZone.prototype.treeBrowser.setItemText(Backend.DeliveryZone.prototype.activeZone, this.nodes.name.value)
        
        var self = this;
        new Ajax.Request(Backend.DeliveryZone.prototype.Links.save + "/" + this.zoneID,
        {
           method: 'post',
           parameters: Form.serialize(self.nodes.form)
        });
    
        this.saving = false;
    },
    
    selectZoneOrUnion: function(regionName) 
    {
        var self = this;
        includedCountriesCodes = Backend.DeliveryZone.countryGroups[regionName];
        
        $A(this.nodes.inactiveCountries.options).each(function(option) 
        {
            option.selected = includedCountriesCodes.indexOf(option.value) !== -1 ? true : false;
        });
    },
    
    addNewCityMask: function(mask)
    {
        var self = this;
        new Ajax.Request(Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask + "/" + '?zoneID=' + this.zoneID,
        {
			method: 'post',
			parameters: 'mask=' + mask.value,
            onSuccess: function(response) 
            {
                response = eval('(' + response.responseText + ')');
                if('success' == response.status)
                {
                    var activeList = ActiveList.prototype.getInstance(self.nodes.cityMasksList);
                    var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />');

                    mask.value = '';
                    self.bindMask(li.down('input'));
                    ActiveForm.prototype.resetErrorMessage(mask);
                }
                else
                {
                    ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
                }
            }
        });
    },
    
    addNewZipMask: function(mask)
    {
        var self = this;
        new Ajax.Request(Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask + "/" + '?zoneID=' + this.zoneID,
        {
			method: 'post',
			parameters: 'mask=' + mask.value,
            onSuccess: function(response) 
            {
                response = eval('(' + response.responseText + ')');
                if('success' == response.status)
                {
                    var activeList = ActiveList.prototype.getInstance(self.nodes.zipMasksList);
                    var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />');

                    mask.value = '';
                    self.bindMask(li.down('input'));
                    ActiveForm.prototype.resetErrorMessage(mask);
                }
                else
                {
                    ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
                }
            }
        });
    },
    
    addNewAddressMask: function(mask)
    {
        var self = this;
        new Ajax.Request(Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask + "/" + '?zoneID=' + this.zoneID,
        {
			method: 'post',
			parameters: 'mask=' + mask.value,
            onSuccess: function(response) 
            {
                response = eval('(' + response.responseText + ')');
                if('success' == response.status)
                {
                    var activeList = ActiveList.prototype.getInstance(self.nodes.addressMasksList);
                    var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />');

                    mask.value = '';
                    self.bindMask(li.down('input'));
                    ActiveForm.prototype.resetErrorMessage(mask);
                }
                else
                {
                    ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
                }
            }
        });
    },
    
    sortSelect: function(select)
    {
        var options = [];
        $A(select.options).each(function(option) { options[options.length] = option; });
        select.options.length = 0;
        optionso = options.sort(function(a, b){ return (b.text < a.text) - (a.text < b.text); });
        $A(options).each(function(option) { select.options[select.options.length] = option; });
    },
    
    moveSelectedOptions: function(fromSelect, toSelect)
    {
        var self = this;
        fromSelectOptions = fromSelect.options;
        toSelectOptions = toSelect.options;
        
        // Deselect options
        $A(toSelectOptions).each(function(option) { option.selected = false; });
        
        // move options to another list
        $A(fromSelectOptions).each(function(option) 
        {
            if(option.selected) 
            {
                toSelectOptions[toSelectOptions.length] = option;
            }
        });
        
        this.sortSelect(toSelect);
    },
    
    sendSelectsData: function(activeSelect, inactiveSelect, url)
    {
        var active = "";
        $A(activeSelect.options).each(function(option) {
            active += "active[]=" + option.value + "&";
        });
        active.substr(0, active.length - 1);
        
        var inactive = "";
        $A(inactiveSelect.options).each(function(option) {
            inactive += "inactive[]=" + option.value + "&";
        });
        inactive.substr(0, active.length - 1)
        console.info(active + inactive);
        var self = this;
        new Ajax.Request(url + "/" + this.zoneID, {
            method: 'post',
            parameters: active + inactive
        });
    },
    
    addCountry: function()
    {
        this.moveSelectedOptions(this.nodes.inactiveCountries, this.nodes.activeCountries);
        this.sendSelectsData(this.nodes.activeCountries, this.nodes.inactiveCountries, Backend.DeliveryZone.prototype.Links.saveCountries);
    },
    
    removeCountry: function()
    {
        this.moveSelectedOptions(this.nodes.activeCountries, this.nodes.inactiveCountries);
        this.sendSelectsData(this.nodes.activeCountries, this.nodes.inactiveCountries, Backend.DeliveryZone.prototype.Links.saveCountries);
    },
    
    addState: function()
    {
        this.moveSelectedOptions(this.nodes.inactiveStates, this.nodes.activeStates);
        this.sendSelectsData(this.nodes.activeStates, this.nodes.inactiveStates, Backend.DeliveryZone.prototype.Links.saveStates);
    },
    
    removeState: function()
    {
        this.moveSelectedOptions(this.nodes.activeStates, this.nodes.inactiveStates);
        this.sendSelectsData(this.nodes.activeStates, this.nodes.inactiveStates, Backend.DeliveryZone.prototype.Links.saveStates);
    },
}








Backend.DeliveryZone.ShippingService = Class.create();
Backend.DeliveryZone.ShippingService.prototype = 
{
    Links: {},
    Messages: {},
 
    Callbacks: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.ShippingService.prototype.Messages.confirmDelete))
            {
                return Backend.DeliveryZone.ShippingService.prototype.Links.remove + "/" + this.getRecordId(li);
            }
        },
        'afterDelete': function(li, response)
        {
            response = eval('(' + response + ')')
            
            if('success' == response.status) {
                this.remove(li);     
            }
        },
        
        beforeEdit:     function(li)
        {
            if(this.isContainerEmpty(li, 'edit')) 
            {
                return Backend.DeliveryZone.ShippingService.prototype.Links.edit + '/' + this.getRecordId(li)
            }
            else 
            {
                this.toggleContainer(li, 'edit');
            }
        },
       
        afterEdit:      function(li, response)
        {
            new Insertion.Bottom(li, response);
            this.toggleContainer(li, 'edit');
        },
         
        beforeSort:     function(li, order)
        {
            return Backend.DeliveryZone.ShippingService.prototype.Links.sortServices + '?target=' + "shippingService_servicesList" + "&" + order
        },
    
        afterSort:      function(li, response) { }
    },
     
    instances: {},
    
    prefix: 'shippingService_',
    
    initialize: function(root, service)
    {
        try
        {
            this.service = service;
            this.findUsedNodes(root);
            this.bindEvents();
            this.rangeTypeChanged();
            this.servicesActiveList = ActiveList.prototype.getInstance(this.nodes.servicesList);
            new SectionExpander(this.nodes.root);
        }
        catch(e)
        {
            console.info(e);
        }
    },
        
    getInstance: function(rootNode, service)
    {
        var rootId = $(rootNode).id;
        if(!Backend.DeliveryZone.ShippingService.prototype.instances[rootId])
        {
            Backend.DeliveryZone.ShippingService.prototype.instances[rootId] = new Backend.DeliveryZone.ShippingService(rootId, service);
        }
        
        return Backend.DeliveryZone.ShippingService.prototype.instances[rootId];
    },
    
    findUsedNodes: function(root)
    {
        this.nodes = {};
        
        this.nodes.root = $(root);
        this.nodes.form = this.nodes.root;

        this.nodes.controls = this.nodes.root.down('.' + this.prefix + 'controls');
        this.nodes.save = this.nodes.controls.down('.' + this.prefix + 'save');
        this.nodes.cancel = this.nodes.controls.down('.' + this.prefix + 'cancel');
        
        this.nodes.rangeTypes = document.getElementsByClassName(this.prefix + 'rangeType', this.nodes.root);
        
        this.nodes.servicesList = $$('.' + this.prefix + 'servicesList_' + this.service.DeliveryZone.ID)[0];

        this.nodes.menu = $(this.prefix + "menu_" + this.service.DeliveryZone.ID);
        this.nodes.menuCancelLink = $(this.prefix + "new_" + this.service.DeliveryZone.ID + "_cancel");
        this.nodes.menuShowLink = $(this.prefix + "new_" + this.service.DeliveryZone.ID + "_show");
        this.nodes.menuForm = $(this.prefix + "new_service_" + this.service.DeliveryZone.ID + "_form");
        
        this.nodes.name = this.nodes.root.down('.' + this.prefix + 'name');
    },
    
    bindEvents: function()
    {
       var self = this;
       

       Event.observe(this.nodes.save, 'click', function(e) { Event.stop(e); self.save(); });
       Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancel(); });
       if(!this.service.ID)
       {
           Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); self.cancel(); });
       }
       
       $A(this.nodes.rangeTypes).each(function(radio)
       {
           Event.observe(radio, 'click', function(e) { self.rangeTypeChanged(); });
       });
    },
    
    rangeTypeChanged: function()
    {
        var radio = null;
        $A(this.nodes.rangeTypes).each(function(r){ if(r.checked) radio = r; });
        
        if(radio.value == 0) 
        {
            document.getElementsByClassName(this.prefix + "subtotalRange").each(function(fieldset) { fieldset.style.display = 'none'; });
            document.getElementsByClassName(this.prefix + "subtotalPercentCharge").each(function(fieldset) { fieldset.up('fieldset').style.display = 'none'; });
            document.getElementsByClassName(this.prefix + "perKgCharge").each(function(fieldset) { fieldset.up('fieldset').style.display = 'block'; });
            document.getElementsByClassName(this.prefix + "weightRange").each(function(fieldset) { fieldset.style.display = 'block'; });
            console.info('weight based');
        }
        else
        {
            document.getElementsByClassName(this.prefix + "subtotalRange").each(function(fieldset) { fieldset.style.display = 'block'; });
            document.getElementsByClassName(this.prefix + "subtotalPercentCharge").each(function(fieldset) { fieldset.up('fieldset').style.display = 'block'; });
            document.getElementsByClassName(this.prefix + "perKgCharge").each(function(fieldset) { fieldset.up('fieldset').style.display = 'none'; });
            document.getElementsByClassName(this.prefix + "weightRange").each(function(fieldset) { fieldset.style.display = 'none'; });
            console.info('subtotal based');
        }
    },
    
    showNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuCancelLink]);
        ActiveForm.prototype.showNewItemForm(this.nodes.menuShowLink, this.nodes.menuForm); 
    },
    
    hideNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuShowLink]);
        ActiveForm.prototype.hideNewItemForm(this.nodes.menuCancelLink, this.nodes.menuForm); 
    },
    
    save: function()
    {
        var self = this;
        
        new Ajax.Request(Backend.DeliveryZone.ShippingService.prototype.Links.save, {
            method: 'post',
            parameters: Form.serialize(this.nodes.form),
            onSuccess: function(response) { 
                var response = eval("(" + response.responseText + ")");
                self.afterSave(response);     
            }
        });

        console.info('save');
    },
    
    afterSave: function(response)
    {
        if(response.service.status == 'success')
        {
            if(!this.service.ID)
            {
                var li = this.servicesActiveList.addRecord(response.service.ID, '<span class="' + this.prefix + 'servicesList_title">' + this.nodes.name.value + '</span>');
            }
        }
    },
    
    cancel: function()
    {
        console.info('cancel');
        
        if(!this.service.ID)
        {
            this.hideNewForm();
        }
    }
}








Backend.DeliveryZone.ShippingRate = Class.create();
Backend.DeliveryZone.ShippingRate.prototype = 
{
    Links: {},
    Messages: {},
 
    Callbacks: {
        'beforeDelete': function(li) 
        {
            Backend.DeliveryZone.ShippingRate.prototype.getInstance(li).remove();
        },
        'beforeEdit': function(li) 
        {
            console.info('before edit');
        },
        'afterEdit': function(li, response) 
        {
            console.info('after edit');
        },

        'beforeSort':     function(li, order)
        {
            return Backend.DeliveryZone.ShippingRate.prototype.Links.sortRates + '?target=' + "shippingService_ratesList&" + order
        },
    
        'afterSort':      function(li, response) { }
    },
    
    instances: {},
    
    prefix: 'shippingService_',
    
    newRateLastId: 0,
    
    initialize: function(root, rate)
    {
        this.rate = rate;
        
        this.findUsedNodes(root);
        this.bindEvents();
        
        this.ratesActiveList = ActiveList.prototype.getInstance(this.nodes.ratesActiveList, Backend.DeliveryZone.ShippingRate.prototype.RatesCallbacks);
        
        if(this.rate.ID)
        {
            this.nodes.controls.hide();
        }
        else
        {
            this.nodes.controls.show();
        }
        
        if(this.rate.ID)
        {
            this.nodes.weightRangeStart.name = 'rate_' + this.rate.ID + '_weightRangeStart';
            this.nodes.weightRangeEnd.name = 'rate_' + this.rate.ID + '_weightRangeEnd';
            this.nodes.subtotalRangeStart.name = 'rate_' + this.rate.ID + '_subtotalRangeStart';
            this.nodes.subtotalRangeEnd.name = 'rate_' + this.rate.ID + '_subtotalRangeEnd';
            this.nodes.flatCharge.name = 'rate_' + this.rate.ID + '_flatCharge';
            this.nodes.perItemCharge.name = 'rate_' + this.rate.ID + '_perItemCharge';
            this.nodes.subtotalPercentCharge.name = 'rate_' + this.rate.ID + '_subtotalPercentCharge';
            this.nodes.perKgCharge.name = 'rate_' + this.rate.ID + '_perKgCharge';
        }
        
        new SectionExpander(this.nodes.root);
    },
        
    getInstance: function(rootNode, rate)
    {
        var rootId = $(rootNode).id;
        if(!Backend.DeliveryZone.ShippingRate.prototype.instances[rootId])
        {
            Backend.DeliveryZone.ShippingRate.prototype.instances[rootId] = new Backend.DeliveryZone.ShippingRate(rootId, rate);
        }
        
        return Backend.DeliveryZone.ShippingRate.prototype.instances[rootId];
    },
    
    findUsedNodes: function(root)
    {
        this.nodes = {};
        
        this.nodes.root = $(root);

        this.nodes.controls = this.nodes.root.down('.' + this.prefix +     'rate_controls');
        this.nodes.save     = this.nodes.controls.down('.' + this.prefix + 'rate_save');
        this.nodes.cancel   = this.nodes.controls.down('.' + this.prefix + 'rate_cancel');
        
        this.nodes.menuCancelLink   = $(this.prefix + "new_rate_" + this.rate.ShippingService.DeliveryZone.ID + '_' + this.rate.ShippingService.ID + "_cancel");
        this.nodes.menuShowLink = $(this.prefix + "new_rate_" + this.rate.ShippingService.DeliveryZone.ID + '_' + this.rate.ShippingService.ID + "_show");
        this.nodes.menu =$(this.prefix + "rate_menu_" + this.rate.ShippingService.DeliveryZone.ID + '_' + this.rate.ShippingService.ID);
        this.nodes.menuForm = $(this.prefix + "new_rate_" + this.rate.ShippingService.DeliveryZone.ID + '_' + this.rate.ShippingService.ID + "_form");
        
        this.nodes.ratesActiveList = $(this.prefix + 'ratesList_' + this.rate.ShippingService.DeliveryZone.ID + '_' + this.rate.ShippingService.ID);
        
        this.nodes.weightRangeStart = this.nodes.root.down('.' + this.prefix + 'weightRangeStart');
        this.nodes.weightRangeEnd = this.nodes.root.down('.' + this.prefix + 'weightRangeEnd');
        this.nodes.subtotalRangeStart = this.nodes.root.down('.' + this.prefix + 'subtotalRangeStart');
        this.nodes.subtotalRangeEnd = this.nodes.root.down('.' + this.prefix + 'subtotalRangeEnd');
        this.nodes.flatCharge = this.nodes.root.down('.' + this.prefix + 'flatCharge');
        this.nodes.perItemCharge = this.nodes.root.down('.' + this.prefix + 'perItemCharge');
        this.nodes.subtotalPercentCharge = this.nodes.root.down('.' + this.prefix + 'subtotalPercentCharge');
        this.nodes.perKgCharge = this.nodes.root.down('.' + this.prefix + 'perKgCharge');
    },
    
    bindEvents: function()
    {
       var self = this;
       if(!this.rate.ID)
       {
           Event.observe(this.nodes.save, 'click', function(e) { Event.stop(e); self.save(e); });
           Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); self.cancel();});
           Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); self.cancel(); });
       } 
    },
    
    showNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuCancelLink ]);
        ActiveForm.prototype.showNewItemForm(this.nodes.menuShowLink, this.nodes.menuForm); 
    },
    
    hideNewForm: function()
    {
        ActiveForm.prototype.hideMenuItems(this.nodes.menu, [this.nodes.menuShowLink]);
        ActiveForm.prototype.hideNewItemForm(this.nodes.menuCancelLink, this.nodes.menuForm); 
    },
    
    save: function(event)
    {
        if(!this.rate.ID)
        {
            Backend.DeliveryZone.ShippingRate.prototype.newRateLastId++;
            var newId = Backend.DeliveryZone.ShippingRate.prototype.newRateLastId;
            
            var li = this.ratesActiveList.addRecord(newId, this.nodes.root);
            
            var idStart = this.prefix + this.rate.ShippingService.DeliveryZone.ID + '_' + this.rate.ShippingService.ID + "_";
            var idStartRegexp = new RegExp(idStart)
                document.getElementsByClassName(this.prefix + 'rateFloatValue', li).each(function(input) {
                input.id = input.id.replace(idStartRegexp, idStart + 'new' + newId);
                input.up().down('label')['for'] = input.id;
            });
            
            var rate = {
                'weightRangeStart': this.nodes.weightRangeStart.value,
                'weightRangeEnd': this.nodes.weightRangeEnd.value,
                'subtotalRangeStart': this.nodes.subtotalRangeStart.value,
                'subtotalRangeEnd': this.nodes.subtotalRangeEnd.value,
                'flatCharge': this.nodes.flatCharge.value,
                'perItemCharge': this.nodes.perItemCharge.value,
                'subtotalPercentCharge': this.nodes.subtotalPercentCharge.value,
                'perKgCharge': this.nodes.perKgCharge.value,
                'ShippingService': this.rate.ShippingService,
                'ID': 'new' + Backend.DeliveryZone.ShippingRate.prototype.newRateLastId
            };
            
            var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(li, rate);
            
            
            this.nodes.weightRangeStart.value = '';
            this.nodes.weightRangeEnd.value = '';
            this.nodes.subtotalRangeStart.value = '';
            this.nodes.subtotalRangeEnd.value = '';
            this.nodes.flatCharge.value = '';
            this.nodes.perItemCharge.value = '';
            this.nodes.subtotalPercentCharge.value = '';
            this.nodes.perKgCharge.value = '';
            
        }
    },
    
    cancel: function()
    {
        console.info('cancel');
        
        if(!this.rate.ID)
        {
            this.hideNewForm();
        }
    },
    
    remove: function()
    {
        if(confirm(Backend.DeliveryZone.ShippingRate.prototype.Messages.confirmDelete))
        {
            if(!this.rate.ID.match(/^new/))
            {
                new Ajax.Request(Backend.DeliveryZone.ShippingService.prototype.Links.deleteRate + '/' + this.rate.ID);    
            }
            
            Element.remove(this.nodes.root);
        }
    }
}