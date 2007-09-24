{if $order.isCancelled}
	<span class="cancelled">{t Cancelled}</span>
{elseif !$order.isPaid}
	<span class="awaitingPayment">{t Awaiting payment} 
    <strong>{$order.formattedTotal[$order.Currency.ID]}</strong></span>.
	{t Make payment for this order}.	    
{else}
	{if $order.isReturned}
	    <span class="returned">{t Returned}</span>
	{elseif $order.isShipped}
	    <span class="mailed">{t Shipped}</span>
	{elseif $order.isAwaitingShipment}
	    <span class="mailed">{t Awaiting Shipment}</span>
	{else}
	    <span class="processing">{t The order is being processed}</span>
	{/if}
{/if}