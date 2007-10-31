/**
 *	@author Integry Systems
 */
 
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
		Backend.Breadcrumb.setTree(Backend.DeliveryZone.prototype.treeBrowser);
		
		Backend.DeliveryZone.prototype.treeBrowser.def_img_x = 'auto';
		Backend.DeliveryZone.prototype.treeBrowser.def_img_y = 'auto';
				
		Backend.DeliveryZone.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		Backend.DeliveryZone.prototype.treeBrowser.setOnClickHandler(this.activateZone.bind(this));
        
        Event.observe($("newZoneInputButton"), 'click', function(e){ Event.stop(e); this.addNewZone(); }.bind(this))
        
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
        
        if(!Backend.ajaxNav.getHash().match(/zone_-?\d+#\w+/)) window.location.hash = '#zone_-1#tabDeliveryZoneShipping__';
		this.tabControl = TabControl.prototype.getInstance('deliveryZoneManagerContainer', this.craftTabUrl, this.craftContainerId, {}); 
        
        this.bindEvents();
	},

    bindEvents: function()
    {
        Event.observe($("deliveryZone_delete"), 'click', function(e) 
        {
            Event.stop(e);
            this.deleteZone();
        }.bind(this));
    },
    
    deleteZone: function()
    {
        if(confirm(Backend.DeliveryZone.prototype.Messages.confirmZoneDelete)) 
        {
		    new LiveCart.AjaxRequest(
    			Backend.DeliveryZone.prototype.Links.remove + '/' + Backend.DeliveryZone.prototype.activeZone,
    			false,
                function(response) 
                { 
                    response = eval("(" + response.responseText + ")");
                    if('success' == response.status)
                    {
                        Backend.DeliveryZone.prototype.treeBrowser.deleteItem(Backend.DeliveryZone.prototype.activeZone, true);
                        var firstId = parseInt(Backend.DeliveryZone.prototype.treeBrowser._globalIdStorage[1]);
                        if(firstId)
                        {
                            Backend.DeliveryZone.prototype.treeBrowser.selectItem(firstId, true);
                        }
                    }
                }.bind(this)
            );
        }
    },
    
	addNewZone: function()
	{
		new LiveCart.AjaxRequest(
			Backend.DeliveryZone.prototype.Links.create,
			false,
            function(response) 
            { 
                this.afterNewZoneAdded(eval("(" + response.responseText + ")")); 
            }.bind(this)
		);
	},

	afterNewZoneAdded: function(response)
	{
        Backend.DeliveryZone.prototype.treeBrowser.insertNewItem(0, response.zone.ID, response.zone.name, 0, 0, 0, 0, 'SELECT');
        this.activateZone(response.zone.ID);
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
        $A(treeBranch).each(function(node)
		{
            Backend.DeliveryZone.prototype.treeBrowser.insertNewItem(rootId, node.ID, node.name, null, 0, 0, 0, '', 1);
            this.treeBrowser.showItemSign(node.ID, 0);
            var zone = document.getElementsByClassName("standartTreeRow", $("deliveryZoneBrowser")).last();
            zone.id = 'zone_' + node.ID;
            zone.onclick = function()
            {
                Backend.DeliveryZone.prototype.treeBrowser.selectItem(node.ID, true);
            }
		}.bind(this));
	},
	
	activateZone: function(id)
	{
        Backend.Breadcrumb.display(id);
        if(id == -1)
        {
            if(Backend.ajaxNav.getHash().match(/tabDeliveryZoneCountry/))
            {
                Backend.ajaxNav.ignoreNextAdd = false;
                Backend.ajaxNav.add('zone_' + id + '#tabDeliveryZoneShipping');
                Backend.ajaxNav.ignoreNextAdd = true;
            }
            
            var activateTab = $('tabDeliveryZoneShipping');
            $("tabDeliveryZoneCountry").hide();
            $("tabDeliveryZoneTaxes").hide();
            $("deliveryZone_delete").parentNode.hide();
        }
        else
        {
            var activateTab = $('tabDeliveryZoneCountry');
            $("tabDeliveryZoneCountry").show();
            $("tabDeliveryZoneTaxes").show();
            $("deliveryZone_delete").parentNode.show();
        }
        
        if(Backend.DeliveryZone.prototype.activeZone && Backend.DeliveryZone.prototype.activeZone != id)
        {
            Backend.DeliveryZone.prototype.activeZone = id;
    		Backend.DeliveryZone.prototype.treeBrowser.showFeedback(id);
            
            Backend.ajaxNav.add('zone_' + id);
            
            this.tabControl.activateTab(activateTab, function() { 
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
			 try 
			 { 
			     response = eval('(' + response + ')'); 
			 } 
			 catch(e) 
			 { 
			     return false; 
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
             try 
             { 
                 response = eval('(' + response + ')'); 
             } 
             catch(e) 
             { 
                 return false; 
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
             try 
             { 
                 response = eval('(' + response + ')'); 
             } 
             catch(e) 
             { 
                 return false; 
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
        
		// I did't found out if sorting the fields realy matters, but they are slowing things down for sure
//        this.sortSelect(this.nodes.inactiveCountries);
//        this.sortSelect(this.nodes.activeCountries);
//        this.sortSelect(this.nodes.inactiveStates);
//        this.sortSelect(this.nodes.activeStates);
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

        this.nodes.addCountryButton           = this.nodes.root.down('.' + this.prefix + 'addCountry');
        this.nodes.removeCountryButton        = this.nodes.root.down('.' + this.prefix + 'removeCountry');
        this.nodes.addStateButton             = this.nodes.root.down('.' + this.prefix + 'addState');
        this.nodes.removeStateButton          = this.nodes.root.down('.' + this.prefix + 'removeState');
        
        this.nodes.inactiveCountries          = this.nodes.root.down('.' + this.prefix + 'inactiveCountries');
        this.nodes.activeCountries            = this.nodes.root.down('.' + this.prefix + 'activeCountries');
        this.nodes.inactiveStates             = this.nodes.root.down('.' + this.prefix + 'inactiveStates');
        this.nodes.activeStates               = this.nodes.root.down('.' + this.prefix + 'activeStates');
        
        this.nodes.cityMasks                  = this.nodes.root.down('.' + this.prefix + 'cityMasks');
        this.nodes.cityMasksList              = this.nodes.cityMasks.down('.' + this.prefix + 'cityMasksList');
        this.nodes.cityMaskNew                = this.nodes.cityMasks.down('.' + this.prefix + 'newMask');
        this.nodes.cityMaskNewButton          = this.nodes.cityMasks.down('.' + this.prefix + 'newMaskButton');
        this.nodes.cityMaskNewCancelButton    = this.nodes.cityMasks.down('.' + this.prefix + 'cancelNewMask');
        this.nodes.cityMaskNewShowButton      = this.nodes.cityMasks.down('.' + this.prefix + 'showNewMaskForm');
        this.nodes.cityMaskNewForm            = this.nodes.cityMasks.down('.' + this.prefix + 'maskForm');
		
        this.nodes.zipMasks                   = this.nodes.root.down('.' + this.prefix + 'zipMasks');
        this.nodes.zipMasksList               = this.nodes.zipMasks.down('.' + this.prefix + 'zipMasksList');
        this.nodes.zipMaskNew                 = this.nodes.zipMasks.down('.' + this.prefix + 'newMask');
        this.nodes.zipMaskNewButton           = this.nodes.zipMasks.down('.' + this.prefix + 'newMaskButton');
        this.nodes.zipMaskNewCancelButton     = this.nodes.zipMasks.down('.' + this.prefix + 'cancelNewMask');
        this.nodes.zipMaskNewShowButton       = this.nodes.zipMasks.down('.' + this.prefix + 'showNewMaskForm');
        this.nodes.zipMaskNewForm             = this.nodes.zipMasks.down('.' + this.prefix + 'maskForm');
		
        this.nodes.addressMasks               = this.nodes.root.down('.' + this.prefix + 'addressMasks');
        this.nodes.addressMasksList           = this.nodes.addressMasks.down('.' + this.prefix + 'addressMasksList');
        this.nodes.addressMaskNew             = this.nodes.addressMasks.down('.' + this.prefix + 'newMask');
        this.nodes.addressMaskNewButton       = this.nodes.addressMasks.down('.' + this.prefix + 'newMaskButton');
        this.nodes.addressMaskNewCancelButton = this.nodes.addressMasks.down('.' + this.prefix + 'cancelNewMask');
        this.nodes.addressMaskNewShowButton   = this.nodes.addressMasks.down('.' + this.prefix + 'showNewMaskForm');
        this.nodes.addressMaskNewForm         = this.nodes.addressMasks.down('.' + this.prefix + 'maskForm');

        this.nodes.zonesAndUnions             = this.nodes.root.down('.' + this.prefix + 'regionsAndUnions').getElementsByTagName('a');
        
        this.nodes.observedElements = document.getElementsByClassName("observed", this.nodes.form);
    },
	
	bindExistingMask: function(li)
	{
	    li = $(li);
	    Event.observe(li.down(".countriesAndStates_saveMaskButton"), "click", function(e) 
	    {
	        Event.stop(e);
	        Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li)
	    }.bind(this));
	    
	    Event.observe(li.down(".countriesAndStates_cancelMask"), "click", function(e) 
	    {
	        Event.stop(e);
	        Backend.DeliveryZone.CountriesAndStates.prototype.toggleMask(li, true);
	    }.bind(this));
	},
    
    toggleMask: function(li, cancel)
    {
        var self = Backend.DeliveryZone.CountriesAndStates.prototype.getInstance(li.up('form'));
        var input = li.down('input');
		var form  = li.down('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'existingMaskForm');
        var title = li.down('.maskTitle');
        if(form.style.display != 'none')
        {      
		    if(cancel)
			{
				input.value = li.oldValue 
			}
			
			var url = null;
            var list = null;   
  
            if((list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'cityMasksList')))
            { 
                url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask;
            }
            else if((list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'zipMasksList')))
            {
                url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask;
            }
            else if((list = $(input).up('.' + Backend.DeliveryZone.CountriesAndStates.prototype.prefix + 'addressMasksList')))
            {
                url = Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask;
            }
            
            var activeList = ActiveList.prototype.getInstance(list);
			if(!cancel)
            {
	            new LiveCart.AjaxRequest(
	                url + "/" + activeList.getRecordId(li) + '&mask=' + li.down('input').value,
	                false,
	                function(response) {
	                    var response = eval('(' + response.responseText + ')');
	                    
	                    if(response.status == 'success') 
	                    {
	                        form.style.display = 'none';
	                        title.style.display = 'inline';
	                        title.update(input.value);
	                        ActiveForm.prototype.resetErrorMessage(input);
	                    }
	                    else
	                    {
	                        ActiveForm.prototype.setErrorMessage(input, response.errors.mask, true);
	                    }
	                }.bind(self)
	            );
			}
			else
			{
                form.style.display = 'none';
                title.style.display = 'inline';
			}
        }
        else
        {
			li.oldValue = input.value;
			form.style.display = 'inline';
			title.style.display = 'none';
        }
    },
    
    bindEvents: function()
    {
        Event.observe(this.nodes.addCountryButton, 'click', function(e) { Event.stop(e); this.addCountry(); }.bind(this));
        Event.observe(this.nodes.removeCountryButton, 'click', function(e) { Event.stop(e); this.removeCountry(); }.bind(this));
        Event.observe(this.nodes.addStateButton, 'click', function(e) { Event.stop(e); this.addState(); }.bind(this));
        Event.observe(this.nodes.form, 'submit', function(e) { Event.stop(e); this.save(); }.bind(this));
        Event.observe(this.nodes.removeStateButton, 'click', function(e) { Event.stop(e); this.removeState(); }.bind(this));
        Event.observe(this.nodes.cityMaskNewButton, 'click', function(e) { Event.stop(e); this.addNewCityMask(this.nodes.cityMaskNew); }.bind(this));
        Event.observe(this.nodes.zipMaskNewButton, 'click', function(e) { Event.stop(e); this.addNewZipMask(this.nodes.zipMaskNew); }.bind(this));
        Event.observe(this.nodes.addressMaskNewButton, 'click', function(e) { Event.stop(e); this.addNewAddressMask(this.nodes.addressMaskNew); }.bind(this));

        Event.observe(this.nodes.cityMaskNewCancelButton, 'click', function(e) { Event.stop(e); this.cancelNewMaskForm(this.nodes.cityMaskNewCancelButton.up("fieldset")); }.bind(this));
        Event.observe(this.nodes.zipMaskNewCancelButton, 'click', function(e) { Event.stop(e); this.cancelNewMaskForm(this.nodes.zipMaskNewCancelButton.up("fieldset")); }.bind(this));
        Event.observe(this.nodes.addressMaskNewCancelButton, 'click', function(e) { Event.stop(e); this.cancelNewMaskForm(this.nodes.addressMaskNewCancelButton.up("fieldset")); }.bind(this));

        Event.observe(this.nodes.cityMaskNewShowButton, 'click', function(e) { Event.stop(e); this.showNewMaskForm(this.nodes.cityMaskNewShowButton.up("fieldset")); }.bind(this));
        Event.observe(this.nodes.zipMaskNewShowButton, 'click', function(e) { Event.stop(e); this.showNewMaskForm(this.nodes.zipMaskNewShowButton.up("fieldset")); }.bind(this));
        Event.observe(this.nodes.addressMaskNewShowButton, 'click', function(e) { Event.stop(e); this.showNewMaskForm(this.nodes.addressMaskNewShowButton.up("fieldset")); }.bind(this));


        $A(this.nodes.zonesAndUnions).each(function(zoneOrUnion) {
            Event.observe(zoneOrUnion, 'click', function(e) { Event.stop(e); this.selectZoneOrUnion(zoneOrUnion.hash.substring(0,1) == '#' ? zoneOrUnion.hash.substring(1) : zoneOrUnion.hash); }.bind(this));
        }.bind(this));
        
        $A(this.nodes.observedElements).each(function(element) {
            Event.observe(element, 'change', function(e) { this.save(e) }.bind(this));
        }.bind(this));
		
		
		
        window.onunload = function(e) { this.save(); }.bind(this);
		
        
        Event.observe(this.nodes.addressMaskNew, 'keyup', function(e) 
        {
           if(KeyboardEvent.prototype.init(e).isEnter()) 
           {
               if(!e.target) e.target = e.srcElement;
               this.addNewAddressMask(e.target);
           }
        }.bind(this));
        
        Event.observe(this.nodes.zipMaskNew, 'keyup', function(e) 
        {
           if(KeyboardEvent.prototype.init(e).isEnter()) 
           {
               if(!e.target) e.target = e.srcElement;
               this.addNewZipMask(e.target);
           }
        }.bind(this));
        
        Event.observe(this.nodes.cityMaskNew, 'keyup', function(e) 
        {
           if(KeyboardEvent.prototype.init(e).isEnter()) 
           {
               if(!e.target) e.target = e.srcElement;
               this.addNewCityMask(e.target);
           }
        }.bind(this));
        
        document.getElementsByClassName(this.prefix + 'mask').each(function(mask) {
           this.bindMask(mask);
        }.bind(this));
    },
    
    bindMask: function(mask) 
    {
       Event.observe(mask, 'keyup', function(e) 
       { 
           if(KeyboardEvent.prototype.init(e).isEnter()) {
               if(!e.target) e.target = e.srcElement;
               this.toggleMask(e.target.up('li'));
           }
       }.bind(this));
    },
	
	cancelNewMaskForm: function(maskRoot)
	{
        var input = maskRoot.down("input")
        var form  = maskRoot.down('.' + this.prefix + 'maskForm');
        var show  = maskRoot.down('.' + this.prefix + 'showNewMaskForm');
		
		input.value = "";
		show.show();
		form.hide();
	}, 
    
    showNewMaskForm: function(maskRoot)
    {
        var input = maskRoot.down("input")
        var form  = maskRoot.down('.' + this.prefix + 'maskForm');
        var show  = maskRoot.down('.' + this.prefix + 'showNewMaskForm');
        
        input.focus();
        show.hide();
        form.show();
    }, 
    
    save: function(e) {
        this.saving = true;
        
        var indicator = null;
        
		if (e)
        {
			var el = Event.element(e);
			if (el.hasClassName('checkbox'))
			{
				el.hide();
			}
			indicator = el.parentNode.down('.progressIndicator');
		}
        
        Backend.DeliveryZone.prototype.treeBrowser.setItemText(Backend.DeliveryZone.prototype.activeZone, this.nodes.name.value)
        var request = new LiveCart.AjaxRequest(this.nodes.form, indicator, function() { if (el) { el.show(); }});
    
        this.saving = false;
    },
    
    selectZoneOrUnion: function(regionName) 
    {
        includedCountriesCodes = Backend.DeliveryZone.countryGroups[regionName];
        
        $A(this.nodes.inactiveCountries.options).each(function(option) 
        {
            option.selected = includedCountriesCodes.indexOf(option.value) !== -1 ? true : false;
        });
    },
    
    addNewCityMask: function(mask)
    {
        new LiveCart.AjaxRequest(
            Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveCityMask + "/" + '?zoneID=' + this.zoneID + '&mask=' + mask.value,
            false,
            function(response) 
            {
                response = eval('(' + response.responseText + ')');
                if('success' == response.status)
                {
                    var activeList = ActiveList.prototype.getInstance(this.nodes.cityMasksList);
                    var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />', true);

                    mask.value = '';
                    this.bindMask(li.down('input'));
                    ActiveForm.prototype.resetErrorMessage(mask);
					
                    this.cancelNewMaskForm(this.nodes.cityMaskNewCancelButton.up("fieldset"));
                }
                else
                {
                    ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
                }
            }.bind(this)
        );
    },
    
    addNewZipMask: function(mask)
    {
        new LiveCart.AjaxRequest (
            Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveZipMask + "/" + '?zoneID=' + this.zoneID + '&mask=' + mask.value,
            false,
            function(response) 
            {
                response = eval('(' + response.responseText + ')');
                if('success' == response.status)
                {
                    var activeList = ActiveList.prototype.getInstance(this.nodes.zipMasksList);
                    var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />', true);

                    mask.value = '';
                    this.bindMask(li.down('input'));
                    ActiveForm.prototype.resetErrorMessage(mask);
					
			        this.cancelNewMaskForm(this.nodes.zipMaskNewCancelButton.up("fieldset"));
                }
                else
                {
                    ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
                }
            }.bind(this)
        );
    },
    
    addNewAddressMask: function(mask)
    {
        new LiveCart.AjaxRequest(
            Backend.DeliveryZone.CountriesAndStates.prototype.Links.saveAddressMask + "/" + '?zoneID=' + this.zoneID + '&mask=' + mask.value,
            false,
            function(response) 
            {
                response = eval('(' + response.responseText + ')');
                if('success' == response.status)
                {
                    var activeList = ActiveList.prototype.getInstance(this.nodes.addressMasksList);
                    var li = activeList.addRecord(response.ID, '<span class="maskTitle">' + mask.value + '</span><input type="text" value="' + mask.value + '" style="display:none;" />', true);
    
                    mask.value = '';
                    this.bindMask(li.down('input'));
                    ActiveForm.prototype.resetErrorMessage(mask);
					
                    this.cancelNewMaskForm(this.nodes.addressMaskNewCancelButton.up("fieldset"));
                }
                else
                {
                    ActiveForm.prototype.setErrorMessage(mask, response.errors.mask, true);
                }
            }.bind(this)
        );
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
        inactive.substr(0, active.length - 1);
        
        
        new LiveCart.AjaxRequest(url + "/" + this.zoneID + "?" + active + inactive);
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
    }
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
             try 
             { 
                 response = eval('(' + response + ')'); 
             } 
             catch(e) 
             { 
                 return false; 
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
                var newServiceForm = $("shippingService_" + this.getRecordId(li, 2) + '_');
                if(newServiceForm.up().style.display == 'block')
                {
                    Backend.DeliveryZone.ShippingService.prototype.getInstance(newServiceForm).hideNewForm();
                }

                Backend.DeliveryZone.ShippingService.prototype.getInstance(li.down('form'));

                this.toggleContainer(li, 'edit');
            }
        },
       
        afterEdit:      function(li, response)
        {
            var newServiceForm = $("shippingService_" + this.getRecordId(li, 2) + '_');
            if(newServiceForm.up().style.display == 'block')
            {
                Backend.DeliveryZone.ShippingService.prototype.getInstance(newServiceForm).hideNewForm();
            }
            this.getContainer(li, 'edit').update(response);
            this.toggleContainer(li, 'edit');
        },
         
        beforeSort:     function(li, order)
        {
            return Backend.DeliveryZone.ShippingService.prototype.Links.sortServices + '?target=' + "shippingService_servicesList_" + this.getRecordId(li, 2) + "&" + order
        },
    
        afterSort:      function(li, response) { }
    },
     
    instances: {},
    
    prefix: 'shippingService_',
    
    initialize: function(root, service)
    {
        this.service = service;
        this.deliveryZoneId = this.service.DeliveryZone ? this.service.DeliveryZone.ID : '';
        this.findUsedNodes(root);
        this.bindEvents();
        this.rangeTypeChanged();
        this.servicesActiveList = ActiveList.prototype.getInstance(this.nodes.servicesList);
        
        if(this.service.ID)
        {
            Form.State.backup(this.nodes.form);
			
			this.nodes.root.down('.rangeTypeStatic').show();
			document.getElementsByClassName('rangeType', this.nodes.root).each(function(rangeTypeField) { rangeTypeField.hide(); }.bind(this));
        }
    },
        
    getInstance: function(rootNode, service)
    {
        var rootId = $(rootNode).id;
        if (!Backend.DeliveryZone.ShippingService.prototype.instances[rootId])
        {
            Backend.DeliveryZone.ShippingService.prototype.instances[rootId] = new Backend.DeliveryZone.ShippingService(rootId, service);
        }
        
        var instance = Backend.DeliveryZone.ShippingService.prototype.instances[rootId];
        instance.rangeTypeChanged();
        
        return instance;
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
        
        this.nodes.servicesList = $$('.' + this.prefix + 'servicesList_' + this.deliveryZoneId)[0];
        this.nodes.ratesList = $(this.prefix + 'ratesList_' + this.deliveryZoneId + '_' + (this.service.ID ? this.service.ID : ''));
        this.nodes.ratesNewForm = $(this.prefix + 'new_rate_' + this.deliveryZoneId + '_' + (this.service.ID ? this.service.ID : '') + '_form');
        
        if(!this.service.ID)
        {
            this.nodes.menu = $(this.prefix + "menu_" + this.deliveryZoneId);
            this.nodes.menuCancelLink = $(this.prefix + "new_" + this.deliveryZoneId + "_cancel");
            this.nodes.menuShowLink = $(this.prefix + "new_" + this.deliveryZoneId + "_show");
            this.nodes.menuForm = $(this.prefix + "new_service_" + this.deliveryZoneId + "_form");
        }
        
        this.nodes.name = this.nodes.root.down('.' + this.prefix + 'name');
    },
    
    bindEvents: function()
    {
       Event.observe(this.nodes.save, 'click', function(e) { Event.stop(e); this.save(); }.bind(this));
       Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       if(!this.service.ID)
       {
           Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       }
       
       $A(this.nodes.rangeTypes).each(function(radio)
       {
           Event.observe(radio, 'click', function(e) { this.rangeTypeChanged(); }.bind(this));
       }.bind(this));
       
       $A(document.getElementsByClassName('shippingService_rateFloatValue', this.nodes.root)).each(function(input)
       {
           Event.observe(input, "keyup", function(e){ NumericFilter(this); });
       }.bind(this));
	   
            
        Event.observe(this.nodes.form, 'submit', function(e)
        {
            Event.stop(e);
            this.save();
        }.bind(this), false);
    },
    
    rangeTypeChanged: function()
    {
        var radio = null;
        $A(this.nodes.rangeTypes).each(function(r){ if(r.checked) radio = r; });
        
        if(radio.value == 0) 
        {
            document.getElementsByClassName(this.prefix + "subtotalRange", this.nodes.root).each(function(fieldset) { fieldset.style.display = 'none'; });
            document.getElementsByClassName(this.prefix + "subtotalPercentCharge", this.nodes.root).each(function(fieldset) { fieldset.up('fieldset').style.display = 'none'; });
            document.getElementsByClassName(this.prefix + "perKgCharge", this.nodes.root).each(function(fieldset) { fieldset.up('fieldset').style.display = 'block'; });
            document.getElementsByClassName(this.prefix + "weightRange", this.nodes.root).each(function(fieldset) { fieldset.style.display = 'block'; });
        }
        else
        {
            document.getElementsByClassName(this.prefix + "subtotalRange", this.nodes.root).each(function(fieldset) { fieldset.style.display = 'block'; });
            document.getElementsByClassName(this.prefix + "subtotalPercentCharge", this.nodes.root).each(function(fieldset) { fieldset.up('fieldset').style.display = 'block'; });
            document.getElementsByClassName(this.prefix + "perKgCharge", this.nodes.root).each(function(fieldset) { fieldset.up('fieldset').style.display = 'none'; });
            document.getElementsByClassName(this.prefix + "weightRange", this.nodes.root).each(function(fieldset) { fieldset.style.display = 'none'; });
        }
    },
    
    showNewForm: function()
    {
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.show("shippingService_add", this.nodes.menuForm);
		
        var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(this.nodes.ratesNewForm, Backend.DeliveryZone.ShippingRate.prototype.newRate);
        newForm.showNewForm(true);		
    },
    
    hideNewForm: function()
    {
        var menu = new ActiveForm.Slide(this.nodes.menu);
        menu.hide("shippingService_add", this.nodes.menuForm);
                
        $A(this.nodes.ratesList.getElementsByTagName('li')).each(function(li) {
           Element.remove(li);
        });
        
        $A(this.nodes.root.getElementsByTagName('input')).each(function(input) {
            if(input.type == 'text') input.value = ''; 
        });
    },
    
    save: function()
    {
		var lastLi = this.nodes.ratesList.childElements().last();
		
		// Remove last rate if it is new and empty
		if(lastLi && lastLi.id.match(/new/))
		{
			var emptyValues = true;
            lastLi.getElementsBySelector("input").each(function(input)
			{
				if(parseFloat(input.value)) 
				{
					emptyValues = false;
					throw $break;
				}
			}.bind(this));
			
			if(emptyValues)
			{
				Element.remove(lastLi);
			}
		}
		
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        this.nodes.form.action = this.service.ID 
            ? Backend.DeliveryZone.ShippingService.prototype.Links.update
            : Backend.DeliveryZone.ShippingService.prototype.Links.create;
            
        new LiveCart.AjaxRequest(
            this.nodes.form,
            null,
            function(response) 
            { 
                var response = eval("(" + response.responseText + ")");
                this.afterSave(response);     
            }.bind(this)
        );
    },
    
    afterSave: function(response)
    {
        if(response.status == 'success')
        {
            ActiveForm.prototype.resetErrorMessages(this.nodes.form);
            if(!this.service.ID)
            {
                ratesCount = this.nodes.root.down(".activeList").getElementsByTagName("li").length;
                rangeTypeString = response.service.rangeType == 0 ? Backend.DeliveryZone.TaxRate.prototype.Messages.weightBasedRates : Backend.DeliveryZone.TaxRate.prototype.Messages.subtotalBasedRates;
				
                var li = this.servicesActiveList.addRecord(response.service.ID, '<span class="' + this.prefix + 'servicesList_title">' + this.nodes.name.value + ' ( <b class="ratesCount">' + ratesCount + '</b>' + rangeTypeString  + ' )</span>');
                this.hideNewForm();
            }
            else
            {
                Form.State.backup(this.nodes.form);
				
				this.nodes.root.up('li').down('.ratesCount').innerHTML = this.nodes.root.down(".activeList").getElementsByTagName("li").length;
                this.servicesActiveList.toggleContainer(this.nodes.root.up('li'), 'edit', 'yellow');
				
                if($H(response.service.newRates).size() > 0)
                {
                    var regexps = {};
                    $H(response.service.newRates).each(function(id) { regexps[id.value] = new RegExp(id.key); }.bind(this));
                    
                    $A(this.nodes.root.down('.activeList').getElementsByTagName('*')).each(function(elem)
                    {						
                        if(elem.id) 
						{
						    $H(regexps).each(function(id) { 
							    if(elem.id.match(id.value))
								{
			                        if(elem.tagName == 'LI')
			                        {
			                            Backend.DeliveryZone.ShippingRate.prototype.instances[elem.id.replace(id.value, id.key)] = Backend.DeliveryZone.ShippingRate.prototype.instances[elem.id]
			                        }
									
							        elem.id = elem.id.replace(id.value, id.key);
									return;
								} 
							}.bind(this));
						}
						
                        if(elem.name) 
                        {
                            $H(regexps).each(function(id) { 
                                elem.name = elem.name.replace(id.value, id.key);
                            }.bind(this));
                        }
                    }.bind(this));
                }
            }
        }
        else
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
        }
    },
    
    cancel: function()
    {
        if(!this.service.ID)
        {
            this.hideNewForm();
        }
        else
        {
            this.servicesActiveList.toggleContainerOff(this.nodes.root.up('.activeList_editContainer'));
            Form.State.restore(this.nodes.form);
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
        'afterDelete': function(li) 
        {
             try 
             { 
                 response = eval('(' + response + ')'); 
             } 
             catch(e) 
             { 
                 return false; 
             }
        },
        'beforeEdit': function(li) 
        {

        },
        'afterEdit': function(li, response) 
        {

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
        this.deliveryZoneId = this.rate.ShippingService.DeliveryZone ? this.rate.ShippingService.DeliveryZone.ID : '';
        this.switchMetricsEnd = Backend.UnitConventer.prototype.getInstance("UnitConventer_Root_shippingService_" + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (rate.ShippingService.ID ? rate.ShippingService.ID : '') + "_" + (this.rate.ID ? this.rate.ID : '') + "_weightRangeEnd");
        this.switchMetricsStart = Backend.UnitConventer.prototype.getInstance("UnitConventer_Root_shippingService_" + (this.deliveryZoneId ? this.deliveryZoneId : '') + "_" + (rate.ShippingService.ID ? rate.ShippingService.ID : '') + "_" + (this.rate.ID ? this.rate.ID : '') + "_weightRangeStart");
    
 
        this.findUsedNodes(root);
        this.bindEvents();
        
        this.ratesActiveList = ActiveList.prototype.getInstance(this.nodes.ratesActiveList, Backend.DeliveryZone.ShippingRate.prototype.Callbacks);
        
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

        if(!this.rate.ID)
        {
            this.nodes.menuCancelLink   = $(this.prefix + "new_rate_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_cancel");
            this.nodes.menuShowLink = $(this.prefix + "new_rate_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_show");
            this.nodes.menu =$(this.prefix + "rate_menu_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID);
            this.nodes.menuForm = $(this.prefix + "new_rate_" + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_form");
        }
        
        this.nodes.ratesActiveList = $(this.prefix + 'ratesList_' + this.deliveryZoneId + '_' + this.rate.ShippingService.ID);
        
        this.nodes.weightRangeStart = this.nodes.root.down('.weightRangeStart').down('.UnitConventer_NormalizedWeight');
        this.nodes.weightRangeStartHiValue = this.nodes.root.down('.weightRangeStart').down('.UnitConventer_HiValue');
        this.nodes.weightRangeStartLoValue = this.nodes.root.down('.weightRangeStart').down('.UnitConventer_LoValue');
        
		this.nodes.weightRangeEnd = this.nodes.root.down('.weightRangeEnd').down('.UnitConventer_NormalizedWeight');
        this.nodes.weightRangeEndHiValue = this.nodes.root.down('.weightRangeEnd').down('.UnitConventer_HiValue');
        this.nodes.weightRangeEndLoValue = this.nodes.root.down('.weightRangeEnd').down('.UnitConventer_LoValue');

		
		this.nodes.subtotalRangeStart = this.nodes.root.down('.' + this.prefix + 'subtotalRangeStart');
        this.nodes.subtotalRangeEnd = this.nodes.root.down('.' + this.prefix + 'subtotalRangeEnd');
        this.nodes.flatCharge = this.nodes.root.down('.' + this.prefix + 'flatCharge');
        this.nodes.perItemCharge = this.nodes.root.down('.' + this.prefix + 'perItemCharge');
        this.nodes.subtotalPercentCharge = this.nodes.root.down('.' + this.prefix + 'subtotalPercentCharge');
        this.nodes.perKgCharge = this.nodes.root.down('.' + this.prefix + 'perKgCharge');
    },
    
    bindEvents: function()
    {
       if(!this.rate.ID)
       {
	       $A(this.nodes.root.getElementsByTagName('input')).each(function(input)
	       {
	           Event.observe(input, 'keypress', function(e) { 
                  if(e.keyCode == 13)
                  {
                      Event.stop(e);
                      this.save(e);
                  } 
	           }.bind(this), false)
	       }.bind(this)); 
		
//           Event.observe(this.nodes.save, 'click', function(e) { Event.stop(e); this.save(e); }.bind(this));
//           Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancel();}.bind(this));
           Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       }

       Event.observe(this.switchMetricsEnd.nodes.switchUnits, 'click', function(e) { this.switchMetricsStart.switchUnitTypes(); }.bind(this));
    },
    
    showNewForm: function(noHighLight)
    {
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
			
			this.afterAdd({validation: 'success'}, rate, noHighLight);
    },
    
    hideNewForm: function()
    {
        var manu = new ActiveForm.Slide(this.nodes.menu);
        manu.hide("addNewRate", this.nodes.menuForm);
    },
  
    afterAdd: function(response, rate, noHighLight)
    {
        if(response.validation == 'success')
        {
            var newId = Backend.DeliveryZone.ShippingRate.prototype.newRateLastId;
            
            var li = this.ratesActiveList.addRecord('new' + newId, this.nodes.root, false);
            
            var idStart = this.prefix + this.deliveryZoneId + '_' + this.rate.ShippingService.ID + "_";
            var idStartRegexp = new RegExp(idStart);
            document.getElementsByClassName(this.prefix + 'rateFloatValue', li).each(function(input) {
                Event.observe(input, "keyup", function(e){ NumericFilter(this) });
                input.id = input.id.replace(idStartRegexp, idStart + 'new' + newId);
                Event.observe(input.up().down('label'), 'click', function(e) { Event.stop(e); input.focus(); });
            }.bind(this));
			
            document.getElementsByClassName('UnitConventer_Root', li).each(function(el) {
                el.id = el.id.replace(idStartRegexp, idStart + 'new' + newId);
            }.bind(this));
			
			var startHi = li.down('.weightRangeStart').down('.UnitConventer_HiValue');
            var startLo = li.down('.weightRangeStart').down('.UnitConventer_LoValue');
            var endHi = li.down('.weightRangeEnd').down('.UnitConventer_HiValue');
            var endLo = li.down('.weightRangeEnd').down('.UnitConventer_LoValue');
			
			startHi.id = startHi.id.replace(idStartRegexp, idStart + 'new' + newId)
            Event.observe(startHi.up('.shippingService_weightRange').down('label'), 'click', function(e) { Event.stop(e); startHi.focus(); });
            startLo.id = startLo.id.replace(idStartRegexp, idStart + 'new' + newId)
			
            endHi.id = endHi.id.replace(idStartRegexp, idStart + 'new' + newId)
            endLo.id = endLo.id.replace(idStartRegexp, idStart + 'new' + newId)	
			
			var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(li, rate);
            
            this.nodes.weightRangeStart.value = '0';
            this.nodes.weightRangeStartHiValue.value = '0';
            this.nodes.weightRangeStartLoValue.value = '0';
			
            this.nodes.weightRangeEnd.value = '0';
            this.nodes.weightRangeEndHiValue.value = '0';
            this.nodes.weightRangeEndLoValue.value = '0';
            
			this.nodes.subtotalRangeStart.value = '0';
            this.nodes.subtotalRangeEnd.value = '0';
            this.nodes.flatCharge.value = '0';
            this.nodes.perItemCharge.value = '0';
            this.nodes.subtotalPercentCharge.value = '0';
            this.nodes.perKgCharge.value = '0';
			
            Backend.DeliveryZone.ShippingRate.prototype.newRateLastId++;
			
			if (!noHighLight)
			{
                this.ratesActiveList.highlight(li);
            }
        }
        else
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.root.up('form'), response.errors);
        }
    },
    
    cancel: function()
    {
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
                new LiveCart.AjaxRequest(Backend.DeliveryZone.ShippingService.prototype.Links.deleteRate + '/' + this.rate.ID);    
            }
            
			var rates = this.nodes.root.up(".activeList")
			var service = this.nodes.root.up('li');
			
            Element.remove(this.nodes.root);
			
            if (service)
            {
                service.down('.ratesCount').innerHTML = rates.getElementsByTagName('li').length;
            }
        }
    }
}




Backend.DeliveryZone.TaxRate = Class.create();
Backend.DeliveryZone.TaxRate.prototype = 
{
    Links: {},
    Messages: {},
 
    Callbacks: {
        'beforeDelete': function(li) 
        {
            if(confirm(Backend.DeliveryZone.TaxRate.prototype.Messages.confirmDelete))
            {
                return Backend.DeliveryZone.TaxRate.prototype.Links.remove + "/" + this.getRecordId(li);
            }
        },
        'afterDelete': function(li, response)
        {
             try 
             { 
                 response = eval('(' + response + ')'); 
             } 
             catch(e) 
             { 
                 return false; 
             }
            
            if('success' == response.status) {
				var formId = $(Backend.DeliveryZone.TaxRate.prototype.prefix + "new_taxRate_" + this.getRecordId(li, 2) + "_form").down('form').id
                if(Backend.DeliveryZone.TaxRate.prototype.instances[formId])
                {
                    var taxForm = Backend.DeliveryZone.TaxRate.prototype.getInstance(formId);
                    taxForm.addTaxOption(response.tax.ID, response.tax.name);
                }

                return true;
            }
			
			return false;
        },
        
        beforeEdit:     function(li)
        {
            if(this.isContainerEmpty(li, 'edit')) 
            {
                return Backend.DeliveryZone.TaxRate.prototype.Links.edit + '/' + this.getRecordId(li)
            }
            else 
            {
                var newRateForm = $("taxRate_" + this.getRecordId(li, 2) + '_');
                if(newRateForm.up().style.display == 'block')
                {
                    Backend.DeliveryZone.TaxRate.prototype.getInstance(newRateForm).hideNewForm();
                }

                this.toggleContainer(li, 'edit');
            }
        },
       
        afterEdit:      function(li, response)
        {
            var newRateForm = $("taxRate_" + this.getRecordId(li, 2) + '_');
            if(newRateForm.up().style.display == 'block')
            {
                Backend.DeliveryZone.TaxRate.prototype.getInstance(newRateForm).hideNewForm();
            }
            this.getContainer(li, 'edit').update(response);
            this.toggleContainer(li, 'edit');
        }
    },
     
    instances: {},
    
    prefix: 'taxRate_',
    
    initialize: function(root, rate)
    {
        this.rate = rate;
        this.deliveryZoneId = this.rate.DeliveryZone ? this.rate.DeliveryZone.ID : '';
        this.findUsedNodes(root);
        this.bindEvents();
        this.ratesActiveList = ActiveList.prototype.getInstance(this.nodes.ratesList);

        Form.State.backup(this.nodes.form);
    },
        
    getInstance: function(rootNode, rate)
    {
        var rootId = $(rootNode).id;
        if(!Backend.DeliveryZone.TaxRate.prototype.instances[rootId])
        {
            Backend.DeliveryZone.TaxRate.prototype.instances[rootId] = new Backend.DeliveryZone.TaxRate(rootId, rate);
        }
        
        return Backend.DeliveryZone.TaxRate.prototype.instances[rootId];
    },
    
    addTaxOption: function(id, name)
    {
        var formBackupId = this.nodes.form.backupId;
        Form.State.backups[formBackupId]['taxID'][0]['options'][id] = name;
        this.nodes.taxID.options[this.nodes.taxID.options.length] = new Option(name, id);
		
		if(this.nodes.taxID.options.length > 0) 
		{
			this.nodes.noAwailableTaxes.hide();
		}
		else 
		{
		    this.nodes.noAwailableTaxes.show();
        }
    },
    
    removeTaxOption: function(id)
    {
        $A(this.nodes.taxID.options).each(function(option)
        {
            if(option.value == id)
            {
                var formBackupId = this.nodes.form.backupId;
                delete Form.State.backups[formBackupId]['taxID'][0]['options'][id];
                Element.remove(option);
                throw $break;
            }
        }.bind(this));
		
        if(this.nodes.taxID.options.length > 0) 
        {
            this.nodes.noAwailableTaxes.hide();
        }
        else 
        {
            this.nodes.noAwailableTaxes.show();
        }
    },
    
    findUsedNodes: function(root)
    {
        this.nodes = {};
        
        this.nodes.root = $(root);
        this.nodes.form = this.nodes.root;

        this.nodes.controls = this.nodes.root.down('.' + this.prefix + 'controls');
        this.nodes.save = this.nodes.controls.down('.' + this.prefix + 'save');
        this.nodes.cancel = this.nodes.controls.down('.' + this.prefix + 'cancel');
        
        this.nodes.rate = this.nodes.root.down('.' + this.prefix  + 'rate');
        this.nodes.taxID = this.nodes.root.down('.' + this.prefix + 'taxID');
        this.nodes.deliveryZoneID = this.nodes.root.down('.' + this.prefix + 'deliveryZoneID');
        this.nodes.taxRateID = this.nodes.root.down('.' + this.prefix + 'taxRateID');
		this.nodes.noAwailableTaxes = this.nodes.root.down(".noAwailableTaxes");
        
        this.nodes.ratesList = $(this.prefix + "taxRatesList_" + this.deliveryZoneId)
        
        if(!this.rate.ID)
        {
            this.nodes.menu = $(this.prefix + "menu_" + this.deliveryZoneId);
            this.nodes.menuCancelLink = $(this.prefix + "new_" + this.deliveryZoneId + "_cancel");
            this.nodes.menuShowLink = $(this.prefix + "new_" + this.deliveryZoneId + "_show");
            this.nodes.menuForm = $(this.prefix + "new_taxRate_" + this.deliveryZoneId + "_form");
        }
    },
    
    bindEvents: function()
    {
       Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
      
       Event.observe(this.nodes.cancel, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       if(!this.rate.ID)
       {
           Event.observe(this.nodes.menuCancelLink, 'click', function(e) { Event.stop(e); this.cancel(); }.bind(this));
       }
       
       $A(this.nodes.rangeTypes).each(function(radio)
       {
           Event.observe(radio, 'click', function(e) { this.rangeTypeChanged(); }.bind(this));
       }.bind(this));
    },
    
    showNewForm: function()
    {
		var menu = new ActiveForm.Slide(this.nodes.menu);
		menu.show("addNewTaxRate", this.nodes.menuForm);
    },
    
    hideNewForm: function()
    {
        var menu = new ActiveForm.Slide(this.nodes.menu);

        this.nodes.menuForm.addClassName('hiding'); 
        menu.hide("addNewTaxRate", this.nodes.menuForm, null, 
                    function() 
                    {
                        this.nodes.menuForm.removeClassName('hiding'); 
                    }.bind(this)
                 );
        
        Form.State.restore(this.nodes.form);
    },
    
    save: function(event)
    {
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        var action = this.rate.ID 
            ? Backend.DeliveryZone.TaxRate.prototype.Links.update
            : Backend.DeliveryZone.TaxRate.prototype.Links.create;
            
        new LiveCart.AjaxRequest(
            action + '?' + Form.serialize(this.nodes.form),
            this.nodes.form.down('.controls').down('.progressIndicator'),
            function(response) 
            { 
                var response = eval("(" + response.responseText + ")");
                this.afterSave(response);     
            }.bind(this)
        );
    },
    
    afterSave: function(response)
    {
        if(response.status == 'success')
        {
            if(!this.rate.ID)
            {
                var li = this.ratesActiveList.addRecord(response.rate.ID, '<span class="' + this.prefix + 'taxRatesList_title">' + response.rate.Tax.name + '</span>');
                this.removeTaxOption(this.nodes.taxID.value);
                this.hideNewForm();
            }
            else
            {
                Form.State.backup(this.nodes.form);
                this.ratesActiveList.toggleContainer(this.nodes.root.up('li'), 'edit', 'yellow');
            }
        }
        else
        {
            ActiveForm.prototype.setErrorMessages(this.nodes.form, response.errors);
        }
    },
    
    cancel: function()
    {
        ActiveForm.prototype.resetErrorMessages(this.nodes.form);
        if(!this.rate.ID)
        {
            this.hideNewForm();
        }
        else
        {
            this.ratesActiveList.toggleContainerOff(this.nodes.root.up('.activeList_editContainer'));
            Form.State.restore(this.nodes.form);
        }
    }
}

