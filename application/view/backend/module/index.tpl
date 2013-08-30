{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/State.js"}
{includeJs file="backend/Module.js"}
{includeCss file="backend/Module.css"}
{includeCss file="library/ActiveList.css"}
{pageTitle help="settings.modules"}{t _modules}{/pageTitle}

[[ partial("layout/backend/header.tpl") ]]

<ul class="menu" id="module-menu">
	<li id="download-modules" class="download-modules"><a href="#">{t _add_modules}</a></li>
	<li id="manage-repos"><a href="[[ url("backend.settings") ]]#section_095-updates__">{t _manage_module_repositories}</a></li>
	<li class="cancel download-modulesCancel" style="display: none;"><a href="#" class="cancel">{t _cancel_adding_modules}</a></li>
</ul>

<div id="download-modules-container" class="slideForm"></div>

<script type="text/javascript">
	new Backend.Module.downloadManager('{json array=$repos}');
</script>

[[ partial("block/message.tpl") ]]

<ul id="moduleList" class="activeList">
	<fieldset id="just-installed" class="type_justInstalled" style="display: none;">
		<legend>{translate text="_module_type_justInstalled"}</legend>
	</fieldset>

	[[ partial('backend/module/list.tpl', ['type': "needUpdate"]) ]]
	[[ partial('backend/module/list.tpl', ['type': "enabled"]) ]]
	[[ partial('backend/module/list.tpl', ['type': "notEnabled"]) ]]
	[[ partial('backend/module/list.tpl', ['type': "notInstalled"]) ]]
	<div class="clear"></div>
</ul>
<div class="clear"></div>


	<script type="text/javascript">
		window.moduleManager = new Backend.Module($('moduleList'));
	</script>


[[ partial("layout/backend/footer.tpl") ]]