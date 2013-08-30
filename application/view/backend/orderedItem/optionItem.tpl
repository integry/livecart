<div{% if $option.isRequired %} class="required"{% endif %} class="productOption">
	{% if $option.fieldName %}{assign var=fieldName value=$option.fieldName}{% else %}{assign var=fieldName value="option_`$option.ID`"}{% endif %}

	{input name=$fieldName}
	{% if 0 == $option.type %}
		{checkbox}
		{label}
			[[option.name_lang]]
			{% if $option.DefaultChoice.priceDiff != 0 %}
				({$option.DefaultChoice.formattedPrice.$currency})
			{% endif %}
		{/label}
	{% else %}
		{label}[[option.name_lang]]{/label}
		{% if 1 == $option.type %}
			<select name="[[fieldName]]">
				{foreach from=$option.choices item=choice}
					<option value="[[choice.ID]]"{% if $selectedChoice.Choice.ID == $choice.ID %} selected="selected"{% endif %}>
						[[choice.name_lang]]
						{% if $choice.priceDiff != 0 %}
							({$choice.formattedPrice.$currency})</label>
						{% endif %}
					</option>
				{/foreach}
			</select>
		{% elseif 2 == $option.type %}
			{textfield class="text"}
		{% elseif 3 == $option.type %}
			{filefield name="upload_`$fieldName`"}
			{hidden name=$fieldName}
			{error for="upload_`$fieldName`"}<div class="errorText">[[msg]]</div>{/error}
		{% endif %}
	{% endif %}
	{/input}
</div>
<div class="clear"></div>