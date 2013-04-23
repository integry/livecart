<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>{'SOFT_NAME'|config} Admin - {$TITLE}</title>
	<base href="{baseUrl}" />

	{if 'FAVICON'|config}
		<link href="{'FAVICON'|config}" rel="shortcut icon" />
	{/if}

	<!-- Css includes -->
	{includeCss file="../javascript/library/tinymce/themes/advanced/css/editor_ui.css" front=true}
	{includeCss file="backend/stat.css" front=true}
	{includeCss file="backend/Backend.css" front=true}
	{includeCss file="backend/theme/redmond/jquery-ui.css" front=true}
	{includeCss file="library/jquery/jquery-plugins.css" front=true}
	{compiledCss glue=true nameMethod=hash}

	<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css" rel="stylesheet">

	{if !'DISABLE_WYSIWYG'|config}
		{includeJs file="library/tinymce/tiny_mce.js" inline=true}
	{/if}

	{includeJs file="library/KeyboardEvent.js" front=true}
	{includeJs file="backend/Backend.js" front=true}
	{includeJs file="backend/QuickSearch.js"}
	{includeJs file="library/FooterToolbar.js" front=true}
	{includeJs file="library/livecart.js" front=true}
	{includeJs file="library/dhtmlHistory/dhtmlHistory.js"}
	{includeJs file="library/scriptaculous/dragdrop.js" front=true}
	{includeJs file="library/scriptaculous/slider.js" front=true}
	{includeJs file="library/scriptaculous/controls.js" front=true}
	{includeJs file="library/scriptaculous/builder.js" front=true}
	{includeJs file="library/jquery/plugins.js" front=true}
	{includeJs file="library/jquery/jquery-ui.js" front=true}
	{includeJs file="library/jquery/jquery-min.js" front=true}
	{includeJs file="library/prototype/prototype.js" front=true}

	{includeJs file="backend/BackendToolbar.js"}

	{compiledJs glue=true nameMethod=hash}
	<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/js/bootstrap.min.js"></script>

	{literal}
	<script language="javascript" type="text/javascript">
	if(window.opener)
	{
	   try
	   {
			window.opener.selectPopupWindow = window;
	   }
	   catch (e)
	   {
			window.opener = null;
			// Permission denied to set property Window.selectPopupWindow
	   }
	}

	if (window.tinyMCE)
	{
		tinyMCE.init({
			theme : "advanced",
			mode : "exact",
			plugins: "table,contextmenu,paste",
			paste_insert_word_content_callback : "convertWord",
			paste_auto_cleanup_on_paste : true,
			elements : "",
			auto_reset_designmode : true,
			theme_advanced_resizing_use_cookie : false,
			theme_advanced_toolbar_location : "top",
			theme_advanced_resizing : true,
			theme_advanced_path_location : "bottom",
			document_base_url : "{/literal}{baseUrl}{literal}",
			remove_script_host : "true",
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,fontselect,fontsizeselect,formatselect,separator,forecolor,backcolor",
			theme_advanced_buttons2 : "bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,anchor,image,cleanup,separator,code,separator,table,separator,sub,sup,separator,charmap",
			theme_advanced_buttons3 : "",
			content_css: "{/literal}{baseUrl}{literal}stylesheet/library/TinyMCE.css",
			forced_root_block : '',
			relative_urls : true,
			remove_linebreaks : false,
			extended_valid_elements : 'iframe[src|width|height|name|align|frameborder|scrolling|marginheight|marginwidth],embed[width|height|name|flashvars|src|bgcolor|align|play|loop|quality|allowscriptaccess|type|pluginspage]',
			entities: '',
			file_browser_callback : "ajaxfilemanager"
			{/literal}
			{if 'TINYMCE_PARAMS'|config}
			, {'TINYMCE_PARAMS'|config}
			{/if}
			{literal}
		});
	}

	if (window.jscolor)
	{
		window.jscolor.dir = 'javascript/library/jscolor/';
	}

	function onLoad()
	{
		Backend.locale = '{/literal}{localeCode}{literal}';
		Backend.onLoad();
	}

	function ajaxfilemanager(field_name, url, type, win)
	{
		var

			ajaxfilemanagerurl = tinyMCE.baseURI.getURI()+"/../ajaxfilemanager/ajaxfilemanager.php?editor=tinymce";

		switch (type)
		{
			case "image":
			case "media":
			case "flash":
			case "file":
				break;
			default:
				return false;
		}
		tinyMCE.activeEditor.windowManager.open(
			{
                url: ajaxfilemanagerurl,
                width: 782,
                height: 440,
                inline : "yes",
                close_previous : "yes"
            }
            ,
            {
				window : win,
				input : field_name
			}
		);
	}
	{/literal}

	window.onload = onLoad;

	Router.setUrlTemplate('{link controller=controller action=action}');
	</script>

	{block TRANSLATIONS}

</head>
<body class="{$request.controller|replace:'.':'-'}Con {$request.controller|replace:'.':'-'}-{$request.action} ui-widget">
<script type="text/javascript">
{literal}
	window.historyStorage.init();
	window.dhtmlHistory.create();
{/literal}
</script>

<!-- Preload images -->
{img src="image/silk/bullet_arrow_up.png" style="display: none" id="bullet_arrow_up"}
{img src="image/silk/bullet_arrow_down.png" style="display: none" id="bullet_arrow_down"}
