<div style="float: right; font-size: smaller;">
    {foreach from=$currencies item="currency"}
        <a href="{$currency.url}">{$currency.ID}</a>
    {/foreach}
</div>