{if 'SHOW_SKU_EMAIL'|config}{% set SHOW_SKU = true %}{/if}
{if !$html}
{if $SHOW_SKU}----------{/if}------------------------------------------------------------
{if $SHOW_SKU}{t _sku|@str_pad_iconv:10}{/if}{t _product|@str_pad_iconv:25}{t _price|@str_pad_iconv:11}{t _qty|@str_pad_iconv:9}{t _subtotal}
{if $SHOW_SKU}----------{/if}------------------------------------------------------------
{/if}{*html*}
{if $html}
{if !$noTable}<table>{/if}
<thead>
<tr>
	{if $SHOW_SKU}<th>{t _sku}</th>{/if}<th>{t _product}</th><th>{t _price}</th><th>{t _qty}</th><th>{t _subtotal}</th>
</tr>
</thead>
{/if}{*html*}