<fieldset class="shipping" ng-show="product.type != 1">
	<legend>{t _shipping}</legend>

	{input name="isSeparateShipment"}
		{checkbox}
		{label}{tip _requires_separate_shipment}{/label}
	{/input}

	{input name="isFreeShipping"}
		{checkbox}
		{label}{tip _qualifies_for_free_shipping}{/label}
	{/input}

	{input name="isBackOrderable"}
		{checkbox}
		{label}{tip _allow_back_ordering}{/label}
	{/input}

	{input name="isFractionalUnit"}
		{checkbox}
		{label}{tip _allow_fractional_quantities}{/label}
	{/input}

	{input name="shippingWeight"}
		{label}{tip _shipping_weight}:{/label}
		{metricsfield name="shippingWeight"}
	{/input}

	{input name="shippingSurchargeAmount"}
		{label}{tip _shipping_surcharge}:{/label}
		{control}{textfield class="number" noFormat=true} {$baseCurrency}{/control}
	{/input}

	{input name="minimumQuantity"}
		{label}{tip _minimum_order_quantity}:{/label}
		{textfield class="number"}
	{/input}

	{input name="fractionalStep"}
		{label}{tip _fractionalStep _hint_fractionalStep}:{/label}
		{textfield class="number"}
	{/input}

</fieldset>