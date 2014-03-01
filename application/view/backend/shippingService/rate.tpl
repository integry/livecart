<fieldset class="error shippingService_subtotalRange">
	<label class="observe" for="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_subtotalRangeStart">{t _subtotal_range}</label>
	<b>{t _from}</b>
	<input type="text" name="rate__subtotalRangeStart" value="{rate.subtotalRangeStart|default:0}" id="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_subtotalRangeStart" class="shippingService_subtotalRangeStart shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />

	<b>{t _to}</b>
	<input type="text" name="rate__subtotalRangeEnd" value="{rate.subtotalRangeEnd|default:0}" id="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_subtotalRangeEnd" class="shippingService_subtotalRangeEnd shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} /> [[defaultCurrencyCode]]

	<br />
	<span class="errorText hidden"> </span>
</fieldset>

<fieldset class="error shippingService_weightRange">
	<label class="observe" for="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_weightRangeStart">{t _weight_range}</label>

	<div class="weightRange">
		<div class="weightRangeStart">
			<b>{t _from}</b>
			{metricsfield name="rate__weightRangeStart" hideSwitch=1 value=rate.weightRangeStart|default:0 id="shippingService_`rate.ShippingService.DeliveryZone.ID`_`rate.ShippingService.ID`_`rate.ID`_weightRangeStart" class="shippingService_weightRangeStart"}
		</div>
		<div class="weightRangeEnd">
			<b>{t _to}</b>
			{metricsfield name="rate__weightRangeEnd" value=rate.weightRangeEnd|default:0 id="shippingService_`rate.ShippingService.DeliveryZone.ID`_`rate.ShippingService.ID`_`rate.ID`_weightRangeEnd" class="shippingService_weightRangeEnd"}
		</div>

		<br />
		<span class="errorText hidden"> </span>
	</div>
</fieldset>

<fieldset class="error">
	<label for="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_flatCharge">{t _flat_charge}</label>
	<input type="text" name="rate__flatCharge" value="{rate.flatCharge|default:0}" id="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_flatCharge"  class="shippingService_flatCharge shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
	[[defaultCurrencyCode]]
	<br />
	<span class="errorText hidden"> </span>
</fieldset>


<fieldset class="error">
	<label for="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_perItemCharge">{t _per_item_charge}</label>
	<fieldset class="container shippingClassRates">
		<input type="text" name="rate__perItemCharge" value="{rate.perItemCharge|default:0}" id="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_perItemCharge" class="shippingService_perItemCharge shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
		[[defaultCurrencyCode]]

		{foreach from=shippingClasses item=name key=classID}
			<p>
				<label>[[name]]</label>
				<input type="text" name="rate__perItemChargeClass[[[classID]]]" value="{rate.perItemChargeClassData[classID]|default:''}" class="shippingService_perItemChargeClass shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
				[[defaultCurrencyCode]]
			</p>
		{% endfor %}
	</fieldset>
	<br />
	<span class="errorText hidden"> </span>
</fieldset>

<fieldset class="error">
	<label for="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_subtotalPercentCharge">{t _subtotal_percent_charge}</label>
	<input type="text" name="rate__subtotalPercentCharge" value="{rate.subtotalPercentCharge|default:0}" id="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_subtotalPercentCharge" class="shippingService_subtotalPercentCharge shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} /> %
	<br />
	<span class="errorText hidden"> </span>
</fieldset>

<fieldset class="error">
	<label for="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_perKgCharge">{t _per_kg_charge}</label>
	<input type="text" name="rate__perKgCharge" value="{rate.perKgCharge|default:0}" id="shippingService_[[rate.ShippingService.DeliveryZone.ID]]_[[rate.ShippingService.ID]]_[[rate.ID]]_perKgCharge" class="shippingService_perKgCharge shippingService_rateFloatValue" {denied role='delivery.update'}readonly="readonly"{/denied} />
	<br />
	<span class="errorText hidden"> </span>
</fieldset>