{assign var="field" value=$field|default:"SpecField"}
{if $attr.values}
	<ul class="attributeList{if $attr.values|@count == 1} singleValue{/if}">
		{foreach from=$attr.values item="value"}
			<li> [[value.value_lang]]</li>
		{/foreach}
	</ul>
{elseif $attr.value_lang}
	[[attr.value_lang]]
{elseif $attr.value}
	{$attr.$field.valuePrefix_lang}[[attr.value]]{$attr.$field.valueSuffix_lang}
{/if}
