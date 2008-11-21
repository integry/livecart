<table>
	{foreach from=$item.attributes item="attr" name="attributes"}

		{if $attr.values || $attr.value || $attr.value_lang}

			{if $prevAttr.EavField.EavFieldGroup.ID != $attr.EavField.EavFieldGroup.ID}
				<tr class="specificationGroup{if $smarty.foreach.attributes.first} first{/if}">
					<td class="param">{$attr.EavField.EavFieldGroup.name_lang}</td>
					<td class="value"></td>
				</tr>
			{/if}
			<tr class="{zebra loop="attributes"}">
				<td class="param">{$attr.EavField.name_lang}</td>
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
						{$attr.EavField.valuePrefix_lang}{$attr.value}{$attr.EavField.valueSuffix_lang}
					{/if}
				</td>
			</tr>
			{assign var="prevAttr" value=$attr}

		{/if}

	{/foreach}
</table>