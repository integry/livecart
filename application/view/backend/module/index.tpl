{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="backend/Module.js"}
{includeCss file="backend/Module.css"}
{includeCss file="library/ActiveList.css"}
{pageTitle help="settings.modules"}{t _modules}{/pageTitle}

{include file="layout/backend/header.tpl"}

<ul id="moduleList" class="activeList">
	{foreach $modules as $module}
		{include file="backend/module/node.tpl"}
	{/foreach}
</ul>

{literal}
	<script type="text/javascript">
		new Backend.Module($('moduleList'));
	</script>
{/literal}

{include file="layout/backend/footer.tpl"}