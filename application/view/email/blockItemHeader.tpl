{if !$html}
------------------------------------------------------------
{t _product|@str_pad_iconv:25}{t _price|@str_pad_iconv:11}{t _qty|@str_pad_iconv:9}{t _subtotal}
------------------------------------------------------------
{/if}{*html*}
{if $html}
{if !$noTable}<table>{/if}
<thead>
<tr>
	<th>{t _product}</th><th>{t _price}</th><th>{t _qty}</th><th>{t _subtotal}</th>
</tr>
</thead>
{/if}{*html*}