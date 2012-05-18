{form action="controller=backend.currency action=save query=id=`$id`" onsubmit="Backend.Currency.prototype.saveFormat(this); return false;" handle=$form role="currency.update"}

<fieldset class="currencyPriceFormatting">

	<legend>{t _pr_form}</legend>

	{input name="pricePrefix"}
		{label}{t _pr_prefix}{/label}
		{textfield class="currencyPricePrefix"}
	{/input}

	{input name="priceSuffix"}
		{label}{t _pr_suffix}{/label}
		{textfield class="currencyPriceSuffix"}
	{/input}

	{input name="decimalSeparator"}
		{label}{t _pr_decimal_sep}{/label}
		{textfield class="currencyPriceSuffix"}
	{/input}

	{input name="thousandSeparator"}
		{label}{t _pr_thousand_sep}{/label}
		{textfield class="currencyPriceSuffix"}
	{/input}

	{input name="decimalCount"}
		{label}{t _pr_decimal_count}{/label}
		{textfield class="currencyPriceSuffix"}
	{/input}

</fieldset>

<fieldset class="currencyPriceFormatting rounding" id="rounding_{$id}">

	<legend>{t _rounding}</legend>

	<div class="dom_template rangeTemplate">
		<span class="range"></span>
		<input type="text" class="text number range" value="0" />
		<span class="rangeTo">{t _and_more}</span>
		<select>
			<option value="NO_ROUNDING">{t NO_ROUNDING}</option>
			<option value="ROUND">{t ROUND}</option>
			<option value="ROUND_UP">{t ROUND_UP}</option>
			<option value="ROUND_DOWN">{t ROUND_DOWN}</option>
			<option value="TRIM">{t TRIM}</option>
			<option value="TRIM_UP">{t TRIM_UP}</option>
			<option value="TRIM_DOWN">{t TRIM_DOWN}</option>
		</select>
		<input type="text" class="text number precision" value="0.01" />
	</div>

	<div class="rangeContainer"></div>

	<input type="hidden" name="rounding" class="roundingSerialize" />

</fieldset>

<script type="text/javascript">
	new Backend.CurrencyRounding($('rounding_{$id}'), {json array=$currency.rounding});
</script>

<fieldset class="controls">
	<span class="progressIndicator" style="display: none;"></span>
	<input type="submit" value="{tn _save}" class="submit">
	{t _or}
	<a href="#cancel" onclick="this.parentNode.parentNode.parentNode.innerHTML = ''; return false;" class="cancel">{t _cancel}</a>
</fieldset>

{/form}