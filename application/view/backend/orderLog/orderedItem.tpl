{if $orderedItem}
    <dl class="{if $orderedItem.ID !== $otherOrderedItem.ID}logValueChanged{/if}">
        <dt>{t _item_id}:</dt>
        <dd>{$orderedItem.ID}&nbsp;</dd>
    </dl>
    
    <dl class="{if $orderedItem.Product.ID !== $otherOrderedItem.Product.ID}logValueChanged{/if}">
        <dt>{t _product_name}:</dt>
        <dd>{$orderedItem.Product.name} (ID: {$orderedItem.Product.ID})&nbsp;</dd>
    </dl>
    
    <dl class="{if $orderedItem.Product.sku !== $otherOrderedItem.Product.sku}logValueChanged{/if}">
        <dt>{t _sku}:</dt>
        <dd>{$orderedItem.Product.sku}&nbsp;</dd>
    </dl>
    
    <dl class="{if $orderedItem.count !== $otherOrderedItem.count}logValueChanged{/if}">
        <dt>{t _quantity}:</dt>
        <dd>{$orderedItem.count}&nbsp;</dd>
    </dl>
    
    <dl class="{if $orderedItem.Shipment.ID !== $otherOrderedItem.Shipment.ID}logValueChanged{/if}">
        <dt>{t _shipment}:</dt>
        <dd>{$orderedItem.Shipment.ID}&nbsp;</dd>
    </dl>
{else}
    <div class="logNoData">{t _no_data_available}</div>
{/if}