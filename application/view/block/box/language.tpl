<div id="language">
    {foreach from=$languages item="language"}
        <a href="{$language.url}">{$language.originalName}</a>
    {/foreach}
</div>