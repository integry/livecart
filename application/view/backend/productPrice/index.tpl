Discounts

product #{$id} in category #{$categoryID}


{form handle=$pricingFormm action="controller=backend.productPrice action=save id=`$product.ID`" method="POST" onsubmit="Backend.Product.saveForm(this); return false;" onreset="Backend.Product.resetAddForm(this);"}

<fieldset>
	<legend>Pricing</legend>
	<p class="required">
		<label for="pricebase_addproduct_{$cat}">Price:</label>
		<fieldset class="error">			
			{textfield name="price_$baseCurrency" class="money" id="pricebase_addproduct_`$cat`"} {$baseCurrency}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	{foreach from=$otherCurrencies item="currency"}
	<p>
		<label for="pricebase_addproduct_{$currency}_{$cat}">Price:</label>
		<fieldset class="error">				
			{textfield name="price_$currency" class="money" id="pricebase_addproduct_`$currency`_`$cat`"} {$currency}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>		
	{/foreach}
</fieldset>
       
{/form}