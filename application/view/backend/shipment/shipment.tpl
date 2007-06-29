<form>
    {if $shipment.status != 3 && !$notShippable}
        {include file="backend/shipment/shipmentControls.tpl"}
    {/if}

    <ul id="orderShipmentsItems_list_{$orderID}_{$shipment.ID}" class="{if $shipments|@count > 1 && !$shipment.isShippable}activeList_add_sort{/if} activeList_add_delete orderShipmentsItem activeList_accept_orderShipmentsItem">
    {foreach item="item" from=$shipment.items}
        <li id="orderShipmentsItems_list_{$orderID}_{$shipment.ID}_{$item.ID}" >
            {include file="backend/shipment/itemAmount.tpl" shipped=false}
        </li>
    {/foreach}
    </ul>
    
    {include file="backend/shipment/shipmentTotal.tpl"}
</form>