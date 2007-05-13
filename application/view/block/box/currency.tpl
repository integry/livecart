<div class="currency">
    {foreach from=$currencies item="currency"}
        <a href="{$currency.url}">{$currency.ID}</a>
    {/foreach}
</div>