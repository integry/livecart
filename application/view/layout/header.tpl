<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>{$TITLE}</title>
	<base href="{$BASE_URL}" />

	<!-- Css includes -->
	{includeCss file=backend/Backend.css}
	{includeCss file="backend/stat.css"}
	{$STYLESHEET}
	{literal}
	<!--[if IE]>
		<link href="stylesheet/backend/BackendIE.css" media="screen" rel="Stylesheet" type="text/css"/>
	<![endif]-->
	{/literal}

	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>

	<!-- JavaScript includes -->
	{includeJs file=library/KeyboardEvent.js}
	{includeJs file=library/json.js}
	{includeJs file=backend/Backend.js}
	
	{$JAVASCRIPT}

	<script type="text/javascript">
		window.onload = initializeNavigationMenu;
	</script>
</head>
<body>

<div id="log" style="position: absolute; top: 0; left: 0; z-index: 10000; background-color: white;"></div>
{literal}
<script>
	function addlog(info)
	{
		document.getElementById('log').innerHTML += info + '<br />';
	}
</script>
{/literal}


<div id="minHeight"></div>
<div id="outer" style="">

	<div id="clearheader"></div>

	<div id="left">
	</div>

	<div id="right">
		<div id="rightWorkareaShadeTop">&nbsp;</div>
	</div>

	<div id="centrecontent">
		<div id="workArea">