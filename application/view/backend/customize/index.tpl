{pageTitle}{t _live_customization}{/pageTitle}
{includeCss file=backend/Customize.css}

{include file="layout/backend/header.tpl"}

<ul id="customizeMenu">
	
	<li{if $isCustomizationModeEnabled} class="active"{/if}>

		<a href="{link controller=backend.customize action=customizationMode}" class="customizeControl {if $isCustomizationModeEnabled}on{/if}">
		{if $isCustomizationModeEnabled}
			{tn _turn_off}
		{else}
			{tn _turn_on}
		{/if}
		</a>
		
		<div class="modeDescr">
			{t _live_locate}
		</div>

	</li>

	<li{if $isTranslationModeEnabled} class="active"{/if}>
	
		<a href="{link controller=backend.customize action=translationMode}" class="customizeControl {if $isTranslationModeEnabled}on{/if}">
		{if $isTranslationModeEnabled}
			{tn _turn_off_trans}
		{else}
			{tn _turn_on_trans}
		{/if}
		</a>
		
		<div class="modeDescr">
			{t _live_trans}
		</div>

	</li>

</ul>

<a href="{link}" target="_blank" id="goToFrontend">{tn _go_frontend}</a>

{include file="layout/backend/footer.tpl"}