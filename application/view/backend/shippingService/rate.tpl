<fieldset class="error shippingService_subtotalRange">
    <label class="observe" for="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_subtotalRangeStart">{t _subtotal_range}</label>
    <input type="text" name="rate__subtotalRangeStart" value="{$rate.subtotalRangeStart}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_subtotalRangeStart" class="shippingService_subtotalRangeStart shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} /> 
    - 
    <input type="text" name="rate__subtotalRangeEnd" value="{$rate.subtotalRangeEnd}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_subtotalRangeEnd" class="shippingService_subtotalRangeEnd shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
    <br />
    <span class="errorText hidden"> </span>
</fieldset>

<fieldset class="error shippingService_weightRange">
    <label class="observe" for="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_weightRangeStart">{t _weight_range}</label>

    <span>
        <input type="text" name="rate__weightRangeStartHiUnit" class="number" onkeyup="Backend.UnitConventer.prototype.updateShippingWeight(this);" {denied role='delivery.update'}readonly="readonly"{/denied} /> 
        <span class="shippingUnit_hi">{t _units_kg}</span>
        <input type="text" name="rate__weightRangeStartLoUnit" class="number" onkeyup="Backend.UnitConventer.prototype.updateShippingWeight(this);" {denied role='delivery.update'}readonly="readonly"{/denied} /> 
        <span class="shippingUnit_lo">{t _units_g}</span>
        <input type="text" name="rate__weightRangeStart" value="{$rate.weightRangeStart}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_weightRangeStart" class="shippingService_weightRangeStart shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} /> 
    <span>
    
    - 
    
    <span>
        <input type="text" name="rate__weightRangeStartHiUnit" class="number" onkeyup="Backend.UnitConventer.prototype.updateShippingWeight(this);" {denied role='delivery.update'}readonly="readonly"{/denied} /> 
        <span class="shippingUnit_hi">{t _units_kg}</span>
        <input type="text" name="rate__weightRangeStartLoUnit" class="number" onkeyup="Backend.UnitConventer.prototype.updateShippingWeight(this);" {denied role='delivery.update'}readonly="readonly"{/denied} /> 
        <span class="shippingUnit_lo">{t _units_g}</span>
        <input type="text" name="rate__weightRangeEnd" value="{$rate.weightRangeEnd}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_weightRangeEnd" class="shippingService_weightRangeEnd shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
    </span>
    
    <span class="unitSwitch">
        <span class="unitDef english_title" style="display: none;">{t _switch_to_english_units}</span>
        <span class="unitDef metric_title" style="display: none;">{t _switch_to_metric_units}</span>
        <span class="unitDef english_hi" style="display: none;">{t _units_kg}</span>
        <span class="unitDef english_lo" style="display: none;">{t _units_g}</span>
        <span class="unitDef metric_hi" style="display: none;">{t _units_pounds}</span>
        <span class="unitDef metric_lo" style="display: none;">{t _units_ounces}</span>
                                                
        <a href="#" onclick="Backend.UnitConventer.prototype.switchUnitTypes(this); return false;">{t _switch_to_english_units}</a>
    </span>
    
    <br />
    <span class="errorText hidden"> </span>
</fieldset>

<fieldset class="error">
    <label for="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_flatCharge">{t _flat_charge}</label>
	<input type="text" name="rate__flatCharge" value="{$rate.flatCharge}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_flatCharge"  class="shippingService_flatCharge shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
	{$defaultCurrencyCode}
    <br />
    <span class="errorText hidden"> </span>
</fieldset>


<fieldset class="error">
    <label for="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_perItemCharge">{t _per_item_charge}</label>
	<input type="text" name="rate__perItemCharge" value="{$rate.perItemCharge}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_perItemCharge" class="shippingService_perItemCharge shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
	{$defaultCurrencyCode}
    <br />
    <span class="errorText hidden"> </span>
</fieldset>
    
<fieldset class="error">
    <label for="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_subtotalPercentCharge">{t _subtotal_percent_charge}</label>
	<input type="text" name="rate__subtotalPercentCharge" value="{$rate.subtotalPercentCharge}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_subtotalPercentCharge" class="shippingService_subtotalPercentCharge shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
	<br />
    <span class="errorText hidden"> </span>
</fieldset>

<fieldset class="error">
    <label for="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_perKgCharge">{t _per_kg_charge}</label>
	<input type="text" name="rate__perKgCharge" value="{$rate.perKgCharge}" id="shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_perKgCharge" class="shippingService_perKgCharge shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
	<br />
    <span class="errorText hidden"> </span>
</fieldset>


<fieldset class="error controls shippingService_rate_controls">
    <span class="progressIndicator" style="display: none;"></span>
    <input type="button" class="shippingService_rate_save button submit" value="{if $rate.ID > 0}{t _save}{else}{t _add}{/if}" />
    {t _or}
    <a href="#cancel" class="shippingService_rate_cancel cancel">{t _cancel}</a>
</fieldset>