{if $orderedItem}
    <dl>
        <dt>{t _item_id}:</dt>
        <dd>{$orderedItem.ID}</dd>
    </dl>
    
    <dl>
        <dt>{t _product_name}:</dt>
        <dd>{$orderedItem.Product.name}</dd>
    </dl>
    
    <dl>
        <dt>{t _quantity}:</dt>
        <dd>{$orderedItem.count}</dd>
    </dl>
    
    <dl>
        <dt>{t _shipment}:</dt>
        <dd>{$orderedItem.Shipment.ID}</dd>
    </dl>
{else}
    <div>{t _no_data_available}</div>
{/if}