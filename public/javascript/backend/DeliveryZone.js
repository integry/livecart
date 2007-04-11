Backend.DeliveryZone = Class.create();
Backend.DeliveryZone.prototype = 
{
  	Links: {},
    
    treeBrowser: null,
  	
  	urls: new Array(),
	  
	initialize: function(zones)
	{
        console.info(zones);
        
		Backend.DeliveryZone.prototype.treeBrowser = new dhtmlXTreeObject("deliveryZoneBrowser","","", false);
		
		Backend.DeliveryZone.prototype.treeBrowser.def_img_x = 'auto';
		Backend.DeliveryZone.prototype.treeBrowser.def_img_y = 'auto';
				
		Backend.DeliveryZone.prototype.treeBrowser.setImagePath("image/backend/dhtmlxtree/");
		Backend.DeliveryZone.prototype.treeBrowser.setOnClickHandler(this.activateZone.bind(this));
        
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
				for (itemId in this.iconUrls)
				{
					this.setItemImage(itemId, this.iconUrls[itemId]);	
				}				
			}
		
    	this.insertTreeBranch(zones, 0);    
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
			self.treeBrowser.insertNewItem(rootId, node.ID, node.name, null, 0, 0, 0, '', 1);
			self.treeBrowser.showItemSign(node.ID, 0);
		});
	},
	
	activateZone: function(id)
	{
        Backend.DeliveryZone.prototype.activeZone = id;
		Backend.DeliveryZone.prototype.treeBrowser.showFeedback(id);
        
        this.tabControl.activateTab($('tabDeliveryZoneCountry'), function() { Backend.DeliveryZone.prototype.treeBrowser.hideFeedback(id) });
        
		;
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
    prefix: 'countriesAndStates_',
    
    initialize: function(root) 
    {
        this.findNodes(root);
        this.bindEvents();
    },
    
    findNodes: function(root)
    {
        this.nodes = {};
        this.nodes.root = $(root);

        this.nodes.addCountryButton     = this.nodes.root.down('.' + this.prefix + 'addCountry');
        this.nodes.removeCountryButton  = this.nodes.root.down('.' + this.prefix + 'removeCountry');
        this.nodes.addStateButton       = this.nodes.root.down('.' + this.prefix + 'addState');
        this.nodes.removeStateButton    = this.nodes.root.down('.' + this.prefix + 'removeState');
        
        this.nodes.inactiveCountries    = this.nodes.root.down('.' + this.prefix + 'inactiveCountries');
        this.nodes.activeCountries      = this.nodes.root.down('.' + this.prefix + 'activeCountries');
        this.nodes.inactiveStates       = this.nodes.root.down('.' + this.prefix + 'inactiveStates');
        this.nodes.activeStates         = this.nodes.root.down('.' + this.prefix + 'activeStates');

    },
    
    bindEvents: function()
    {
        var self = this;
        
        Event.observe(this.nodes.addCountryButton, 'click', function(e) { Event.stop(e); self.addCountry(); });
        Event.observe(this.nodes.removeCountryButton, 'click', function(e) { Event.stop(e); self.removeCountry(); });
        Event.observe(this.nodes.addStateButton, 'click', function(e) { Event.stop(e); self.addState(); });
        Event.observe(this.nodes.removeStateButton, 'click', function(e) { Event.stop(e); self.removeState(); });
    },
    
    addCountry: function()
    {
        var self = this;
        $A(this.nodes.inactiveCountries.getElementsByTagName('option')).each(function(option) 
        {
            if(!option.selected) return;
            self.nodes.activeCountries.options[self.nodes.activeCountries.length] = option;
        });
    },
    
    removeCountry: function()
    {
        var self = this;
        $A(this.nodes.activeCountries.getElementsByTagName('option')).each(function(option) 
        {
            if(!option.selected) return;
            self.nodes.inactiveCountries.options[self.nodes.inactiveCountries.length] = option;
        });
    },
    
    addState: function()
    {
        var self = this;
        $A(this.nodes.inactiveStates.getElementsByTagName('option')).each(function(option) 
        {
            if(!option.selected) return;
            self.nodes.activeStates.options[self.nodes.activeStates.length] = option;
        });
    },
    
    removeState: function()
    {
        var self = this;
        $A(this.nodes.activeStates.getElementsByTagName('option')).each(function(option) 
        {
            if(!option.selected) return;
            self.nodes.inactiveStates.options[self.nodes.inactiveStates.length] = option;
        });
    },
}