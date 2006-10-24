<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<base href="{$BASE_URL}" />
	
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>{$TITLE}</title>
	<!-- Css includes -->
	{includeCss file=base.css}
	{$STYLESHEET}
	
	<!-- JavaScript includes -->
	{$JAVASCRIPT}
	
</head>
<body style="">

<div id="pageTop">
	<div id="topAuthInfo">
		Logged in as: <span id="headerUserName">rinalds</span> <a href="/logout">(logout)</a>
	</div>
	<table id="headerContainer">
		<tr>
			<td id="headerShade">
				<table width="100%">
					<tr>	
						<td id="homeButtonWrapper">
							<img src="image/backend/header_home_button.png" align="absmiddle"> Home
						</td>
						<td style="text-align:left;">
							{foreach from=$topList item=item}
							 &nbsp; &nbsp; <a href="{link controller=$item.controller action=$item.action}">{translate text=$item.title}</a>
							{/foreach}
						</td>
					</tr>
					<tr>
						<td colspan="2">
							{foreach from=$MENU item=block}{$block}{/foreach}						
						</td>
				</table>				
			</td>
			<td id="headerShadeStretch">
				<table width="100%">
					<tr>
						<td id="systemMenu">History | Bookmarks | Help | Change Language</td>
					</tr>
					<tr>
						<td id="logo">
							<img src="image/backend/logo.png">		
						</td>
					</tr>
				</table>
			</td>
		</tr>	
	</table>
</div>

<div id="topBorder"></div>

<div id="workAreaContainer">

	<div id="pageTitle">{$PAGE_TITLE}</div>

	<div id="pageTitle">{$TITLE}</div>

	<table style="width: 100%;">
		<tr>
			<td>	
				<div id="workArea">