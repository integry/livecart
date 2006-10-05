<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link href="/k-shop/public/stylesheet/style.css" media="screen" rel="stylesheet" type="text/css"/>
	<link href="/k-shop/public/stylesheet/treemenu.css" media="screen" rel="stylesheet" type="text/css"/>
	<link href="/k-shop/public/stylesheet/tabs.css" media="screen" rel="stylesheet" type="text/css"/>
	<title>{$TITLE}</title>
	{foreach from=$JAVASCRIPT item=file}
	<script src="{$file}"></script>
	{/foreach}
</head>
<body onload='{foreach from=$BODY_ONLOAD item=item}{$item};{/foreach}'>
{literal}
<script type="text/javascript"> 

</script>
{/literal}