<fieldset class="pricing">
	<legend>{t _pricing}</legend>

	<div class="row">
	<div class="priceRow">
		{input name="defined.baseCurrency" class="basePrice"}
			{label}{tip baseCurrency _tip_main_currency_price}:{/label}
			<div class="controls">
				{textfield money=true class="money price" noFormat=true}
			</div>
		{/input}

		{input name="definedlistPrice.baseCurrency" class="listPrice"}
			{label}{tip _list_price}:{/label}
			<div class="controls">
				{textfield money=true class="money price" noFormat=true}
			</div>
		{/input}

		[[ partial('backend/product/form/quantityPricing.tpl', ['currency': baseCurrency]) ]]
	</div>
	</div>

	{foreach from=otherCurrencies item="currency"}

		<div class="row">
		<div class="priceRow">
			{input name="defined.currency" class="basePrice"}
				{label}{tip currency _tip_secondary_currency_price}:{/label}
				<div class="controls">
					{textfield money=true class="money price" noFormat=true}
				</div>
			{/input}

			{input name="definedlistPrice.currency" class="listPrice"}
				{label}{tip _list_price}:{/label}
				<div class="controls">
					{textfield money=true class="money price" noFormat=true}
				</div>
			{/input}

			[[ partial('backend/product/form/quantityPricing.tpl', ['currency': currency]) ]]
		</div>
		</div>

	{% endfor %}
</fieldset>