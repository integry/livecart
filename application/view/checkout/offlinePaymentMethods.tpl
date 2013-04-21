{foreach from=$offlineMethods key="key" item="method"}
	<h2>{"OFFLINE_NAME_`$key`"|config}</h2>

	{include file="checkout/offlineMethodInfo.tpl" method=$key}

	{form action="controller=checkout action=payOffline query=id=$method" handle=$offlineForms[$method] method="POST"}
		{include file="block/eav/fields.tpl" fieldList=$offlineVars[$method].specFieldList}
		{include file="block/submit.tpl" caption="_complete_now"}
		<input type="hidden" name="{$method}" value="1" />
	{/form}

{/foreach}