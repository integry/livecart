{foreach from=$offlineMethods key="key" item="method"}
	<h2>{"OFFLINE_NAME_`$key`"|config}</h2>

	{if "OFFLINE_LOGO_`$key`"|config}
		<p class="offlineMethodLogo">
			<img src="{"OFFLINE_LOGO_`$key`"|config}" />
		</p>
	{/if}

	{if "OFFLINE_DESCR_`$key`"|config}
		<p class="offlineMethodDescr">
			{"OFFLINE_DESCR_`$key`"|config}
		</p>
	{/if}

	{if "OFFLINE_INSTR_`$key`"|config}
		<p class="offlineMethodInstr">
			{"OFFLINE_INSTR_`$key`"|config}
		</p>
	{/if}

	{form action="controller=checkout action=payOffline query=id=$method" handle=$offlineForms[$method] method="POST"}
		{include file="block/eav/fields.tpl" specFieldList=$offlineVars[$method].specFieldList}
		<p class="submit">
			<label></label>
			<input type="submit" class="submit" name="{$method}" value="{tn _complete_now}" />
		</p>
	{/form}

{/foreach}