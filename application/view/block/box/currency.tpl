{% if $currencies %}
	<div id="currency" class="btn-group">
		{foreach from=$allCurrencies item="currency"}
			<a class="btn btn-small {% if $currency.ID == $current %}btn-info{% else %}btn-default{% endif %}" href="[[currency.url]]">[[currency.ID]]</a>
		{/foreach}
	</div>
{% endif %}