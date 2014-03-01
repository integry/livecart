{% if $product.listAttributes %}
	<p class="specSummary spec">
		{foreach from=$product.listAttributes item="attr" name="attr"}
			{% if $attr.values %}
				{foreach from=$attr.values item="value" name="values"}
					[[value.value()]]
					{% if !$smarty.foreach.values.last %}
					/
					{% endif %}
				{/foreach}
			{% elseif $attr.value %}
				[[attr.SpecField.valuePrefix()]][[attr.value]][[attr.SpecField.valueSuffix()]]
			{% elseif $attr.value() %}
				[[attr.value()]]
			{% endif %}

			{% if !$smarty.foreach.attr.last %}
			/
			{% endif %}
		{/foreach}
	</p>
{% endif %}
