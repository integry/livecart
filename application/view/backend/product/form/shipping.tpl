<fieldset class="shipping">
	<legend>{t _shipping}</legend>

	<p class="checkbox">
		{checkbox name="isSeparateShipment" class="checkbox" id="product_issep_`$cat`_`$product.ID`"}
		<label for="product_issep_{$cat}_{$product.ID}" class="checkbox">{tip _requires_separate_shipment}</label>
	</p>
	<p class="checkbox">
		{checkbox name="isFreeShipping" class="checkbox" id="product_isFreeShipping_`$cat`_`$product.ID`"}
		<label class="checkbox" for="product_isFreeShipping_{$cat}_{$product.ID}">{tip _qualifies_for_free_shipping}</label>
	</p>
	<p class="checkbox">
		{checkbox name="isBackOrderable" class="checkbox" id="product_isBackOrderable_`$cat`_`$product.ID`"}
		<label for="product_isBackOrderable_{$cat}_{$product.ID}">{tip _allow_back_ordering}</label>
	</p>
	<p class="checkbox">
		<label></label>
		{checkbox name="isFractionalUnit" class="checkbox" id="product_`$cat`_`$product.ID`_isFractionalUnit"}
		<label for="product_{$cat}_{$product.ID}_isFractionalUnit" class="checkbox">{tip _allow_fractional_quantities}</label>
	</p>

	<p>
		<label for="product_shippingWeight_{$cat}_{$product.ID}">{tip _shipping_weight}:</label>
		<fieldset class="error" >

			{metricsfield name="shippingWeight"}

			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	<p>
		<label for="product_shippingSurcharge_{$cat}_{$product.ID}">{tip _shipping_surcharge}:</label>
		<fieldset class="error">
			{textfield name="shippingSurchargeAmount" id="product_shippingSurcharge_`$cat`_`$product.ID`" class="number"} {$baseCurrency}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	<p>
		<label for="product_minimumQuantity_{$cat}_{$product.ID}">{tip _minimum_order_quantity}:</label>
		<fieldset class="error">
			{textfield name="minimumQuantity" id="product_minimumQuantity_`$cat`_`$product.ID`" class="number"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	<p>
		<label for="product_{$cat}_{$product.ID}_fractionalStep">{tip _fractionalStep _hint_fractionalStep}:</label>
		<fieldset class="error">
			{textfield name="fractionalStep" class="number" id="product_`$cat`_`$product.ID`_fractionalStep"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>

</fieldset>