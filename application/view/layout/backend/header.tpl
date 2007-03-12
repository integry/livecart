<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>{$TITLE}</title>
	<base href="{baseUrl}" />

	<!-- Css includes -->
	<link href="stylesheet/backend/Backend.css" media="screen" rel="Stylesheet" type="text/css"/>
	{includeCss file="backend/stat.css"}

	{$STYLESHEET}
	{literal}
	<!--[if IE]>
		<link href="stylesheet/backend/BackendIE.css" media="screen" rel="Stylesheet" type="text/css"/>
	<![endif]-->
	<!--[if IE 6]>
		<link href="stylesheet/backend/BackendIE6.css" media="screen" rel="Stylesheet" type="text/css"/>
	<![endif]-->
	<!--[if IE 7]>
		<link href="stylesheet/backend/BackendIE7.css" media="screen" rel="Stylesheet" type="text/css"/>
	<![endif]-->
	{/literal}

	<script type="text/javascript" src="javascript/library/tinymce/tiny_mce.js"></script>

	<script type="text/javascript" src="firebug/firebug.js"></script>
	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>
	<script type="text/javascript" src="javascript/library/scriptaculous/scriptaculous.js"></script>
	<script type="text/javascript" src="javascript/backend/Backend.js"></script>

	<!-- JavaScript includes -->
	{includeJs file=library/KeyboardEvent.js}
	{includeJs file=library/json.js}
	{includeJs file=library/livecart.js}
	{includeJs file=library/Debug.js}
	{includeJs file=library/dhtmlHistory/dhtmlHistory.js}
	
	{includeJs file=backend/Customize.js}
    
	{$JAVASCRIPT}

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

<div id="log"></div>

<div id="topShadeContainer">
	<div id="topShadeContainerLeft"></div>
	<div id="topShadeContainerRight" style="background-image:url(image/backend/layout/workarea_shade_vertical_wide.jpg);">
		<div></div>
	</div>
</div>

<div id="pageContainer">

	<div id="pageHeader">

		<div id="topAuthInfo">
			Logged in as: <span id="headerUserName">rinalds</span> <a href="/logout">(logout)</a>
		</div>

		<div id="topBackground" >
			<div id="topBackgroundLeft" >

				<div style="float: left;">
					<div id="homeButtonWrapper">
						<a href="{link controller=backend.index action=index}">
							<img src="image/backend/layout/top_home_button.jpg" id="homeButton">
						</a>
					</div>

					<div id="navContainer">
						<div id="nav"></div>
						{backendMenu}
					</div>
				</div>

				<div id="topLogoContainer">

					<div id="systemMenu">
							<a id="help" href="#" target="_blank">{t _base_help}</a> | <a href="#" onClick="showLangMenu(true);return false;">{t _change_language}</a>
							{backendLangMenu}
					</div>

					<div id="topLogoImageContainer">
					 	<a href="{link controller=backend.index action=index}">
						 	<img src="image/backend/layout/logo_tr.png" id="topLogoImage">
						</a>
					</div>

				</div>

			</div>

		</div>

		<div id="pageTitleContainer">
			<div id="pageTitle">{$PAGE_TITLE}</div>
			<div id="breadcrumb_template" class="dom_template">
				<span id="breadcrumb_item"><a href=""></a></span>
				<span id="breadcrumb_separator"> &gt; </span>
				<span id="breadcrumb_lastItem"></span>
			</div>
			<div id="breadcrumb"></div>
		</div>

	</div>

	<div id="pageContentContainer">

		<div id="pageContentInnerContainer" class="maxHeight h--20"  >
