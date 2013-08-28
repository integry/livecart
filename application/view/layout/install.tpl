<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

	<title>
		LiveCart Installer
		{* $PAGE_TITLE *}
	</title>
	<base href="{baseUrl}" />

	<!-- Css includes -->
	<link href="stylesheet/install/Install.css" rel="Stylesheet" type="text/css"/>

	{literal}
	<!--[if IE]>
		<link href="stylesheet/frontend/InstallIE.css" rel="Stylesheet" type="text/css"/>
		<style>
			#header
			{
				padding-bottom: 0;
			}
			#form
			{
				width: 430px;
			}
		</style>
	<![endif]-->
	{/literal}

	{includeJs file="library/prototype/prototype.js"}
	{includeJs file="library/livecart.js"}
	{includeJs file="library/form/Validator.js"}
	{includeJs file="library/form/ActiveForm.js"}
	{compiledJs glue=false}

</head>

<body>
	<div id="container" class="lang_{localeCode} action_[[request.action]]">
		<div id="header">
			<span id="title" style="float: left">LiveCart Installer</span>
			<ul id="installProgress">
				<li id="progressRequirements">Requirements</li>
				<li id="progressLicense">License</li>
				<li id="progressDatabase">Database</li>
				<li id="progressAdmin">Admin</li>
				<li id="progressConfig">Config</li>
				<li id="progressFinish">Finish</li>
			</ul>
			<div class="clear"></div>
		</div>
		<div id="installContent" class="action_[[request.action]]">
			[[ACTION_VIEW]]
			<div class="clear"></div>
		</div>
		<div id="installFooter">
		  &copy; <a href="http://livecart.com" target="_blank">UAB Integry Systems</a>, 2007-2013
		</div>
	</div>
</body>

</html>
