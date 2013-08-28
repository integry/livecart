<div class="spec">
	{foreach from=$product.attributes item="attr" name="attr"}
		{if $attr.values}
			{foreach from=$attr.values item="value" name="values"}
				[[value.value_lang]]
				{if !$smarty.foreach.values.last}
				/
				{/if}
			{/foreach}
		{elseif $attr.value}
			[[attr.SpecField.valuePrefix_lang]][[attr.value]][[attr.SpecField.valueSuffix_lang]]
		{elseif $attr.value_lang}
			[[attr.value_lang]]
		{/if}

		{if !$smarty.foreach.attr.last}
		/
		{/if}
	{/foreach}
</div>