{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="backend/Settings.js"}
{includeJs file="library/lightbox/lightbox.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="backend/Settings.css"}
{includeCss file="library/lightbox/lightbox.css"}

{pageTitle help="settings.configuration"}{t _livecart_settings}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="settingsContainer" class="maxHeight h--50">

	<div class="treeContainer">
		<div id="settingsBrowser" class="treeBrowser"></div>
		<div id="confirmations"></div>
	</div>

	<span id="settingsIndicator"></span>

	<div id="settingsContent" class="treeManagerContainer maxHeight">
		<span class="progressIndicator"></span>
	</div>

</div>

<div id="activeSettingsPath" ></div>

{literal}
<script type="text/javascript">
	var settings = new Backend.Settings({/literal}{$categories}{literal});
	settings.urls['edit'] = '{/literal}{link controller=backend.settings action=edit}?id=_id_{literal}';
	Event.observe(window, 'load', function() {settings.activateCategory('00-store');})
</script>
{/literal}

<div id="handlers" style="display: none;">
	<div id="handler_ENABLED_COUNTRIES" style="position: absolute; right: 0; z-index: 10; padding-right: 5px;">
		<a href="#" class="countrySelect">{t _select_all}</a> | <a href="#" class="countryDeselect">{t _deselect_all}</a>
	</div>

	<div id="handler_ENABLE_SITEMAPS">
		<a href="{link controller=sitemap action=ping}" id="siteMapPing" class="menu">{t _sitemap_ping}</a>
		<span class="progressIndicator" id="siteMapFeedback" style="display: none;"></span>
		<div id="siteMapSubmissionResult"></div>
	</div>
</div>

{include file="layout/backend/footer.tpl"}