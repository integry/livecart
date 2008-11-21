{foreach from=$attributes item="attr" name="attributes"}

	{if $prevAttr.$field.$group.ID != $attr.$field.$group.ID}
		<tr class="specificationGroup heading{if $smarty.foreach.attributes.first} first{/if}">
			<td class="param">{$attr.$field.$group.name_lang}</td>
			<td class="value"></td>
		</tr>
	{/if}
	<tr class="{zebra loop="attributes"}">
		<td class="param">{$attr.$field.name_lang}</td>
		<td class="value">
			{include file="product/attributeValue.tpl"}
		</td>
	</tr>
	{assign var="prevAttr" value=$attr}

{/foreach}