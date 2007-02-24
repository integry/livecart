{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="backend/Settings.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="backend/Settings.css"}

{pageTitle}LiveCart Settings{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="settingsContainer" class="maxHeight h--50">
	<div id="settingsBrowser" class="treeBrowser">
	</div>

	<span id="settingsIndicator"></span>
	
	<div class="yellowMessage" style="display: none;"><div>{t _save_conf}</div></div>
	
	<div id="settingsContent" class="maxHeight" style="margin-left: 240px;">
	test
	</div>

</div>

<div id="activeSettingsPath"></div>

{literal}
<script type="text/javascript">
	var settings = new Backend.Settings({/literal}{$categories}{literal});
	settings.urls['edit'] = '{/literal}{link controller=backend.settings action=edit}?id=_id_{literal}';
	settings.urls['save'] = '{/literal}{link controller=backend.settings action=save}?id=_id_{literal}';
    settings.treeBrowser.selectItem('00-store', true, false);	
</script>
{/literal}

{include file="layout/backend/footer.tpl"}