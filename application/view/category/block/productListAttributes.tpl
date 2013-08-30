<div class="spec">
	{foreach from=$product.attributes item="attr" name="attr"}
		{% if $attr.values %}
			{foreach from=$attr.values item="value" name="values"}
				[[value.value_lang]]
				{% if !$smarty.foreach.values.last %}
				/
				{% endif %}
			{/foreach}
		{% elseif $attr.value %}
			[[attr.SpecField.valuePrefix_lang]][[attr.value]][[attr.SpecField.valueSuffix_lang]]
		{% elseif $attr.value_lang %}
			[[attr.value_lang]]
		{% endif %}

		{% if !$smarty.foreach.attr.last %}
		/
		{% endif %}
	{/foreach}
</div>