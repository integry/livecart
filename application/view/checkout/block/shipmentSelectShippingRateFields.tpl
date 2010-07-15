{foreach from=$rates.$key item="rate" name="rates"}
	<p>
		{radio name="shipping_`$key`" id="shipping_`$key`_`$rate.serviceID`" value=$rate.serviceID class="radio"}
		<label for="shipping_{$key}_{$rate.serviceID}" class="radio">
			{$rate.ShippingService.name_lang}
			(&rlm;<strong>{$rate.taxPrice.$currency}</strong>)
		</label>
	</p>
{/foreach}

<div class="errorText hidden{error for="shipping_`$key`"} visible{/error}" style="clear: both;">
	<div>{error for="shipping_`$key`"}{$msg}{/error}</div>
</div>