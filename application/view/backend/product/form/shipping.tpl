<fieldset>
	<legend>Shipping</legend>

	<p style="color:red;">
		<label for="product_shippingWeight_{$cat}_{$product.ID}">Shipping Weight:</label>
		<fieldset class="error">				
			
			{textfield name="shippingHiUnit" onkeyup="Backend.Product.updateShippingWeight(this);" class="number" id="product_shippingWeight_`$cat`_`$product.ID`"} <span class="shippingUnit_hi">kg</span>
			{textfield name="shippingLoUnit" onkeyup="Backend.Product.updateShippingWeight(this);" class="number"} <span class="shippingUnit_lo">g</span>
			
			<span class="unitSwitch">
				<span class="unitDef english_title">Switch to English units</span>
				<span class="unitDef metric_title">Switch to Metric units</span>
				<span class="unitDef english_hi">kg</span>
				<span class="unitDef english_lo">g</span>
				<span class="unitDef metric_hi">pounds</span>
				<span class="unitDef metric_lo">ounces</span>
														
				<a href="#" onclick="Backend.Product.switchUnitTypes(this); return false;">Switch to English units</a>
			</span>
			
			{hidden name="shippingWeight"}
			{hidden name="unitsType"}
			
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	<p>
		<label for="product_minimumQuantity_{$cat}_{$product.ID}">Minimum Order Quantity:</label>
		<fieldset class="error">		
			{textfield name="minimumQuantity" id="product_minimumQuantity_`$cat`_`$product.ID`" class="number"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	<p>
		<label for="product_shippingSurcharge_{$cat}_{$product.ID}">Shipping Surcharge:</label>
		<fieldset class="error">	
			{textfield name="shippingSurchargeAmount" id="product_shippingSurcharge_`$cat`_`$product.ID`" class="number"} {$baseCurrency}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	<p class="checkbox">			
		{checkbox name="isSeparateShipment" class="checkbox" id="product_issep_`$cat`_`$product.ID`" value="on"}
		<label for="product_issep_{$cat}_{$product.ID}" class="checkbox"> Requires separate shipment</label>
	</p>
	<p class="checkbox">			
		{checkbox name="isFreeShipping" class="checkbox" id="product_isFreeShipping_`$cat`_`$product.ID`" value="on"}
		<label class="checkbox" for="product_isFreeShipping_{$cat}_{$product.ID}"> Qualifies for free shipping</label>
	</p>
	<p class="checkbox">			
		{checkbox name="isBackOrderable" class="checkbox" value="on" id="product_isBackOrderable_`$cat`_`$product.ID`"}
        <label for="product_isBackOrderable_{$cat}_{$product.ID}"> Allow back-ordering</label>
	</p>
</fieldset>