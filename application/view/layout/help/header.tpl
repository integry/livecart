<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>LiveCart Help</title>
	<base href="{$BASE_URL}" />

	<!-- Css includes -->
	<link href="stylesheet/backend/Backend.css" media="screen" rel="Stylesheet" type="text/css"/>	
	{$STYLESHEET}
	{literal}
	<!--[if IE]>
		<link href="stylesheet/backend/BackendIE.css" media="screen" rel="Stylesheet" type="text/css"/>	
	<![endif]-->
	{/literal}

	<script type="text/javascript" src="javascript/library/prototype/prototype.js"></script>

	<!-- JavaScript includes -->
	{$JAVASCRIPT}

{literal}
<style>
body {
	background-image: none;  
	background-color: #EEEEEE;
}
p {
  	margin-bottom: 10px;
}
ul, ol {
  	margin-left: 20px;
  	margin-bottom: 10px;
}
h1 {
	font-size: largest; 
	font-weight: bold;
	margin: 0px;
	padding-top: 5px;
	padding-left: 5px;
}
h2 {
	margin-bottom: 5px;  
}

#helpHeader {
	padding: 10px; 
	background-color: yellow;
	position: relative;
	height: 60px;
}
#helpTitle {
  	font-size: 20px;
  	font-weight: bold;
  	position: absolute;
  	top: 55px;
  	left: 150px;
}
#helpNav {
  	background-color: white;
  	padding: 4px;
}
#helpContent {
  	padding: 10px;
}
#breadCrumbLast {
  	font-weight: bold;
}

</style>
{/literal}

</head>
<body>
	
	<div id="helpHeader">
		<div style="float: left;">
			<img src="image/backend/layout/logo_tr.png" style="margin-right: 10px;">
			<span id="helpTitle">Help</span>
		</div>
		<div style="float: right; text-align: right;">
			<input type="text" name="search"><br />
			<input type="submit" value="Search">
		</div>
	</div>