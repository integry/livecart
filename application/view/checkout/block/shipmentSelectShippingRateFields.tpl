<div class="row">
{foreach from=$rates.$key item="rate" name="rates"}
<div class="radio">
	<label>
		{radio name="shipping_`$key`" value=$rate.serviceID}
		[[rate.ShippingService.name_lang]]
		(&rlm;<strong>{$rate.taxPrice.$currency}</strong>)
	</label>

	{% if $rate.ShippingService.attributes %}
		<dl class="dl-horizontal">
		{foreach $rate.ShippingService.attributes as $attr}
			{% if $attr.values || $attr.value || $attr.value_lang %}
			<dt>[[attr.EavField.name_lang]]</dt>
			<dd>
				{% if $attr.values %}
					<ul class="attributeList{% if $attr.values|@count == 1 %} singleValue{% endif %}">
						{foreach from=$attr.values item="value"}
							<li class="fieldDescription"> [[value.value_lang]]</li>
						{/foreach}
					</ul>
				{% elseif $attr.value_lang %}
					[[attr.value_lang]]
				{% elseif $attr.value %}
					[[attr.EavField.valuePrefix_lang]][[attr.value]][[attr.EavField.valueSuffix_lang]]
				{% endif %}
			</dd>
			{% endif %}
		{/foreach}
		</dl>
	{% endif %}

	<span class="help-block">
		{% if $rate.ShippingService.description_lang %}
			{$rate.ShippingService.description_lang|escape}<br />
		{% endif %}
		{% if $rate.ShippingService.formatted_deliveryTimeMaxDays && $rate.ShippingService.formatted_deliveryTimeMinDays %}
			{t _deliveryDate_between}:
			<strong>{$rate.ShippingService.formatted_deliveryTimeMinDays.date_long|escape}</strong>
				{t _and}
			<strong>{$rate.ShippingService.formatted_deliveryTimeMaxDays.date_long|escape}</strong>
		{% elseif $rate.ShippingService.formatted_deliveryTimeMaxDays %}
			{t _deliveryDate_before}: <strong>{$rate.ShippingService.formatted_deliveryTimeMaxDays.date_long|escape}</strong>
		{% elseif $rate.ShippingService.formatted_deliveryTimeMinDays %}
			{t _deliveryDate_after}: <strong>{$rate.ShippingService.formatted_deliveryTimeMinDays.date_long|escape}</strong>
		{% endif %}
	</span>
</div>
{/foreach}

<div class="text-danger hidden{error for="shipping_`$key`"} visible{/error}" style="clear: both;">
	<div>{error for="shipping_`$key`"}[[msg]]{/error}</div>
</div>
</div>