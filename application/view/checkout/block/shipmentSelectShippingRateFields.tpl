{foreach from=$rates.$key item="rate" name="rates"}
	<p>
		{radio name="shipping_`$key`" id="shipping_`$key`_`$rate.serviceID`" value=$rate.serviceID class="radio"}
		<label for="shipping_{$key}_{$rate.serviceID}" class="radio">
			{$rate.ShippingService.name_lang}
			(&rlm;<strong>{$rate.taxPrice.$currency}</strong>)
			{foreach $rate.ShippingService.attributes as $attr}
				<p class="fieldDescription">
					<label class="attrName fieldDescription">{$attr.EavField.name_lang}:</label>
					<label class="attrValue fieldDescription">
						{if $attr.values}
							<ul class="attributeList{if $attr.values|@count == 1} singleValue{/if}">
								{foreach from=$attr.values item="value"}
									<li class="fieldDescription"> {$value.value_lang}</li>
								{/foreach}
							</ul>
						{elseif $attr.value_lang}
							{$attr.value_lang}
						{elseif $attr.value}
							{$attr.EavField.valuePrefix_lang}{$attr.value}{$attr.EavField.valueSuffix_lang}
						{/if}
					</label>
				</p>
			{/foreach}
				
			<p class="fieldDescription">
				{if $rate.ShippingService.description_lang}
					{$rate.ShippingService.description_lang|escape}<br />
				{/if}
				{if $rate.ShippingService.formatted_deliveryTimeMaxDays && $rate.ShippingService.formatted_deliveryTimeMinDays}
					{t _deliveryDate_between}:
					<strong>{$rate.ShippingService.formatted_deliveryTimeMinDays.date_long|escape}</strong>
						{t _and}
					<strong>{$rate.ShippingService.formatted_deliveryTimeMaxDays.date_long|escape}</strong>
				{elseif $rate.ShippingService.formatted_deliveryTimeMaxDays}
					{t _deliveryDate_before}: <strong>{$rate.ShippingService.formatted_deliveryTimeMaxDays.date_long|escape}</strong>
				{elseif $rate.ShippingService.formatted_deliveryTimeMinDays}
					{t _deliveryDate_after}: <strong>{$rate.ShippingService.formatted_deliveryTimeMinDays.date_long|escape}</strong>
				{/if}
			</p>
		</label>
	</p>
{/foreach}

<div class="text-error hidden{error for="shipping_`$key`"} visible{/error}" style="clear: both;">
	<div>{error for="shipping_`$key`"}{$msg}{/error}</div>
</div>
