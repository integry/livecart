{form id="shippingService_`$service.DeliveryZone.ID`_`$service.ID`" handle=$form action="controller=backend.deliveryZone action=update id=`$service.DeliveryZone.ID`" method="post" role="delivery.update"}
	<input type="hidden" name="deliveryZoneID" value="[[service.DeliveryZone.ID]]" />
	<input type="hidden" name="serviceID" value="[[service.ID]]" />

	{input name="name"}
		{label}{t _name}:{/label}
		{textfield class="observed shippingService_name"}
	{/input}

	{input name="isFinal"}
		{checkbox class="checkbox observed shippingService_isFinal"}
		{label}{tip _disable_other_services}{/label}
	{/input}

	{input name="isLocalPickup"}
		{checkbox class="checkbox observed shippingService_isLocalPickup"}
		{label}{tip _is_local_pickup}{/label}
	{/input}

	{input name="rangeType" class="rangeType"}
		{radio class="checkbox shippingService_rangeType" value="0"}
		{label}{tip _weight_based_calculations}{/label}
	{/input}

	{input name="rangeType" class="rangeType"}
		{radio class="checkbox shippingService_rangeType" value="1"}
		{label}{tip _subtotal_based_calculations}{/label}
	{/input}

	{input name="rangeTypeStatic" class="rangeTypeStatic"}
		<label>{tip _range_type}</label>
		<label style="width: auto;">{if $service.rangeType == 0}{tip _weight_based_calculations}{else}{tip _subtotal_based_calculations}{/if}</label>
	{/input}

	<div class="expectedDeliveryInterval">
		{input name="deliveryTimeMinDays"}
			{label}{tip _expected_delivery_time}{/label}
			{textfield name="deliveryTimeMinDays" class="number observed shippingService_deliveryTimeMinDays"}
			<span class="labelIntervalTo">{t _to}</span>
			{textfield name="deliveryTimeMaxDays" class="number observed shippingService_deliveryTimeMinDays"}
		{/input}
	</div>

	{input name="description"}
		{label}{tip _description}{/label}
		{textarea class="observed shippingService_description"}
	{/input}

	<fieldset class="shippingService_rates ">
		<legend>{t _shipping_service_rates}</legend>
		[[ partial("backend/shippingService/rateTable.tpl") ]]

		<fieldset class="error" style="display: none;">
			<ul class="activeList {allowed role='delivery.update'}activeList_add_delete{/allowed} shippingService_ratesList" id="shippingService_ratesList_[[service.DeliveryZone.ID]]_[[service.ID]]"></ul>
			<fieldset class="container" {denied role='delivery.update'}style="display: none"{/denied}>
				<ul class="menu" id="shippingService_rate_menu_[[service.DeliveryZone.ID]]_[[service.ID]]">
					<li style="display:none;" class="addNewRate"><a href="#new_rate" id="shippingService_new_rate_[[service.DeliveryZone.ID]]_[[service.ID]]_show">{t _add_new_rate}</a></li>
					<li style="display:none;" class="addNewRateCancel" style="display: none"><a href="#cancel_rate" id="shippingService_new_rate_[[service.DeliveryZone.ID]]_[[service.ID]]_cancel">{t _cancel_adding_new_rate}</a></li>
				</ul>
			</fieldset>

			<fieldset class="shippingService_new_rate" id="shippingService_new_rate_[[service.DeliveryZone.ID]]_[[service.ID]]_form" style="display: none">
				{include file="backend/shippingService/rate.tpl" rate=$newRate}
			</fieldset>

			<script type="text/jscript">
				{literal}
				Backend.DeliveryZone.ShippingRate.prototype.newRate = {/literal}{json array=$newRate}{literal}

				Event.observe($("shippingService_new_rate_{/literal}[[service.DeliveryZone.ID]]{literal}_{/literal}[[service.ID]]{literal}_show"), "click", function(e)
				{
					e.preventDefault();
					var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(
						$("shippingService_new_rate_{/literal}[[service.DeliveryZone.ID]]{literal}_{/literal}[[service.ID]]{literal}_form"),
						Backend.DeliveryZone.ShippingRate.prototype.newRate
					);

					newForm.showNewForm();
				});

				Backend.DeliveryZone.ShippingRate.prototype.getInstance(
						$("shippingService_new_rate_{/literal}[[service.DeliveryZone.ID]]{literal}_{/literal}[[service.ID]]{literal}_form"),
						Backend.DeliveryZone.ShippingRate.prototype.newRate
					);
				ActiveList.prototype.getInstance("shippingService_ratesList_{/literal}[[service.DeliveryZone.ID]]{literal}_{/literal}[[service.ID]]{literal}", Backend.DeliveryZone.ShippingRate.prototype.Callbacks, function() {});
				{/literal}
			</script>
		</fieldset>
	</fieldset>

	{include file="backend/eav/fields.tpl" item=$service}

	{language}
		{input name="name_`$lang.ID`"}
			{label}{t _name}:{/label}
			{textfield class="observed"}
		{/input}

		{input name="description_`$lang.ID`"}
			{label}{t _description}:{/label}
			{textfield class="observed"}
		{/input}
	{/language}

	<fieldset class="shippingService_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="shippingService_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="shippingService_cancel cancel">{t _cancel}</a>
	</fieldset>
{/form}

{* labels for Backend.DeliveryZone.WeightUnitConventer *}
<span style="display: none" id="RateInputTableLabels">
	<span class="UnitConventer_SwitchToEnglishTitle">{t _switch_to_english_units}</span>
	<span class="UnitConventer_SwitchToMetricTitle">{t _switch_to_metric_units}</span>
	<span class="UnitConventer_MetricHiUnit">{t _units_kilograms}</span>
	<span class="UnitConventer_MetricLoUnit">{t _units_grams}</span>
	<span class="UnitConventer_EnglishHiUnit">{t _units_pounds}</span>
	<span class="UnitConventer_EnglishLoUnit">{t _units_ounces}</span>
	<span class="UnitConventer_MetricHiUnitAbbr">{t _units_kg}</span>
	<span class="UnitConventer_MetricLoUnitAbbr">{t _units_g}</span>
	<span class="UnitConventer_EnglishHiUnitAbbr">{t _units_lbs}</span>
	<span class="UnitConventer_EnglishLoUnitAbbr">{t _units_oz}</span>
</span>