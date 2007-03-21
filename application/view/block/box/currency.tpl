<div class="box currency">
	<div class="title">
		<div>{t _switch_currency}</div>
	</div>

	<div class="content">
	    {foreach from=$currencies item="currency"}
	        <a href="{$currency.url}">{$currency.ID}</a>
	    {/foreach}
	</div>
</div>