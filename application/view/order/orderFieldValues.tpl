{foreach $order.attributes as $attr}
	{if $attr.EavField.isDisplayedInList && $attr.EavField && ($attr.values || $attr.value || $attr.value_lang)}
		<label class="attrName">{$attr.EavField.name_lang}:</label>
		<label class="attrValue">
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
		</label>
	{/if}
{/foreach}