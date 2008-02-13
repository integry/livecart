{include file="email/blockOrderItems.tpl"}
{t _subtotal|@str_pad:48}: {$order.formatted_itemSubtotal}
{if $order.shippingSubtotal}
{t _shipping|@str_pad:48}: {$order.formatted_shippingSubtotal}
{/if}
{if $order.taxes|@count > 1}
						   ---------------------------------
{t _before_tax|@str_pad:48}: {$order.formatted_subtotalBeforeTaxes}
{foreach from=$order.taxes[$order.Currency.ID] item=tax}
{$tax.name_lang|@str_pad:48}: {$tax.formattedAmount}
{/foreach}
{/if}
						   ---------------------------------
{t _grand_total|@str_pad:48}: {$order.formatted_totalAmount}
{t _amount_paid|@str_pad:48}: {$order.formatted_amountPaid}
{t _amount_due|@str_pad:48}: {$order.formatted_amountDue}