<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>LiveCart Help</title>
	<base href="{baseUrl}" />

	<!-- Css includes -->
	<link href="stylesheet/backend/Backend.css" media="screen" rel="Stylesheet" type="text/css"/>
	{includeCss file="help/Help.css"}
	{$STYLESHEET}
	{literal}
	<!--[if IE]>
		<link href="stylesheet/backend/BackendIE.css" media="screen" rel="Stylesheet" type="text/css"/>
	<![endif]-->
	{/literal}

	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>

	<!-- JavaScript includes -->
	{$JAVASCRIPT}
</head>
<body>
	<div id="helpContainer">

		<div id="helpHeader">
			<div style="float: left;">
				<a href="{help /index}">
					<img src="image/backend/layout/logo_tr.png" style="margin-right: 10px;">
				</a>
				<span id="helpTitle">Help</span>
			</div>
			<div style="float: right; text-align: right;">
				<input type="text" name="search" style="width: 160px;">
				<input type="submit" class="submit" value="Search">
			</div>
		</div>

		<div id="helpContentContainer">