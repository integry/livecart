var TabControll = Class.create();

TabControll.prototype = {
	
	activeTab: null,
	indicatorImageName: null,
	
	initialize: function(tabContainerName, sectionContainerName, indicatorImageName) 
	{
		if (indicatorImageName != undefined)
		{
			this.indicatorImageName = indicatorImageName;
		}
		
		var tabList = document.getElementsByClassName("tab");
		for (var i = 0; i < tabList.length; i++)
		{
			tabList[i].onclick = this.handleTabClick.bindAsEventListener(this);
			tabList[i].onmouseover = this.handleTabMouseOver.bindAsEventListener(this);
			tabList[i].onmouseout = this.handleTabMouseOut.bindAsEventListener(this);

			aElementList = tabList[i].getElementsByTagName('a');
			if (aElementList.length > 0)
			{
				// Getting an URL that tab is pointing to by analysing "A" element
				tabList[i].url = aElementList[0].href;
				new Insertion.After(aElementList[0], aElementList[0].innerHTML);
				// inserting indicator element which will be show on tab activation
				new Insertion.Before(aElementList[0], '<img src="' + this.indicatorImageName + '" class="tabIndicator" id="' + tabList[i].id + 'Indicator" alt="Tab indicator" style="display:none"/> ');
				Element.remove(aElementList[0]);
			}
			
			if (tabList[i].id == '')
			{
				tabList[i].id = 'tab' + i;
			}
			if (Element.hasClassName(tabList[i], 'active'))
			{
				this.activeTab = tabList[i];
				Element.show(tabList[i].id + 'Content');
			}
			else
			{
				Element.hide(tabList[i].id + 'Content');
			}
		}
	},

	handleTabMouseOver: function(evt) 
	{	
		if (this.activeTab != evt.target)
		{
			Element.removeClassName(evt.target, 'inactive');
			Element.addClassName(evt.target, 'hover');
		}
	},

	handleTabMouseOut: function(evt) 
	{
		if (this.activeTab != evt.target)
		{
			Element.removeClassName(evt.target, 'hover');
			Element.addClassName(evt.target, 'inactive');
		}
	},
	
	handleTabClick: function(evt) 
	{
		var targetTab = evt.target;
		this.activateTab(targetTab);
	},
	
	activateTab: function(targetTab)
	{
		if (this.activeTab == targetTab && !Element.empty(contentId)) 
		{
			return;
		}
		
		Element.removeClassName(this.activeTab, 'active');
		Element.addClassName(this.activeTab, 'inactive');
		Element.hide(this.activeTab.id + 'Content');
			
		this.activeTab = targetTab;
		Element.removeClassName(targetTab, 'hover');
		Element.addClassName(this.activeTab, 'active');			
		Element.show(this.activeTab.id + 'Content');
			
		var indicatorId = this.activeTab.id + 'Indicator';
		var contentId = this.activeTab.id + 'Content'
		
		if (Element.empty(contentId))
		{
			new LiveCart.AjaxUpdater(targetTab.url, contentId, indicatorId);
		}
	},
	
	/**
	 * Reset content related to a given tab. When tab will be activated content must 
	 * be resent
	 */
	resetContent: function(tabObj)
	{
		tabObj.innerHTML = '';
	}
}