{if !$html}
------------------------
{'STORE_NAME'|config}
{link url=true}
{/if}{*html*}
{if $html}
<hr /><a href="{link url=true}">{'STORE_NAME'|config}</a>
{/if}{*html*}