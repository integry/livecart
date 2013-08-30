<fieldset class="shipping" ng-show="product.type != 1">
	<legend>{t _shipping}</legend>

	[[ checkbox('isSeparateShipment', tip('_requires_separate_shipment')) ]]

	[[ checkbox('isFreeShipping', tip('_qualifies_for_free_shipping')) ]]

	[[ checkbox('isBackOrderable', tip('_allow_back_ordering')) ]]

	[[ checkbox('isFractionalUnit', tip('_allow_fractional_quantities')) ]]

	{input name="shippingWeight"}
		{label}{tip _shipping_weight}:{/label}
		{metricsfield name="shippingWeight"}
	{/input}

	{input name="shippingSurchargeAmount"}
		{label}{tip _shipping_surcharge}:{/label}
		{control}{textfield class="number" money=true noFormat=true} [[baseCurrency]]{/control}
	{/input}

	{input name="minimumQuantity"}
		{label}{tip _minimum_order_quantity}:{/label}
		{textfield class="number" number="float"}
	{/input}

	{input name="fractionalStep"}
		{label}{tip _fractionalStep _hint_fractionalStep}:{/label}
		{textfield class="number" number="float"}
	{/input}

</fieldset>