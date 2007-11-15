{if $currencies|@count > 1}
	<div id="currency">
		{foreach from=$currencies item="currency"}
			<a href="{$currency.url}">{$currency.ID}</a>
		{/foreach}
	</div>
{/if}