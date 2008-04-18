{include file="email/blockOrderItems.tpl"}

{t _subtotal|@str_pad_left:49}: {$order.formatted_itemSubtotal}
{if $order.shippingSubtotal}
{t _shipping|@str_pad_left:49}: {$order.formatted_shippingSubtotal}
{/if}
{if $order.taxes|@count > 0}
						   ---------------------------------
{t _subtotal_before_tax|@str_pad_left:49}: {$order.formatted_subtotalBeforeTaxes}
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
{$tax.name_lang|@str_pad_left:49}: {$tax.formattedAmount}
{/foreach}
{/if}
						   ---------------------------------
{t _grand_total|@str_pad_left:49}: {$order.formattedTotal[$order.Currency.ID]}
{t _amount_paid|@str_pad_left:49}: {$order.formatted_amountPaid}
{t _amount_due|@str_pad_left:49}: {$order.formatted_amountDue}