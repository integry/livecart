<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>LiveCart DEV</title>
	<!-- Css includes -->
	{$STYLESHEET}
	<!-- JavaScript includes -->
	{$JAVASCRIPT}
</head>
<body style="">

<div id="pageTop">
	<div id="topAuthInfo">
		Logged in as: <span id="headerUserName">rinalds</span> <a href="/logout">(logout)</a>
	</div>
	<table width="100%" style="height: 44px;">
		<tr>
			<td id="headerShade" style="background-image:url(/livecart/public/image/backend/header_shade.png);">
				<table width="100%">
					<tr>	
						<td id="homeButtonWrapper" style="background-image:url(/livecart/public/image/backend/header_home_button_wrapper.png);">
							<img src="/livecart/public/image/backend/header_home_button.png" align="absmiddle"> Home
						</td>
						<td>
						&nbsp;
						</td>
					</tr>
				</table>				
			</td>
			<td style="background-image:url({$ROOT}/image/backend/header_shade_stretch.png);">
				<table width="100%">
					<tr>
						<td id="systemMenu">History | Bookmarks | Help | Change Language</td>
					</tr>
					<tr>
						<td id="logo">
							<img src="/livecart/public/image/backend/logo.png">		
						</td>
					</tr>
				</table>
			</td>
		</tr>	
	</table>
</div>

<div id="topBorder"></div>

<div id="workAreaContainer">

{$MENU}

<!-- <div id="container"> -->

	<div id="pageTitle">{$PAGE_TITLE}Temporary Page Title</div>

	<table style="width: 100%;">
		<tr>
			<td>	
				<div id="workArea">

<!--
<div id="menu">
	<ul>
		<li>Product categories</li>
		<li>Products</li>
		<li>Orders</li>
	</ul>
	<div style="clear: both"></div>
</div>
-->