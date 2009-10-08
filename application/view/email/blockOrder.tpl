{if !$html}
{include file="email/blockOrderItems.tpl"}

{t _subtotal|@str_pad_left:49}: {$order.formatted_itemSubtotalWithoutTax}
{if $order.formatted_shippingSubtotal}
{t _shipping|@str_pad_left:49}: {$order.formatted_shippingSubtotal}
{/if}
{if $order.taxes[$order.Currency.ID]}
{''|@str_pad_left:27}---------------------------------
{t _subtotal_before_tax|@str_pad_left:49}: {$order.formatted_subtotalBeforeTaxes}
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
{$tax.name_lang|@str_pad_left:49}: {$tax.formattedAmount}
{/foreach}
{/if}
{''|@str_pad_left:27}---------------------------------
{t _grand_total|@str_pad_left:49}: {$order.formattedTotal[$order.Currency.ID]}
{t _amount_paid|@str_pad_left:49}: {$order.formatted_amountPaid}
{if $order.amountDue}
{t _amount_due|@str_pad_left:49}: {$order.formatted_amountDue}
{/if}
{/if}{*html*}
{if $html}
<table border="1">
{include file="email/blockOrderItems.tpl" noTable=true}

<tr><td colspan="3">{t _subtotal}</td><td align="right">{$order.formatted_itemSubtotalWithoutTax}</td></tr>
{if $order.formatted_shippingSubtotal}
	{if $order.shipments|@count == 1}
		{include file="email/blockShippingCost.tpl" shipment=$order.shipments.0}
	{else}
		<tr><td colspan="3">{t _shipping}</td><td align="right">{$order.formatted_shippingSubtotal}</td></tr>
	{/if}
{/if}
{if $order.taxes[$order.Currency.ID]}
<tr><td colspan="3">{t _subtotal_before_tax}</td><td align="right">{$order.formatted_subtotalBeforeTaxes}</td></tr>
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
<tr><td colspan="3">{$tax.name_lang}</td><td align="right">{$tax.formattedAmount}</td></tr>
{/foreach}
{/if}
<tr><td colspan="3">{t _grand_total}</td><td align="right"><b>{$order.formattedTotal[$order.Currency.ID]}</b></td></tr>
<tr><td colspan="3">{t _amount_paid}</td><td align="right">{$order.formatted_amountPaid}</td></tr>
{if $order.amountDue}
<tr><td colspan="3">{t _amount_due}</td><td align="right">{$order.formatted_amountDue}</td></tr>
{/if}
</table>
{/if}{*html*}