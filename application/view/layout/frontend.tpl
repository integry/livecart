{cache var=url value=$request.route final=true}

<!DOCTYPE html>
<html lang="en">

<head>
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	<meta name="Keywords" content="{$metaKeywords|@meta}" />
	{assign var="defaultMeta" value='DEFAULT_META_DESCR'|config}
	<meta name="Description" content="{$metaDescription|@meta:$defaultMeta}" />
	<meta http-equiv="X-UA-Compatible" content="IE=100" />

	<title>
		{if !$PAGE_TITLE}
			{capture assign="PAGE_TITLE"}
				{block BREADCRUMB_TITLE}
			{/capture}
		{/if}

		{if 'TITLE_FORMAT'|config == 'NAME_FIRST'}
			{'STORE_NAME'|config} {'TITLE_SEPARATOR'|config} {$PAGE_TITLE|@strip_tags}
		{else}
			{$PAGE_TITLE|@strip_tags} {'TITLE_SEPARATOR'|config} {'STORE_NAME'|config}
		{/if}
	</title>

	<base href="{baseUrl}"></base>
	{liveCustomization}

	{if 'FAVICON'|config}
		<link href="{'FAVICON'|config}" rel="shortcut icon" />
	{/if}

	{if !$CANONICAL}
		{canonical}{self}{/canonical}
	{/if}

	<link rel="canonical" href="{$CANONICAL}" />

	<!-- Css includes -->
	{includeCss file="frontend/Frontend.css"}
	{includeCss file="backend/stat.css"}
	{includeCss file="backend/theme/redmond/jquery-ui.css" front=true}

	{if {isRTL}}
		{includeCss file="frontend/FrontendRTL.css"}
	{/if}

	<link href="/livecart/public/bootstrap/bootstrap/css/bootstrap.css" rel="stylesheet">

	{* <link href="http://bootswatch.com/united/bootstrap.min.css" rel="stylesheet"> *}

	{compiledCss glue=true nameMethod=hash}
	<!--[if lt IE 8]>
		<link href="stylesheet/frontend/FrontendIE.css" rel="Stylesheet" type="text/css"/>
		{if $ieCss}
			<link href="{$ieCss}" rel="Stylesheet" type="text/css"/>
		{/if}
	<![endif]-->

	<!-- JavaScript includes -->
	{loadJs form=true}
	{includeJs file="library/jquery/jquery-ui.js"}

	{compiledJs glue=true nameMethod=hash}
	<script src="/livecart/public/bootstrap/bootstrap/js/bootstrap.js"></script>

	{*
	<!--[if lt IE 7]>
		<script src="javascript/library/iepngfix/iepngfix_tilebg.js" type="text/javascript"></script>
	<![endif]-->

	<!--[if lt IE 8]>
		<script src="javascript/library/ie7/IE8.js" type="text/javascript"></script>
	<![endif]-->
	*}
	<script type="text/javascript">
		Router.setUrlTemplate('{link controller=controller action=action}');
	</script>
</head>

<body class="{$request.controller}Con {$request.controller}-{$request.action}{if {isRTL}} rtl{/if}{if $bodyClass} {$bodyClass}{/if}">
	{liveCustomization action="menu"}
	<div id="container" class="lang_{localeCode}">
		<div id="containerWrapper1">
		<div id="containerWrapper2">
		<div id="containerWrapper3">
			<div class="container">
				<div class="row">
					{block PAGE-TOP}
					{$ACTION_VIEW}
					{block PAGE-BOTTOM}
				</div>
			</div>
		</div>
		</div>
		</div>
	</div>
	{block TRACKING}
	{liveCustomization action="lang"}

	{if !'DISABLE_AJAX'|config}
		<script type="text/javascript">
			new Frontend.AjaxInit(document.body);
			{loadJs}
		</script>
	{/if}

{*
		{if !'DISABLE_TOOLBAR'|config}
			{block FOOTER_TOOLBAR}
		{/if}
*}
	</body>
</html>
