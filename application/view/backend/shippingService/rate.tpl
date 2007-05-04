<div> </div>
<span class="shippingService_subtotalRange">
    <label class="observe">{t _subtotal_range}</label>
    <input type="text" value="{$rate.subtotalRangeStart}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_subtotalRangeStart" class="shippingService_subtotalRangeStart shippingService_rateFloatValue" /> 
    - 
    <input type="text" value="{$rate.subtotalRangeEnd}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_subtotalRangeEnd" class="shippingService_subtotalRangeEnd shippingService_rateFloatValue"  />
    <span class="errorText hidden"> </span>
    <br />
</span>

<span class="shippingService_weightRange">
    <label class="observe">{t _weight_range}</label>
    <input type="text" value="{$rate.weightRangeStart}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_weightRangeStart" class="shippingService_weightRangeStart shippingService_rateFloatValue" /> 
    - 
    <input type="text" value="{$rate.weightRangeEnd}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_weightRangeEnd" class="shippingService_weightRangeEnd shippingService_rateFloatValue"  />
    <span class="errorText hidden"> </span>
    <br />
</span>



<fieldset class="error">
    <label>{t _flat_charge}</label>
	<input type="text" value="{$rate.flatCharge}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_flatCharge"  class="shippingService_flatCharge shippingService_rateFloatValue" />
	<span class="errorText hidden"> </span>
</fieldset>


<fieldset class="error">
    <label>{t _per_item_charge}</label>
	<input type="text" value="{$rate.perItemCharge}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_perItemCharge" class="shippingService_perItemCharge shippingService_rateFloatValue" />
	<span class="errorText hidden"> </span>
</fieldset>


    
<fieldset class="error">
    <label for="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_subtotalPercentCharge">{t _subtotal_percent_charge}</label>
	<input type="text" value="{$rate.subtotalPercentCharge}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_subtotalPercentCharge" class="shippingService_subtotalPercentCharge shippingService_rateFloatValue" />
	<span class="errorText hidden"> </span>
</fieldset>

<fieldset class="error" >
    <label for="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_perKgCharge">{t _per_kg_charge}</label>
	<input type="text" value="{$rate.perKgCharge}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_perKgCharge" class="shippingService_perKgCharge shippingService_rateFloatValue" />
	<span class="errorText hidden"> </span>
</fieldset>


<fieldset class="error shippingService_rate_controls">
    <span class="activeForm_progress"></span>
    <input type="submit" class="shippingService_rate_save button submit" value="{if $rate.ID > 0}{t _save}{else}{t _add}{/if}" />
    {t _or}
    <a href="#cancel" class="shippingService_rate_cancel cancel">{t _cancel}</a>
</fieldset>