<p>
    {t Select shipping method}:
</p>

<div class="shippingMethods">

    {foreach from=$rates.$key item="rate"}                
        <p>
            {radio name="shipping_`$key`" id="shipping_`$key`_`$rate.serviceID`" value=$rate.serviceID class="radio"}
            <label for="shipping_{$key}_{$rate.serviceID}" class="radio">
                {$rate.ShippingService.name_lang}
                (<strong>{$rate.formattedPrice.$currency}</strong>)
            </label>
        </p>            
    {/foreach}
    
	<div class="errorText hidden{error for="shipping_`$key`"} visible{/error}" style="clear: both;">
		<div>{error for="shipping_`$key`"}{$msg}{/error}</div>
    	<div class="clear"></div>
    </div>            
    
</div>                
