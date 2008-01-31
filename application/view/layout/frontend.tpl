<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<meta name="Keywords" content="{$metaKeywords|escape}" />
	<meta name="Description" content="{$metaDescription|escape}" />

	<title>
		{if $PAGE_TITLE}
			{$PAGE_TITLE|@strip_tags}
		{else}
			{if $breadCrumb}
				{assign var="lastBreadcrumb" value=$breadCrumb|@end}
				{$lastBreadcrumb.title}
			{/if}
		{/if}
		- {'STORE_NAME'|config}
	</title>
	<base href="{baseUrl}"></base>
	{liveCustomization}

	<!-- Css includes -->
	<link href="stylesheet/frontend/Frontend.css" rel="Stylesheet" type="text/css"/>
	<!--[if IE]>
		<link href="stylesheet/frontend/FrontendIE.css" rel="Stylesheet" type="text/css"/>
	<![endif]-->

	{includeCss file="custom/Custom.css"}

	{* {includeCss file="backend/stat.css"} *}
	{compiledCss}

	<!-- JavaScript includes -->
	{* <script type="text/javascript" src="firebug/firebug.js"></script> *}
	{compiledJs}
</head>

<body>
	<div id="container" class="lang_{localeCode}">
		{$ACTION_VIEW}
	</div>
	{liveCustomization action="menu"}
	{block TRACKING}
</body>

</html>