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
    
    <div class="weightRangeStart">
    {metricsfield name="rate__subtotalRangeStart" hideSwitch=1 value=$rate.weightRangeStart id="shippingService_`$rate.ShippingService.DeliveryZone.ID`_`$rate.ShippingService.ID`_`$rate.ID`_weightRangeStart" class="shippingService__weightRangeStart"}
    </div>
    <div class="weightDelimiter">
    -
    </div>
    
    <div class="weightRangeEnd">
    {metricsfield name="rate__subtotalRangeEnd" value=$rate.weightRangeEnd id="shippingService_`$rate.ShippingService.DeliveryZone.ID`_`$rate.ShippingService.ID`_`$rate.ID`_weightRangeEnd" class="shippingService__weightRangeEnd"}
    </div>

    <script type="text/javascript">
        var switchMetricsEnd = Backend.UnitConventer.prototype.getInstance("UnitConventer_Root_shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_weightRangeEnd");
        
        Event.observe(switchMetricsEnd.nodes.switchUnits, 'click', function(e) {ldelim}
            var switchMetricsStart = Backend.UnitConventer.prototype.getInstance("UnitConventer_Root_shippingService_{$rate.ShippingService.DeliveryZone.ID}_{$rate.ShippingService.ID}_{$rate.ID}_weightRangeStart");
            switchMetricsStart.switchUnitTypes();
        {rdelim});
    </script>

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