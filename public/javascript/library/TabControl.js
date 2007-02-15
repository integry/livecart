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
    __instance__: null,
    
	activeTab: null,
	indicatorImageName: "image/indicator.gif",

	initialize: function(tabContainerName, urlParserCallback)
	{
        try
        {  
            this.tabContainerName = tabContainerName;
            this.urlParserCallback = urlParserCallback;
            console.info(urlParserCallback);
            
            this.__nodes__();
            this.__bind__();
            this.__init__();
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
            var indicator = '<img src="' + self.indicatorImageName + '" class="tabIndicator hidden" alt="Tab indicator" /> ';
            
            Event.observe(link, 'click', function(e) { if(e) Event.stop(e); });
            Event.observe(li, 'click', function(e) { 
                if(e) Event.stop(e); 
                self.handleTabClick({'target': li}) 
            });
			Event.observe(li, 'mouseover', function(e) { 
                if(e) Event.stop(e); 
                self.handleTabMouseOver({'target': li}) 
            });
			Event.observe(li, 'mouseout', function(e) { 
                if(e) Event.stop(e); 
                self.handleTabMouseOut({'target': li}) 
            });
            
            li.update(indicator + li.innerHTML);

			if (Element.hasClassName(li, 'active')) self.activeTab = li;
		});
    },
    
    __init__: function()
    {
        Event.fire(this.activeTab, 'click');
    },
    
    getInstance: function(tabContainerName, urlParserCallback)
    {
        if(!TabControl.prototype.__instance__)
        {
            TabControl.prototype.__instance__ = new TabControl(tabContainerName, urlParserCallback);
        }
        
        return TabControl.prototype.__instance__;
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
        this.activateTab(args.target);
	},

	activateTab: function(targetTab)
	{
        var contentId = targetTab.id + '_' + Backend.Category.treeBrowser.getSelectedItemId() + 'Content';
        if(!$(contentId)) new Insertion.Top(this.nodes.sectionContainer, '<div id="' + contentId + '"></div>');
        else if (this.activeTab == targetTab && !Element.empty(contentId)) 
        {
             console.info(this.activeTab + " == " + targetTab + " && !Element.empty(" + contentId + ")["+!Element.empty(contentId)+"]");
             return;   
        }

		if(this.activeTab)
		{
			Element.removeClassName(this.activeTab, 'active');
			Element.addClassName(this.activeTab, 'inactive');
			Element.hide(this.activeTab.id + '_' + Backend.Category.treeBrowser.getSelectedItemId() + 'Content');
		}

		this.activeTab = targetTab;
        
        Element.removeClassName(this.activeTab, 'hover');
		Element.addClassName(this.activeTab, 'active');
		Element.show(contentId);   
        
		if (Element.empty($(contentId)))
		{
            new LiveCart.AjaxUpdater(this.urlParserCallback(targetTab.down('a').href), contentId, targetTab.down('.tabIndicator'));
		}
        else
        {
            console.info("Element.empty($(" + $(contentId).id + ")");
        }
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
	}
}