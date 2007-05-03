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
        if(Backend.DeliveryZone.prototype.activeZone != id)
        {
            Backend.DeliveryZone.prototype.activeZone = id;
    		Backend.DeliveryZone.prototype.treeBrowser.showFeedback(id);
            
            Backend.ajaxNav.add('zone_' + id);
            this.tabControl.activateTab($('tabDeliveryZoneCountry'), function() { 
                Backend.DeliveryZone.prototype.treeBrowser.hideFeedback(id);
            });
        }
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