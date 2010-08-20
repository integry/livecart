{form id="shippingService_`$service.DeliveryZone.ID`_`$service.ID`" handle=$form action="controller=backend.deliveryZone action=update id=`$service.DeliveryZone.ID`" method="post" role="delivery.update"}
	<input type="hidden" name="deliveryZoneID" value="{$service.DeliveryZone.ID}" />
	<input type="hidden" name="serviceID" value="{$service.ID}" />

	<label for="shippingService_{$service.DeliveryZone.ID}_{$service.ID}_name">{t _name}</label>
	<fieldset class="error">
		{textfield name="name" class="observed shippingService_name" id="shippingService_`$service.DeliveryZone.ID`_`$service.ID`_name"}
		<span class="errorText hidden"> </span>
	</fieldset>

	<fieldset class="error">
		<label></label>
		{checkbox name="isFinal" class="checkbox observed shippingService_isFinal" id="shippingService_`$service.DeliveryZone.ID`_`$service.ID`_isFinal"}
		<label for="shippingService_{$service.DeliveryZone.ID}_{$service.ID}_isFinal" class="checkbox wide">{t _disable_other_services}</label>
	</fieldset>

	<fieldset class="error rangeType">
		<label ></label>
		{radio name="rangeType" id="shippingService_`$service.DeliveryZone.ID`_`$service.ID`_weight" class="checkbox shippingService_rangeType" value="0"}
		<label for="shippingService_{$service.DeliveryZone.ID}_{$service.ID}_weight" class="checkbox">{t _weight_based_calculations}</label>
	</fieldset>

	<fieldset class="error rangeType">
		<label></label>
		{radio name="rangeType" id="shippingService_`$service.DeliveryZone.ID`_`$service.ID`_subtotal" class="checkbox shippingService_rangeType" value="1"}
		<label for="shippingService_{$service.DeliveryZone.ID}_{$service.ID}_subtotal" class="checkbox">{t _subtotal_based_calculations}</label>
	</fieldset>

	<fieldset class="error rangeTypeStatic">
		<label>{t _range_type}</label>
		<label style="width: auto;">{if $service.rangeType == 0}{t _weight_based_calculations}{else}{t _subtotal_based_calculations}{/if}</label>
	</fieldset>

	<fieldset class="shippingService_rates ">
		{include file="backend/shippingService/rateTable.tpl"}
	
		
		<fieldset class="error">

			<ul class="activeList {allowed role='delivery.update'}activeList_add_delete{/allowed} shippingService_ratesList" id="shippingService_ratesList_{$service.DeliveryZone.ID}_{$service.ID}">

			
		{*
				{foreach from=$shippingRates item="rate"}
					<li id="shippingService_ratesList_{$service.DeliveryZone.ID}_{$service.ID}_{$rate.ID}">
						{include file="backend/shippingService/rate.tpl" rate=$rate}
						<script type="text/jscript">
						{literal}
							Backend.DeliveryZone.ShippingRate.prototype.getInstance(
								"{/literal}shippingService_ratesList_{$service.DeliveryZone.ID}_{$service.ID}_{$rate.ID}{literal}",
								{/literal}{json array=$rate}{literal}
							);
							ActiveList.prototype.getInstance("shippingService_ratesList_{/literal}{$service.DeliveryZone.ID}{literal}_{/literal}{$service.ID}{literal}", Backend.DeliveryZone.ShippingRate.prototype.Callbacks, function() {});
						{/literal}
						</script>
					</li>
				{/foreach}
		*}

			</ul>
		
			<fieldset class="container" {denied role='delivery.update'}style="display: none"{/denied}>
				<ul class="menu" id="shippingService_rate_menu_{$service.DeliveryZone.ID}_{$service.ID}">
					<li style="display:none;" class="addNewRate"><a href="#new_rate" id="shippingService_new_rate_{$service.DeliveryZone.ID}_{$service.ID}_show">{t _add_new_rate}</a></li>
					<li style="display:none;" class="addNewRateCancel" style="display: none"><a href="#cancel_rate" id="shippingService_new_rate_{$service.DeliveryZone.ID}_{$service.ID}_cancel">{t _cancel_adding_new_rate}</a></li>

				</ul>
			</fieldset>
			
			<fieldset class="shippingService_new_rate" id="shippingService_new_rate_{$service.DeliveryZone.ID}_{$service.ID}_form" style="display: none">
				{include file="backend/shippingService/rate.tpl" rate=$newRate}
			</fieldset>
		
			<script type="text/jscript">
				{literal}
				Backend.DeliveryZone.ShippingRate.prototype.newRate = {/literal}{json array=$newRate}{literal}

				Event.observe($("shippingService_new_rate_{/literal}{$service.DeliveryZone.ID}{literal}_{/literal}{$service.ID}{literal}_show"), "click", function(e)
				{
					Event.stop(e);
					var newForm = Backend.DeliveryZone.ShippingRate.prototype.getInstance(
						$("shippingService_new_rate_{/literal}{$service.DeliveryZone.ID}{literal}_{/literal}{$service.ID}{literal}_form"),
						Backend.DeliveryZone.ShippingRate.prototype.newRate
					);

					newForm.showNewForm();
				});

				Backend.DeliveryZone.ShippingRate.prototype.getInstance(
						$("shippingService_new_rate_{/literal}{$service.DeliveryZone.ID}{literal}_{/literal}{$service.ID}{literal}_form"),
						Backend.DeliveryZone.ShippingRate.prototype.newRate
					);
				ActiveList.prototype.getInstance("shippingService_ratesList_{/literal}{$service.DeliveryZone.ID}{literal}_{/literal}{$service.ID}{literal}", Backend.DeliveryZone.ShippingRate.prototype.Callbacks, function() {});
				{/literal}
			</script>
		</fieldset>
	</fieldset>

	{language}
		<label for="shippingService_{$service.DeliveryZone.ID}_{$service.ID}_name_{$lang.ID}">{t _name}</label>
		<fieldset class="error">
			{textfield name="name_`$lang.ID`" class="observed" id="shippingService_`$service.DeliveryZone.ID`_`$service.ID`_name_`$lang.ID`"}
			<span class="errorText hidden"> </span>
		</fieldset>
	{/language}

	<fieldset class="shippingService_controls controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="shippingService_save button submit" value="{t _save}" />
		{t _or}
		<a href="#cancel" class="shippingService_cancel cancel">{t _cancel}</a>
	</fieldset>
{/form}

{* labels for Backend.DeliveryZone.WeightUnitConventer *}
<span style="display: none">
	<span class="UnitConventer_SwitchToEnglishTitle">{t _switch_to_english_units}</span>
	<span class="UnitConventer_SwitchToMetricTitle">{t _switch_to_metric_units}</span>
	<span class="UnitConventer_MetricHiUnit">{t _units_kg}</span>
	<span class="UnitConventer_MetricLoUnit">{t _units_g}</span>
	<span class="UnitConventer_EnglishHiUnit">{t _units_pounds}</span>
	<span class="UnitConventer_EnglishLoUnit">{t _units_ounces}</span>
</span>
