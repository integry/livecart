{if $shipment.isReturned}
    <span class="returned">{t Returned}</span>
{elseif $shipment.isShipped}
    <span class="mailed">{t Shipped}</span>
{elseif $shipment.isAwaitingShipment}
    <span class="mailed">{t Awaiting Shipment}</span>
{else}
    <span class="processing">{t Processing}</span>
{/if}