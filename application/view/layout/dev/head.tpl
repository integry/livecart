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
	<script type="text/javascript">
	function onLoad()
	{
		Backend.locale = '{/literal}{localeCode}{literal}';
		Backend.onLoad();
	}
	window.onload = onLoad;
	</script>
	{/literal}

</head>
<body>

<div style="padding: 20px;">