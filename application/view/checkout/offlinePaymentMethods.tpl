{foreach from=$offlineMethods key="key" item="method"}
	<h2>{"OFFLINE_NAME_`$key`"|config}</h2>

	{include file="checkout/offlineMethodInfo.tpl" method=$key}

	{form action="controller=checkout action=payOffline query=id=$method" handle=$offlineForms[$method] method="POST"}
		{include file="block/eav/fields.tpl" fieldList=$offlineVars[$method].specFieldList}
		<p class="submit">
			<label></label>
			<input type="submit" class="submit" name="{$method}" value="{tn _complete_now}" />
		</p>
	{/form}

{/foreach}