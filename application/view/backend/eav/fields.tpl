{assign var=containerId value=$blah|rand:1000000}
<div id="eavContainer_{$containerId}">
{foreach from=$specFieldList key=groupID item=fieldList}

	{if $groupID}
		<fieldset>
			<legend>{$fieldList.0.$groupClass.name_lang}</legend>
	{/if}

	{foreach from=$fieldList item=field}
	<p class="{if $field.isRequired}required{/if} {if !$field.isDisplayed}notDisplayed{/if}">
		<label for="product_{$cat}_{$product.ID}_{$field.fieldName}">{$field.name_lang}:</label>
		<fieldset class="error">
			{include file="backend/eav/specFieldFactory.tpl" field=$field cat=$cat}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	{/foreach}

	{if $groupID}
		</fieldset>
	{/if}
{/foreach}
</div>

{literal}
<script type="text/javascript">
	new Backend.Eav($('eavContainer_{/literal}{$containerId}'));
</script>