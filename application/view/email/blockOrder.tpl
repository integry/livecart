{if 'SHOW_SKU_EMAIL'|config}{% set SHOW_SKU = true %}{/if}{if !$html}
{include file="email/blockOrderItems.tpl"}

{if $order.taxes[$order.Currency.ID] && !'HIDE_TAXES'|config}
{t _subtotal|@str_pad_left:49}: [[order.formatted_itemSubtotalWithoutTax]]
{/if}
{if $order.formatted_shippingSubtotal}
{if $SHOW_SKU}{''|@str_pad_left:10}{/if}{t _shipping|@str_pad_left:49}: [[order.formatted_shippingSubtotal]]
{/if}
{if $order.taxes[$order.Currency.ID] && !'HIDE_TAXES'|config}
{if $SHOW_SKU}{''|@str_pad_left:10}{/if}{''|@str_pad_left:27}---------------------------------
{t _subtotal_before_tax|@str_pad_left:49}: [[order.formatted_subtotalBeforeTaxes]]
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
{if $SHOW_SKU}{''|@str_pad_left:10}{/if}{$tax.name_lang|@str_pad_left:49}: [[tax.formattedAmount]]
{/foreach}
{/if}
{foreach from=$order.discounts item=discount}
{if $discount.amount != 0}
{if $SHOW_SKU}{''|@str_pad_left:10}{/if}{$discount.description|@str_pad_left:49}: [[discount.formatted_amount]]{/if}
{/foreach}
{if $SHOW_SKU}{''|@str_pad_left:10}{/if}{''|@str_pad_left:27}---------------------------------
{if $SHOW_SKU}{''|@str_pad_left:10}{/if}{t _grand_total|@str_pad_left:49}: {$order.formattedTotal[$order.Currency.ID]}
{if $SHOW_SKU}{''|@str_pad_left:10}{/if}{t _amount_paid|@str_pad_left:49}: [[order.formatted_amountPaid]]
{if $order.amountDue}
{if $SHOW_SKU}{''|@str_pad_left:10}{/if}{t _amount_due|@str_pad_left:49}: [[order.formatted_amountDue]]
{/if}
{/if}{*html*}
{if $html}
<table border="1">
{include file="email/blockOrderItems.tpl" noTable=true}

{if $order.taxes[$order.Currency.ID] && !'HIDE_TAXES'|config}
<tr><td colspan="{if $SHOW_SKU}4{else}3{/if}">{t _subtotal}</td><td align="right">[[order.formatted_itemSubtotalWithoutTax]]</td></tr>
{/if}
{if $order.formatted_shippingSubtotal}
	{if $order.shipments|@count == 1}
		{include file="email/blockShippingCost.tpl" shipment=$order.shipments.0}
	{else}
		<tr><td colspan="{if $SHOW_SKU}4{else}3{/if}">{t _shipping}</td><td align="right">[[order.formatted_shippingSubtotal]]</td></tr>
	{/if}
{/if}
{if $order.taxes[$order.Currency.ID] && !'HIDE_TAXES'|config}
<tr><td colspan="{if $SHOW_SKU}4{else}3{/if}">{t _subtotal_before_tax}</td><td align="right">[[order.formatted_subtotalBeforeTaxes]]</td></tr>
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
<tr><td colspan="{if $SHOW_SKU}4{else}3{/if}">[[tax.name_lang]]</td><td align="right">[[tax.formattedAmount]]</td></tr>
{/foreach}
{/if}
{foreach from=$order.discounts item=discount}
{if $discount.amount != 0}
<tr><td colspan="{if $SHOW_SKU}4{else}3{/if}">[[discount.description]]</td><td align="right">[[discount.formatted_amount]]</td></tr>
{/if}
{/foreach}
<tr><td colspan="{if $SHOW_SKU}4{else}3{/if}">{t _grand_total}</td><td align="right"><b>{$order.formattedTotal[$order.Currency.ID]}</b></td></tr>
<tr><td colspan="{if $SHOW_SKU}4{else}3{/if}">{t _amount_paid}</td><td align="right">[[order.formatted_amountPaid]]</td></tr>
{if $order.amountDue}
<tr><td colspan="{if $SHOW_SKU}4{else}3{/if}">{t _amount_due}</td><td align="right">[[order.formatted_amountDue]]</td></tr>
{/if}
</table>
{/if}{*html*}