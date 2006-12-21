<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>{$TITLE}</title>
	<base href="{$BASE_URL}" />

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
	{/literal}

	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>
	<script type="text/javascript" src="javascript/library/scriptaculous/scriptaculous.js"></script>
	<script type="text/javascript" src="javascript/backend/Backend.js"></script>

	<!-- JavaScript includes -->
	{includeJs file=library/KeyboardEvent.js}
	{includeJs file=library/json.js}
	{includeJs file=library/livecart.js}
	{includeJs file=library/Debug.js}
	
	{includeJs file=backend/Customize.js}

	{$JAVASCRIPT}
</head>
<body>

<div id="log" style="position: absolute; top: 0; left: 0; z-index: 10000; background-color: white;"></div>

<div style="position: absolute; width: 100%; height:100%; background-color: white;">
	<div style="float: left; width: 15px; height: 100%; background-color: #E6E6E6;"></div>
	<div style="float: right; width: 20px; height: 100%; background-image:url(image/backend/layout/workarea_shade_vertical_wide.jpg);
	background-position: -8px 0px; background-repeat: repeat-y;  background-color: #E6E6E6;">
		<div style="height:110px; width: 20px; background-color: #E6E6E6;"></div>
	</div>
</div>

<div id="pageContainer">
	
	<div id="pageHeader" style="height: 99px;">
				
		<div id="topAuthInfo">
			Logged in as: <span id="headerUserName">rinalds</span> <a href="/logout">(logout)</a>
		</div>
	
		<div id="topBackground" style="height: 60px;">
			<div id="topBackgroundLeft" style="width: 100%;">
				
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
							{t _base_help} | <a href="#" onClick="showLangMenu(true);return false;">{t _change_language}</a>
							{backendLangMenu}								
					</div>	

					<div style="float: right;">
					 	<a href="{link controller=backend.index action=index}">
						 	<img src="image/backend/layout/logo_tr.png" id="topLogoImage">
						</a>
					</div>									

				</div>			
				
			</div>					 	 						
			
		</div>
	
		<div id="pageTitleContainer">
			<div id="pageTitle" style="float: left;">{$PAGE_TITLE}</div>
			<div id="breadcrumb_template" class="dom_template">
				<span id="breadcrumb_item"><a href=""></a></span>
				<span id="breadcrumb_separator"> &gt; </span>
				<span id="breadcrumb_lastItem"></span>								
			</div>
			<div id="breadcrumb"></div>
		</div>
		
	</div>
		
	<div id="pageContentContainer" style="position: relative;">
			
		<div style="background-color: white; margin-left:15px; margin-right:20px; padding: 10px;" class="maxHeight h--20">	
	