{foreach order.attributes as attr}
	{% if attr.EavField.isDisplayedInList && attr.EavField && (attr.values || attr.value || attr.value()) %}
		<label class="attrName">[[attr.EavField.name()]]:</label>
		<label class="attrValue">
			{% if attr.values %}
				<ul class="attributeList{% if attr.values|@count == 1 %} singleValue{% endif %}">
					{foreach from=attr.values item="value"}
						<li> [[value.value()]]</li>
					{% endfor %}
				</ul>
			{% elseif attr.value() %}
				[[attr.value()]]
			{% elseif attr.value %}
				[[attr.EavField.valuePrefix()]][[attr.value]][[attr.EavField.valueSuffix()]]
			{% endif %}
		</label>
	{% endif %}
{% endfor %}