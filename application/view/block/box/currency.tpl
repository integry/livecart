{if $currencies}
	<div id="currency" class="btn-group">
		{foreach from=$allCurrencies item="currency"}
			<a class="btn btn-mini {if $currency.ID == $current}btn-info{/if}" href="{$currency.url}">{$currency.ID}</a>
		{/foreach}
	</div>
{/if}