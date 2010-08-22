{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="backend/Settings.js"}
{includeJs file="backend/User.js"}
{includeJs file="library/lightbox/lightbox.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="backend/Settings.css"}
{includeCss file="library/lightbox/lightbox.css"}

{pageTitle help="settings.configuration"}{t _livecart_settings|branding}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="settingsContainer" class="maxHeight h--50">

	<div class="treeContainer">
		<div id="settingsBrowser" class="treeBrowser"></div>
	</div>

	<span id="settingsIndicator"></span>

	<div id="settingsContent" class="treeManagerContainer maxHeight">
		<span class="progressIndicator"></span>
	</div>

</div>

<div id="activeSettingsPath" ></div>

{literal}
<script type="text/javascript">
	var settings = new Backend.Settings({/literal}{$categories}, {$settings}{literal});
	settings.urls['edit'] = '{/literal}{link controller=backend.settings action=edit}?id=_id_{literal}';
	Event.observe(window, 'load', function() {settings.init();})
</script>
{/literal}

<div id="handlers" style="display: none;">
	<div id="handler_ENABLED_COUNTRIES" style="position: absolute; right: 0; z-index: 10; padding-right: 5px;">
		<a href="#" class="countrySelect">{t _select_all}</a> | <a href="#" class="countryDeselect">{t _deselect_all}</a>
	</div>

	<div id="handler_ENABLE_SITEMAPS">
		<a href="{link controller=sitemap action=ping}" id="siteMapPing" class="menu">{t _sitemap_ping}</a> |
		<a href="{link controller=sitemap}" class="menu" target="_blank">{t _view_sitemap}</a>
		<span class="progressIndicator" id="siteMapFeedback" style="display: none;"></span>
		<div id="siteMapSubmissionResult"></div>
	</div>

	<div id="handler_ENABLED_FEEDS">
		<a href="{link controller=xml action=export module=module query="key=accessKey"}" target="_blank" style="margin-left: 0.5em;"><span style="font-size: smaller;">[{t _open_feed}]</a></a>
	</div>

	<div id="handler_SOFT_NAME">
		<a href="{link controller=backend.settings action=disablePrivateLabel}" id="disablePrivateLabel" class="menu">{t _disable_private_label_change}</a>
		<span class="progressIndicator" style="display: none;"></span>
	</div>
</div>

<iframe id="upload" name="upload"></iframe>

{include file="layout/backend/footer.tpl"}