{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="backend/Settings.js"}
{includeJs file="backend/User.js"}

{includeCss file="backend/Settings.css"}

{pageTitle help="settings.configuration"}{t _livecart_settings|branding}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<div id="settingsContainer" ng-controller="SettingsController" ng-init="setTree({$categories|escape});">
	<div class="treeContainer">
		{include file="backend/quickSearch/form.tpl" limit=10 hint=_hint_settings_search formid="SettingsSearch" classNames="SearchableItem,-SearchableTemplate"}

		[[ partial("block/backend/tree.tpl") ]]
	</div>

	<div id="settingsContent" class="treeManagerContainer">
		[[ partial("backend/settings/edit.tpl") ]]
	</div>

</div>

<div id="activeSettingsPath" ></div>

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
		<a href="{link controller="backend.settings" action=disablePrivateLabel}" id="disablePrivateLabel" class="menu">{t _disable_private_label_change}</a>
		<span class="progressIndicator" style="display: none;"></span>
	</div>

	<div id="handler_UPDATE_COPY_METHOD">
		<a href="{link controller="backend.update" action=testCopy}" id="testUpdateCopy" class="menu">{t _test_update_copy}</a>
		<span class="progressIndicator" style="display: none;"></span>
		<div id="testUpdateCopyResult"></div>
	</div>
</div>

<iframe id="upload" name="upload"></iframe>

[[ partial("layout/backend/footer.tpl") ]]