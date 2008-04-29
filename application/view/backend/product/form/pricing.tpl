<fieldset class="pricing">
	<legend>{t _pricing}</legend>
	<p class="required">
		<label for="product_price_{$cat}_{$product.ID}_{$baseCurrency}">{$baseCurrency}:</label>
		<fieldset class="error">
			{textfield name="price_$baseCurrency" class="money price" id="product_price_`$cat`_`$product.ID`_`$baseCurrency`"} {$baseCurrency}
			<span class="listPrice">
				{t _list_price}:
				{textfield name="listPrice_$baseCurrency" class="money" id="product_listPrice_`$cat`_`$product.ID`_`$baseCurrency`"}
			</span>
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	{foreach from=$otherCurrencies item="currency"}
	<p>
		<label for="product_price_{$cat}_{$product.ID}_{$currency}">{$currency}:</label>
		<fieldset class="error">
			{textfield name="price_$currency" class="money price" id="product_price_`$cat`_`$product.ID`_`$currency`"} {$currency}
			<span class="listPrice">
				{t _list_price}:
				{textfield name="listPrice_$currency" class="money" id="product_listPrice_`$cat`_`$product.ID`_`$currency`"}
			</span>
			<div class="errorText hidden"></div>
		</fieldset>
	</p>
	{/foreach}
</fieldset>