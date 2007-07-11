{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="backend/Settings.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="backend/Settings.css"}

{pageTitle help="config"}{t _livecart_settings}{/pageTitle}
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

{include file="layout/backend/footer.tpl"}