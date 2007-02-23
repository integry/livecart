<fieldset>
	<legend>Pricing</legend>
	<p class="required">
		<label for="product_price_{$cat}_{$product.ID}_{$baseCurrency}">{$baseCurrency}:</label>
		<fieldset class="error">			
			{textfield name="price_$baseCurrency" class="money" id="product_price_`$cat`_`$product.ID`_`$baseCurrency`"}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	{foreach from=$otherCurrencies item="currency"}
	<p>
		<label for="product_price_{$cat}_{$product.ID}_{$currency}">{$currency}:</label>
		<fieldset class="error">				
			{textfield name="price_$currency" class="money" id="product_price_`$cat`_`$product.ID`_`$currency`"} {$currency}
			<div class="errorText hidden"></div>
		</fieldset>
	</p>		
	{/foreach}
</fieldset>