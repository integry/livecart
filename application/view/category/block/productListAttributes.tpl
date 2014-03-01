<div class="spec">
	{foreach from=product.attributes item="attr" name="attr"}
		{% if attr.values %}
			{foreach from=attr.values item="value" name="values"}
				[[value.value()]]
				{% if !smarty.foreach.values.last %}
				/
				{% endif %}
			{% endfor %}
		{% elseif attr.value %}
			[[attr.SpecField.valuePrefix()]][[attr.value]][[attr.SpecField.valueSuffix()]]
		{% elseif attr.value() %}
			[[attr.value()]]
		{% endif %}

		{% if !smarty.foreach.attr.last %}
		/
		{% endif %}
	{% endfor %}
</div>