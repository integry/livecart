{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/State.js"}
{includeJs file="backend/Module.js"}
{includeCss file="backend/Module.css"}
{includeCss file="library/ActiveList.css"}
{pageTitle help="settings.modules"}{t _modules}{/pageTitle}

{include file="layout/backend/header.tpl"}

<ul class="menu">
	<li id="download-modules"><a href="#">{t _add_modules}</a></li>
	<li id="manage-repos"><a href="{link controller=backend.settings}#section_095-updates__">{t _manage_module_repositories}</a></li>
</ul>

{include file="block/message.tpl"}

<ul id="moduleList" class="activeList">
	{include file="backend/module/list.tpl" type="needUpdate"}
	{include file="backend/module/list.tpl" type="enabled"}
	{include file="backend/module/list.tpl" type="notEnabled"}
	{include file="backend/module/list.tpl" type="notInstalled"}
	<div class="clear"></div>
</ul>
<div class="clear"></div>

{literal}
	<script type="text/javascript">
		new Backend.Module($('moduleList'));
	</script>
{/literal}

{include file="layout/backend/footer.tpl"}