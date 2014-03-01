{foreach from=attributes item="attr" name="attributes"}

	{% if prevAttr.field.group.ID != attr.field.group.ID %}
		<tr class="specificationGroup heading{% if smarty.foreach.attributes.first %} first{% endif %}">
			<th colspan="2">{attr.field.group.name()}</th>
		</tr>
	{% endif %}
	<tr>
		<td class="param">{attr.field.name()}</td>
		<td class="value">
			[[ partial("product/attributeValue.tpl") ]]
		</td>
	</tr>
	{% set prevAttr = attr %}

{% endfor %}