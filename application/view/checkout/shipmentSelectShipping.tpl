<p>
    {t Select shipping method}:
</p>

<div class="shippingMethods">

    {foreach from=$rates.$key item="rate" name="rates"}                
        
        <p>
            
            {if $smarty.foreach.rates.last}
                <fieldset class="error">
            {/if}
            
            {radio name="shipping_`$key`" id="shipping_`$key`_`$rate.serviceID`" value=$rate.serviceID class="radio"}
            <label for="shipping_{$key}_{$rate.serviceID}" class="radio">
                {$rate.ShippingService.name_lang}
                (<strong>{$rate.taxPrice.$currency}</strong>)
            </label>       
        
            {if $smarty.foreach.rates.last}
            	<div class="errorText hidden{error for="shipping_`$key`"} visible{/error}" style="clear: both;">
            		<div>{error for="shipping_`$key`"}{$msg}{/error}</div>
                </div>            
                </fieldset>
            {/if}
        
        </p>            
                
    {/foreach}
        
</div>                
