{include file="layout/backend/meta.tpl"}

<div id="topAuthInfo">
	{block USER_MENU}
</div>

<div id="pageContainer">

	<div id="pageHeader">

		<div id="systemMenu">
				<a id="help" href="#" target="_blank" {if !'BACKEND_SHOW_HELP'|config}style="display:none;"{/if}>{t _base_help}</a>
				{if 'BACKEND_SHOW_HELP'|config} | {/if}
				{backendLangMenu}
				<a href="{link}" target="_blank">{t _frontend}</a>
		</div>

		<div id="topLogoImageContainer">
			<a href="{link controller=backend.index action=index}">
				{img src='BACKEND_LOGO_SMALL'|config|@or:"image/promo/transparentlogo_small.png" id="topLogoImage"}
			</a>
		</div>

		<div id="navContainer">
			<div id="nav"></div>
			{backendMenu}
			<div class="clear"></div>
		</div>

	</div>

	<div id="pageTitleContainer">
		<div id="pageTitle">{$PAGE_TITLE}</div>
		<div id="breadcrumb_template" class="dom_template">
			<span class="breadcrumb_item"><a href=""></a></span>
			<span class="breadcrumb_separator"> &gt; </span>
			<span class="breadcrumb_lastItem"></span>
		</div>
		<div id="breadcrumb"></div>
	</div>

	<div id="pageContentContainer">

		<div id="pageContentInnerContainer" class="maxHeight h--20"  >
