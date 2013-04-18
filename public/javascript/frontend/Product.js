jQuery(function()
{
	var tabs = jQuery('#productTabs');
	if (tabs.length)
	{
		jQuery('#productContent').addClass('tab-content');
		
		jQuery('#productContent .productSection').each(function()
		{
			var section = jQuery(this);
			var title = section.find('h2');
			var displayTitle = title.find('small').html() || title.html();
			var id = section.attr('id');
			
			tabs.append(jQuery('<li><a id="tab_' + id + '" href="#' + id +'">' + displayTitle + '</a></li>'));
			section.addClass('tab-pane');
		});
		
		tabs.find('a').tab().click(function (e) { e.preventDefault(); jQuery(this).tab('show');}).first().tab('show');
	}
});
