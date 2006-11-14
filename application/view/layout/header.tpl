<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<title>{$TITLE}</title>
	<base href="{$BASE_URL}" /> 
	
	<!-- Css includes -->
	{includeCss file=base.css}	
	{$STYLESHEET}
	
	<!-- JavaScript includes -->
	{includeJs file=backend/keyboard.js}
	{includeJs file=json.js}
	{$JAVASCRIPT} 

	<script type="text/javascript">
		window.onload = startList;
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