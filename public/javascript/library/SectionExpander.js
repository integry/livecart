var SectionExpander = Class.create();

SectionExpander.prototype = {

	expandingFieldsetClassName: 'expandingSection',
	expandingContentClassName: 'expandingSectionContent',
	expandIconClassName: 'expandIcon',

	/**
	 * SectionExpander constructor
	 */
	initialize: function(parent)
	{
		var sectionList = document.getElementsByClassName('expandingSection', $(parent));
		for (var i = 0; i < sectionList.length; i++)
		{
			var legend = sectionList[i].down('legend');
			var expandIcon = sectionList[i].down('.expandIcon');
			if (legend && !expandIcon)
			{
				legend.innerHTML = '<span class="expandIcon">' + this.getToggleIconContent(false) + '</span> ' + legend.innerHTML;
				legend.onclick = this.handleLegendClick.bindAsEventListener(this);
			}
		}
		var sectionContentList = document.getElementsByClassName('expandingSectionContent', $(parent));
		for (var i = 0; i < sectionContentList.length; i++)
		{
			Element.hide(sectionContentList[i]);
		}
	},
    
    unexpand: function(parent)
    {
		var sectionContentList = document.getElementsByClassName('expandingSectionContent', $(parent));
		for (var i = 0; i < sectionContentList.length; i++)
		{
			Element.show(sectionContentList[i]);
		}
    },

	/**
	 * Legend element click handler
	 * Toggles fieldsets content visibility
	 */
	handleLegendClick: function(evt)
	{
        if(!evt || !evt.target) 
        { 
            evt = window.event; 
            evt.target = evt.srcElement;
        }
                
		var fieldset = evt.target.parentNode;
		var content = document.getElementsByClassName(this.expandingContentClassName, fieldset);
		if (content[0] != undefined)
		{
			Element.toggle(content[0]);
			var expandIcon = document.getElementsByClassName('expandIcon', fieldset);
			if (expandIcon[0] != undefined)
			{
				if (Element.visible(content[0]))
				{
					isOpened = true;
				}
				else
				{
					isOpened = false;
				}
				expandIcon[0].innerHTML = this.getToggleIconContent(isOpened);
			}

		}
		
		tinyMCE.execCommand("mceResetDesignMode");
	},

	getToggleIconContent: function(isSectionOpened)
	{
		if (isSectionOpened)
		{
			return '[-]';
		}
		else
		{
			return '[+]';
		}
	}


}