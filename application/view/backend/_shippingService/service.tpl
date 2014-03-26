{form id="shippingService_`service.DeliveryZone.ID`_`service.ID`" handle=form action="controller=backend.deliveryZone action=update id=`service.DeliveryZone.ID`" method="post" role="delivery.update"}
	<input type="hidden" name="deliveryZoneID" value="[[service.DeliveryZone.ID]]" />
	<input type="hidden" name="serviceID" value="[[service.ID]]" />

	<div class="expectedDeliveryInterval">
		{input name="deliveryTimeMinDays"}
		{/input}
	</div>

	

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
				[[ partial('backend/shippingService/rate.tpl', ['rate': newRate]) ]]
			</fieldset>

			<script type="text/jscript">

				Backend.DeliveryZone.ShippingRate.prototype.newRate = {json array=newRate}

				Event.observe(("shippingService_new_rate_[[service.DeliveryZone.ID]]_[[service.ID]]_show"), "click", function(e)
				{
					e.preventDefault();
					var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(
						("shippingService_new_rate_[[service.DeliveryZone.ID]]_[[service.ID]]_form"),
						Backend.DeliveryZone.ShippingRate.prototype.newRate
					);

					newForm.showNewForm();
				});

				Backend.DeliveryZone.ShippingRate.prototype.getInstance(
						("shippingService_new_rate_[[service.DeliveryZone.ID]]_[[service.ID]]_form"),
						Backend.DeliveryZone.ShippingRate.prototype.newRate
					);
				ActiveList.prototype.getInstance("shippingService_ratesList_[[service.DeliveryZone.ID]]_[[service.ID]]", Backend.DeliveryZone.ShippingRate.prototype.Callbacks, function() {});

			</script>
		</fieldset>
	</fieldset>

	[[ partial('backend/eav/fields.tpl', ['item': service]) ]]

	{language}
		[[ textfld('name_`lang.ID`', '_name', class: 'observed') ]]

		[[ textfld('description_`lang.ID`', '_description', class: 'observed') ]]
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
