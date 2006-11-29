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
}
p {
  	margin-bottom: 10px;
}
#helpHeader {
	padding: 10px; 
	background-color: yellow;
	position: relative;
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
</style>
{/literal}

</head>
<body>
	
	<div id="helpHeader">
		<img src="image/backend/layout/logo_tr.png" style="margin-right: 10px;">
		<span id="helpTitle">Help</span>
	</div>
	
	<div>
		{$ACTION_VIEW}
	</div>
	
</body>
</html>