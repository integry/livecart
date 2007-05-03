<input type="hidden" name="rateID" value="{$rate.ID}" />

{if $isSubtotal}
    <span class="shippingService_subtotalRange">
        <label class="observe">{t _subtotal_range}</label>
        <input type="text" id="shippingService_{$zoneID}_{$serviceID}_{$rateID}_subtotalRangeStart" class="shippingService_rateFloatValue" /> 
        - 
        <input type="text" id="shippingService_{$zoneID}_{$serviceID}_{$rateID}_subtotalRangeEnd" class="shippingService_rateFloatValue"  />
        <span class="errorText hidden"> </span>
        <br />
    </span>
{else}
    <span class="shippingService_weightRange">
        <label class="observe">{t _weight_range}</label>
        <input type="text" id="shippingService_{$zoneID}_{$serviceID}_{$rateID}_weightRangeStart" class="shippingService_rateFloatValue" /> 
        - 
        <input type="text" id="shippingService_{$zoneID}_{$serviceID}_{$rateID}_weightRangeEnd" class="shippingService_rateFloatValue"  />
        <span class="errorText hidden"> </span>
        <br />
    </span>
{/if}

<label>{t _flat_charge}</label>
<fieldset class="error">
	<input type="text" id="shippingService_{$zoneID}_{$serviceID}_{$rateID}_flatCharge"  class="shippingService_rateFloatValue" />
	<span class="errorText hidden"> </span>
</fieldset>

<label>{t _per_item_charge}</label>
<fieldset class="error">
	<input type="text" id="shippingService_{$zoneID}_{$serviceID}_{$rateID}_perItemCharge" class="shippingService_rateFloatValue" />
	<span class="errorText hidden"> </span>
</fieldset>

{if $isSubtotal}
    <label>{t _subtotal_percent_charge}</label>
    <fieldset class="error">
    	<input type="text" id="shippingService_{$zoneID}_{$serviceID}_{$rateID}_subtotalPercentCharge" class="shippingService_rateFloatValue" />
    	<span class="errorText hidden"> </span>
    </fieldset>
{else}
    <label>{t _per_kg_charge}</label>
    <fieldset class="error">
    	<input type="text" id="shippingService_{$zoneID}_{$serviceID}_{$rateID}_perKgCharge" class="shippingService_rateFloatValue" />
    	<span class="errorText hidden"> </span>
    </fieldset>
{/if}

 