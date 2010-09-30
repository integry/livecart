<fieldset class="pricing">
	<legend>{t _pricing}</legend>
	<p class="required">
		<label for="product_price_{$cat}_{$product.ID}_{$baseCurrency}">{tip $baseCurrency _tip_main_currency_price}:</label>
		<fieldset class="error">
			{textfield name="price_$baseCurrency" class="money price" id="product_price_`$cat`_`$product.ID`_`$baseCurrency`"} {$baseCurrency}
			<span class="listPrice">
				{tip _list_price}:
				{textfield name="listPrice_$baseCurrency" class="money" id="product_listPrice_`$cat`_`$product.ID`_`$baseCurrency`"}
			</span>
			<a href="" class="menu setQuantPrice" style="display: none;">{t _set_quant}</a>
			<div class="errorText hidden"></div>

			{include file="backend/product/form/quantityPricing.tpl" currency=$baseCurrency}

		</fieldset>
	</p>
	{foreach from=$otherCurrencies item="currency"}
	<p>
		<label for="product_price_{$cat}_{$product.ID}_{$currency}">{tip $currency _tip_secondary_currency_price}:</label>
		<fieldset class="error">
			{textfield name="price_$currency" class="money price" id="product_price_`$cat`_`$product.ID`_`$currency`"} {$currency}
			<span class="listPrice">
				{tip _list_price}:
				{textfield name="listPrice_$currency" class="money" id="product_listPrice_`$cat`_`$product.ID`_`$currency`"}
			</span>
			<a href="" class="menu setQuantPrice" style="display: none;">{t _set_quant}</a>
			<div class="errorText hidden"></div>
			{include file="backend/product/form/quantityPricing.tpl" currency=$currency}
		</fieldset>
	</p>
	{/foreach}
</fieldset>