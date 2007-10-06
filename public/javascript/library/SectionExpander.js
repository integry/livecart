/**
 *	@author Integry Systems
 */
 
var SectionExpander = Class.create();

SectionExpander.prototype = 
{
	/**
	 * SectionExpander constructor
	 */
	initialize: function(parent)
	{
        var sectionList = document.getElementsByClassName('expandingSection', $(parent));

		for (var i = 0; i < sectionList.length; i++)
		{
			var legend = sectionList[i].down('legend');
			if (legend)
			{
				legend.onclick = this.handleLegendClick.bindAsEventListener(this);
			}
		}
	},
    
    unexpand: function(parent)
    {
		Element.addClassName($(parent), 'expanded');
    },

	/**
	 * Legend element click handler
	 * Toggles fieldsets content visibility
	 */
	handleLegendClick: function(evt)
	{
		Element.toggleClassName(Event.element(evt).parentNode, 'expanded');		
		tinyMCE.execCommand("mceResetDesignMode");
	}
}