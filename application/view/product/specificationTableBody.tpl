{foreach from=$attributes item="attr" name="attributes"}

	{if $prevAttr.$field.$group.ID != $attr.$field.$group.ID}
		<tr class="specificationGroup heading{if $smarty.foreach.attributes.first} first{/if}">
			<td class="param">{$attr.$field.$group.name_lang}</td>
			<td class="value"></td>
		</tr>
	{/if}
	<tr class="{zebra loop="attributes"} {if $smarty.foreach.attributes.first && !$attr.$field.$group.ID}first{/if}{if $smarty.foreach.attributes.last} last{/if}">
		<td class="param">{$attr.$field.name_lang}</td>
		<td class="value">
			{if $attr.values}
				<ul class="attributeList{if $attr.values|@count == 1} singleValue{/if}">
					{foreach from=$attr.values item="value"}
						<li> {$value.value_lang}</li>
					{/foreach}
				</ul>
			{elseif $attr.value_lang}
				{$attr.value_lang}
			{elseif $attr.value}
				{$attr.$field.valuePrefix_lang}{$attr.value}{$attr.$field.valueSuffix_lang}
			{/if}
		</td>
	</tr>
	{assign var="prevAttr" value=$attr}

{/foreach}