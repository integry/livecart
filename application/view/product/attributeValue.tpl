{assign var="field" value=$field|default:"SpecField"}
{% if $attr.values %}
	<ul class="attributeList{% if $attr.values|@count == 1 %} singleValue{% endif %}">
		{foreach from=$attr.values item="value"}
			<li> [[value.value()]]</li>
		{/foreach}
	</ul>
{% elseif $attr.value() %}
	[[attr.value()]]
{% elseif $attr.value %}
	{$attr.$field.valuePrefix()}[[attr.value]]{$attr.$field.valueSuffix()}
{% endif %}
