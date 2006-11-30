var SectionExpander = Class.create();

SectionExpander.prototype = {

	initialize: function()
	{
		alert('SectionExpander constructor');
		var sectionList = document.getElementsByClassName('expandingSection');
		for (var i = 0; i < sectionList.length; i++)
		{
			var legendList = sectionList[i].getElementsByTagName('legend');
			if (legendList[0] != undefined)
			{
				var legend = legendList[0];
				legend.innerHTML = "[+] " + legend.innerHTML;
				legend.onclick = this.handleLegendClick.bindAsEventListener(this);
			}
		}
	},

	handleLegendClick: function(evt)
	{

	}
}