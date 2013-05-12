<fieldset class="pricing">
	<legend>{t _pricing}</legend>

	<div class="priceRow">
		{input name="price_$baseCurrency" class="basePrice"}
			{label}{tip $baseCurrency _tip_main_currency_price}:{/label}
			<div class="controls">
				{textfield class="money price" noFormat=true} {$baseCurrency}
			</div>
		{/input}

		{input name="listPrice_$baseCurrency"}
			{label}{tip _list_price}:{/label}
			<div class="controls">
				{textfield class="money price" noFormat=true} {$baseCurrency}
			</div>
		{/input}

		<a href="#" class="menu setQuantPrice" style="display: none;">{t _set_quant}</a>
		{include file="backend/product/form/quantityPricing.tpl" currency=$baseCurrency}
	</div>

	{foreach from=$otherCurrencies item="currency"}

		<div class="priceRow">
			{input name="price_$currency" class="basePrice"}
				{label}{tip $currency _tip_secondary_currency_price}:{/label}
				<div class="controls">
					{textfield class="money price" noFormat=true} {$currency}
				</div>
			{/input}

			{input name="listPrice_$currency"}
				{label}{tip _list_price}:{/label}
				<div class="controls">
					{textfield class="money price" noFormat=true} {$currency}
				</div>
			{/input}

			<a href="#" class="menu setQuantPrice" style="display: none;">{t _set_quant}</a>
			{include file="backend/product/form/quantityPricing.tpl" currency=$currency}
		</div>

	{/foreach}
</fieldset>