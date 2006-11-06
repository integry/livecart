var TabControll = Class.create();

TabControll.prototype = {
	
	activeTab: null,
	
	initialize: function(tabContainerName, sectionContainerName) {
		var tabList = document.getElementsByClassName("tab");
		for (var i = 0; i < tabList.length; i++)
		{
			//tabList[i].onclick = this.handleTabClick.bindAsEventListener(this);
			//tabList[i].onmouseover = this.handleTabMouseOver.bindAsEventListener(this);
			//tabList[i].onmouseout = this.handleTabMouseOut.bindAsEventListener(this);
			tabList[i].tabControll = this;
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
			
			Element.observe(tabList[i], 'click', this.handleTabClick);
			Element.observe(tabList[i], 'mouseover', this.handleTabMouseOver);
			Element.observe(tabList[i], 'mouseout', this.handleTabMouseOut);
		}
	},

	handleTabMouseOver: function(evt) {
		
		if (this.tabControll.activeTab != evt.target)
		{
			Element.removeClassName(evt.target, 'inactive');
			Element.addClassName(evt.target, 'hover');
		}
	},

	handleTabMouseOut: function(evt) {
		if (this.tabControll.activeTab != evt.target)
		{
			Element.removeClassName(evt.target, 'hover');
			Element.addClassName(evt.target, 'inactive');
		}
	},
	
	handleTabClick: function(evt) {

		if (this.tabControll.activeTab != evt.target) 
		{
			Element.removeClassName(this.tabControll.activeTab, 'active');
			Element.addClassName(this.tabControll.activeTab, 'inactive');
			Element.hide(this.tabControll.activeTab.id + 'Content');
			
			this.tabControll.activeTab = evt.target;
			Element.removeClassName(evt.target, 'hover');
			Element.addClassName(this.tabControll.activeTab, 'active');
			Element.show(this.tabControll.activeTab.id + 'Content');
		}
	}
}