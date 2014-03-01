<div class="box currencies">
	<div class="title">
		<div>{t _switch_currency}</div>
	</div>

	<div class="content">
		<select onchange="window.location.href = this.value" style="width: 100%">
		{foreach from=allCurrencies item="currency"}
			<option value="[[currency.url]]"{% if currentCurrency.ID == currency.ID %} selected="selected"{% endif %}>[[currency.name]]</option>
		{% endfor %}
		</select>
	</div>
</div>