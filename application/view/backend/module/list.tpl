{if $sortedModules[$type]}
	<fieldset class="type_[[type]]">
		<legend>{translate text="_module_type_`$type`"}</legend>
		{foreach $sortedModules[$type] as $module}
			{include file="backend/module/node.tpl"}
		{/foreach}
	</fieldset>
{/if}
