{if $order.isCancelled}
	<span class="cancelled">{t _cancelled}</span>
{elseif !$order.isPaid}
	<span class="awaitingPayment">{t _awaiting_payment} 
    <strong>{$order.formattedTotal[$order.Currency.ID]}</strong></span>.
	{t _make_payment}.
{else}
	{if $order.isReturned}
	    <span class="returned">{t _returned}</span>
	{elseif $order.isShipped}
	    <span class="mailed">{t _shipped}</span>
	{elseif $order.isAwaitingShipment}
	    <span class="mailed">{t _awaiting}</span>
	{else}
	    <span class="processing">{t _order_processing}</span>
	{/if}
{/if}