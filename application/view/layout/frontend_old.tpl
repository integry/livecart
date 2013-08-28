<!DOCTYPE html>
<html lang="en">

<head>

	<meta name="Keywords" content="{$metaKeywords|@meta}" />
	{assign var="defaultMeta" value='DEFAULT_META_DESCR'|config}
	<meta name="Description" content="{$metaDescription|@meta:$defaultMeta}" />


	<title>
		{if !$PAGE_TITLE}
			{capture assign="PAGE_TITLE"}
				{block BREADCRUMB_TITLE}
			{/capture}
		{/if}

		{if 'TITLE_FORMAT'|config == 'NAME_FIRST'}
			[[ config('STORE_NAME') ]] [[ config('TITLE_SEPARATOR') ]] {$PAGE_TITLE|@strip_tags}
		{else}
			{$PAGE_TITLE|@strip_tags} [[ config('TITLE_SEPARATOR') ]] [[ config('STORE_NAME') ]]
		{/if}
	</title>


	{liveCustomization}

	{if 'FAVICON'|config}
		<link href="[[ config('FAVICON') ]]" rel="shortcut icon" />
	{/if}

	{if !$CANONICAL}
		{canonical}{self}{/canonical}
	{/if}

	<link rel="canonical" href="[[CANONICAL]]" />

	<!-- Css includes -->
	{includeCss file="frontend/Frontend.css"}
	{includeCss file="backend/stat.css"}

	{if {isRTL}}
		{includeCss file="frontend/FrontendRTL.css"}
	{/if}

	<link href="bootstrap/bootstrap/css/bootstrap.css" rel="stylesheet">

	{* <link href="http://bootswatch.com/united/bootstrap.min.css" rel="stylesheet"> *}

	{compiledCss glue=true nameMethod=hash}
	<!--[if lt IE 8]>
		<link href="stylesheet/frontend/FrontendIE.css" rel="Stylesheet" type="text/css"/>
		{if $ieCss}
			<link href="[[ieCss]]" rel="Stylesheet" type="text/css"/>
		{/if}
	<![endif]-->

	<!-- JavaScript includes -->

	{compiledJs glue=true nameMethod=hash}
	<script src="bootstrap/bootstrap/js/bootstrap.js"></script>

	<script type="text/javascript">
		Router.setUrlTemplate('{link controller=controller action=action}');
	</script>
</head>

<body class="[[request.controller]]Con [[request.controller]]-[[request.action]]{if {isRTL}} rtl{/if}{if $bodyClass} [[bodyClass]]{/if}">
	{liveCustomization action="menu"}
	<div id="container" class="lang_{localeCode}">
		<div id="containerWrapper1">
		<div id="containerWrapper2">
		<div id="containerWrapper3">
			<div class="container">
				<div class="row">
					{block PAGE-TOP}
					[[ACTION_VIEW]]
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
					</script>
	{/if}


	</body>
</html>
