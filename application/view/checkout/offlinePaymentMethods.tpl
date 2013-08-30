{foreach from=$offlineMethods key="key" item="method"}
	<h2>{"OFFLINE_NAME_`$key`"|config}</h2>

	[[ partial('checkout/offlineMethodInfo.tpl', ['method': key]) ]]

	{form action="controller=checkout action=payOffline query=id=$method" handle=$offlineForms[$method] method="POST" class="form-horizontal"}
		[[ partial('block/eav/fields.tpl', ['fieldList': offlineVars[$method].specFieldList]) ]]
		[[ partial('block/submit.tpl', ['caption': "_complete_now"]) ]]
		<input type="hidden" name="[[method]]" value="1" />
	{/form}

{/foreach}