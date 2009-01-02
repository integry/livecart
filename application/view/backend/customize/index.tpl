{pageTitle help="customize"}{t _live_customization}{/pageTitle}
{includeCss file=backend/Customize.css}

{include file="layout/backend/header.tpl"}

<ul id="customizeMenu">

	<li{if $isCustomizationModeEnabled} class="active"{/if}>

		<a href="{link controller=backend.customize action=mode}" class="customizeControl {if $isCustomizationModeEnabled}on{/if}">
		{if $isCustomizationModeEnabled}
			{tn _turn_off}
		{else}
			{tn _turn_on}
		{/if}
		</a>

		<div class="modeDescr">
			{t _live_locate}
		</div>

		<a href="{link}" target="_blank" id="goToFrontend">{tn _go_frontend}</a>
		<div class="clear"></div>

	</li>

</ul>

{include file="layout/backend/footer.tpl"}