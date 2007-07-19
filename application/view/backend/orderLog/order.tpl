<dl class="{if $order.ID !== $otherOrder.ID}logValueChanged{/if}">
    <dt>{t _order_id}:</dt>
    <dd>{$order.ID}</dd>
</dl>

<dl class="{if $order.status !== $otherOrder.status}logValueChanged{/if}">
    <dt>{t _status}:</dt>
    <dd>
        {if $order.status == 0}{t _new}
        {elseif $order.status == 1}{t _backordered}
        {elseif $order.status == 2}{t _awaiting_shipment}
        {elseif $order.status == 3}{t _shipped}
        {elseif $order.status == 4}{t _returned}{/if} 
    </dd>
</dl>

<dl class="{if $order.isCancelled !== $otherOrder.isCancelled}logValueChanged{/if}">
    <dt>{t _canceled}:</dt>
    <dd>
        {if $order.isCancelled == 0}{t _false}
        {elseif $order.isCancelled == 1}{t _true}{/if}
    </dd>
</dl>