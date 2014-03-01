{% if 'row' != $format %}
	{assign var=container value="table"}
	{assign var=row value="tr"}
	{assign var=cell value="td"}
{% else %}
	{assign var=container value="div"}
	{assign var=row value="p"}
	{assign var=cell value="label"}
{% endif %}

<[[container]]>
	{foreach from=$item.attributes item="attr" name="attributes"}

		{% if $attr.values || $attr.value || $attr.value() %}

			{% if $prevAttr.EavField.EavFieldGroup.ID != $attr.EavField.EavFieldGroup.ID %}
				<[[row]] class="specificationGroup{% if $smarty.foreach.attributes.first %} first{% endif %}">
					<[[cell]] class="param">[[attr.EavField.EavFieldGroup.name()]]</[[cell]]>
					<[[cell]] class="value"></[[cell]]>
				</[[row]]>
			{% endif %}
			<[[row]]>
				<[[cell]] class="param">[[attr.EavField.name()]]</[[cell]]>
				<[[cell]] class="value">
					{% if $attr.values %}
						<ul class="attributeList{% if $attr.values|@count == 1 %} singleValue{% endif %}">
							{foreach from=$attr.values item="value"}
								<li> [[value.value()]]</li>
							{/foreach}
						</ul>
					{% elseif $attr.value() %}
						[[attr.value()]]
					{% elseif $attr.value %}
						[[attr.EavField.valuePrefix()]][[attr.value]][[attr.EavField.valueSuffix()]]
					{% endif %}
				</[[cell]]>
			</[[row]]>
			{% set prevAttr = $attr %}

		{% endif %}

	{/foreach}
</[[container]]>