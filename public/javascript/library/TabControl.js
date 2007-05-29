Event.fire = function(element, event) 
{
   Event.observers.each(function(observer) 
   {
        if(observer[1] == event && observer[0] == element)
        {
            var func = observer[2];
            func();
        }
   });
};


var TabControl = Class.create();
TabControl.prototype = {
    __instances__: {},
    
	activeTab: null,
	indicatorImageName: "image/indicator.gif",

	initialize: function(tabContainerName, urlParserCallback, idParserCallback, callbacks)
	{
        try
        {  
            this.tabContainerName = tabContainerName;
            this.urlParserCallback = urlParserCallback;
            this.idParserCallback = idParserCallback;
            this.callbacks = callbacks ? callbacks : {};
            
            this.__nodes__();
            this.__bind__();

            this.decorateTabs();
            this.countersCache = {};
        }
        catch(e)
        {
            console.info(e)
        }
	},
    
    __nodes__: function()
    {
        this.nodes = {};
        this.nodes.tabContainer = $(this.tabContainerName);
		this.nodes.tabList = this.nodes.tabContainer.down(".tabList");
		this.nodes.tabListElements = document.getElementsByClassName("tab", this.nodes.tabList);
		this.nodes.sectionContainer = this.nodes.tabContainer.down(".sectionContainer");
    },
    
    __bind__: function()
    {
        var self = this;   
        this.nodes.tabListElements.each(function(li) 
        {
			var link = li.down('a');
            var indicator = '<img src="' + self.indicatorImageName + '" class="tabIndicator" alt="Tab indicator" style="display: none" /> ';
            
            Event.observe(link, 'click', function(e) { if(e) Event.stop(e); });
            
            li.onclick = function(e) { 
                if(!e) e = window.event;
                if(e) Event.stop(e);
                
                self.handleTabClick({'target': li}); 
            } 
   
			Event.observe(li, 'mouseover', function(e) { 
                if(e) Event.stop(e); 
                self.handleTabMouseOver({'target': li}) 
            });
			Event.observe(li, 'mouseout', function(e) { 
                if(e) Event.stop(e); 
                self.handleTabMouseOut({'target': li}) 
            });
            
            li.update(indicator + li.innerHTML);
		});
    },
    
    __init__: function()
    {
        // this.activateTab();
    },

    decorateTabs: function()
    {
        this.nodes.tabListElements.each(function(tab)
        {
            var firstLink = tab.down('a');
            new Insertion.After(firstLink, '<span class="tabCounter"> </span>');
        });  
    },

    getInstance: function(tabContainerName, urlParserCallback, idParserCallback, callbacks)
    {
        if(!TabControl.prototype.__instances__[tabContainerName])
        {
            TabControl.prototype.__instances__[tabContainerName] = new TabControl(tabContainerName, urlParserCallback, idParserCallback, callbacks);
            TabControl.prototype.__instances__[tabContainerName].__init__();
        }
        else if(false !== urlParserCallback)
        {
            TabControl.prototype.__instances__[tabContainerName].__init__();
        }
        
        return TabControl.prototype.__instances__[tabContainerName];
    },

	handleTabMouseOver: function(args)
	{
		if (this.activeTab != args.target)
		{
			Element.removeClassName(args.target, 'inactive');
			Element.addClassName(args.target, 'hover');
		}
	},

	handleTabMouseOut: function(args)
	{
		if (this.activeTab != args.target)
		{
			Element.removeClassName(args.target, 'hover');
			Element.addClassName(args.target, 'inactive');
		}
	},

	handleTabClick: function(args)
	{
        if(this.callbacks.beforeClick) this.callbacks.beforeClick.call(this);
        this.activateTab(args.target);
        if(this.callbacks.afterClick) this.callbacks.afterClick.call(this);
	},
    
    addHistory: function()
    {
        var self = this;
        setTimeout(function()
        {
            var locationHash = "#" + Backend.ajaxNav.getHash();
            self.nodes.tabListElements.each(function(tab)
            {
                if(locationHash.indexOf("#" + tab.id) !== -1)
                {
                    locationHash = locationHash.substring(0, locationHash.indexOf(tab.id) - 1);
                    throw $break;
                }
            });
            
            Backend.ajaxNav.add(locationHash.substring(1) + "#" + self.activeTab.id);
        }, dhtmlHistory.currentWaitTime);
    },

	activateTab: function(targetTab, onComplete)
	{
        targetTab = $(targetTab);
        
		if(!targetTab) 
		{
			targetTab = this.nodes.tabListElements[0];	
		}
                
		// get help context
		var helpContext = document.getElementsByClassName('tabHelp', targetTab);
		if (helpContext.length > 0)
		{
			Backend.setHelpContext(helpContext[0].firstChild.nodeValue);
		}
		
		var contentId = this.idParserCallback(targetTab.id);
        if(!$(contentId)) new Insertion.Top(this.nodes.sectionContainer, '<div id="' + contentId + '" class="tabPageContainer"></div>');		

        var self = this;
        $A(this.nodes.tabListElements).each(function(tab) {
			Element.removeClassName(tab, 'active');
			Element.addClassName(tab, 'inactive');
        });
        
        document.getElementsByClassName("tabPageContainer", this.nodes.sectionContainer).each(function(container) {
            Element.hide(container);
        })
        
		this.activeTab = targetTab;
        this.activeContent = $(contentId);
                
        Element.removeClassName(this.activeTab, 'hover');
		Element.addClassName(this.activeTab, 'active');
		Element.show(contentId);
        
		if (Element.empty($(contentId)))
		{
            new LiveCart.AjaxUpdater(this.urlParserCallback(targetTab.down('a').href), contentId, targetTab.down('.tabIndicator'), 'bottom',  onComplete);
		}
        else if(onComplete)
        {
            onComplete();
        }
       
        this.addHistory();
	},

	/**
	 * Reset content related to a given tab. When tab will be activated content must
	 * be resent
	 */
	resetContent: function(tabObj)
	{
		$($(tabObj).id + 'Content').update();
	},

	reloadActiveTab: function()
	{
		this.resetContent(this.activeTab);
		this.activateTab(this.activeTab);
	},

	getActiveTab: function()
	{
		return this.activeTab;
	},

	setTabUrl: function(tabId, url)
	{
		$(tabId).url = url;
	},
    
    setCounter: function(tab, value, hashId)
    {
        if(!this.countersCache[hashId]) this.countersCache[hashId] = {};
        
        tab = $(tab);
        
        if(!tab) throw new Error('Could not find tab!');
        
        var counter = tab.down('.tabCounter');
        if(false === value)
        {
            counter.update('');
            delete this.countersCache[hashId][tab.id];
        }
        else
        {
            counter.update("(" + value + ")");
            this.countersCache[hashId][tab.id] = value;
        }
    },
        
    setAllCounters: function(counters, hashId)
    {     
        var self = this;
        $H(counters).each(function(tab) {
            self.setCounter(tab[0], tab[1], hashId);
        });
    },
    
    restoreCounter: function(tab, hashId)
    {
        tab = $(tab);

        if(tab && this.countersCache[hashId][tab.id])
        {
            this.setCounter(tab.id, this.countersCache[hashId][tab.id]);
            return true;
        }
        
        return false;
    },
    
    restoreAllCounters: function(hashId)
    {
        var self = this;
        var restored = false;
        if(this.countersCache[hashId])
        {
            $A(this.nodes.tabListElements).each(function(tab) {
                restored = self.restoreCounter(tab, hashId) ? true : restored;    
            });
        }
        
        return restored;  
    },
    
    getCounter: function(tab)
    {
        tab = $(tab);
        
        if(!tab) throw new Error('Could not find tab!');
        
        var counter = tab.down('.tabCounter');      
        var match = counter.innerHTML.match(/\((\d+)\)/);
        return match ? parseInt(match[1]) : 0;
    }
}