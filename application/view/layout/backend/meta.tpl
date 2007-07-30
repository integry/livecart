<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>LiveCart Admin - {$TITLE}</title>
	<base href="{baseUrl}" />

	{liveCustomization}
	
	<!-- Css includes -->
    {includeCss file="backend/stat.css" inline=true}
    {includeCss file="backend/Backend.css" inline=true}
	{compiledCss glue=true}
	
    {includeJs file=library/tinymce/tiny_mce_src.js inline=true} 
    
    {includeJs file=library/KeyboardEvent.js front=true}
    {includeJs file=library/livecart.js front=true}
    {includeJs file="backend/Backend.js" front=true}
    
    {includeJs file=library/Debug.js}
    {includeJs file=library/dhtmlHistory/dhtmlHistory.js}
    
    {includeJs file="library/scriptaculous/dragdrop.js" front=true}
    {includeJs file="library/scriptaculous/slider.js" front=true}
    {includeJs file="library/scriptaculous/controls.js" front=true}
    {includeJs file="library/scriptaculous/builder.js" front=true}
    {includeJs file="library/scriptaculous/effects.js" front=true}
    {includeJs file="library/prototype/prototype.js" front=true}
	
	{compiledJs glue=true}

	{literal}
	<script language="javascript" type="text/javascript">
    try
    {
        tinyMCE.init({
			theme : "advanced",
			mode : "exact",
			elements : "",
			auto_reset_designmode : true,
			theme_advanced_resizing_use_cookie : false,
			theme_advanced_toolbar_location : "top",
			theme_advanced_resizing : true,
			theme_advanced_path_location : "bottom",
			document_base_url : "{/literal}{baseUrl}{literal}",
			remove_script_host : "true",
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,styleselect,formatselect",
			theme_advanced_buttons2 : "bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,anchor,image,cleanup,separator,code,removeformat,visualaid,separator,sub,sup,separator,charmap",
			theme_advanced_buttons3 : "",
			relative_urls : true
        });

    	function onLoad()
    	{
    		Backend.locale = '{/literal}{localeCode}{literal}';
    		Backend.onLoad();
    	}
        
    	window.onload = onLoad;
    } 
    catch(e)
    {
        console.info(e);
    }
	</script>
	{/literal}

</head>
<body>
<script type="text/javascript">
{literal}
    try
    {
        /** Initialize all of our objects now. */
        window.historyStorage.init();
        window.dhtmlHistory.create();
    }
    catch(e)
    {
        console.info(e);
    }
{/literal}
</script>