{if $orderedItem}
  
    <dl class="{if $orderedItem.Product.ID !== $otherOrderedItem.Product.ID}logValueChanged{/if}">
        <dt>{t _product_name}:</dt>
        <dd><a href="{backendProductUrl product=$orderedItem.Product}">{$orderedItem.Product.name}</a> ({$orderedItem.Product.sku})&nbsp;</dd>
    </dl>
    
    {if $orderedItem.price !== $otherOrderedItem.price}
        <dl class="{if $orderedItem.price !== $otherOrderedItem.price}logValueChanged{/if}">
            <dt>{t _price}:</dt>
            <dd>{$log.Order.Currency.pricePrefix}{$orderedItem.price}{$log.Order.Currency.priceSuffix}&nbsp;</dd>
        </dl>
    {/if}
    
    {if $orderedItem.count !== $otherOrderedItem.count}
        <dl class="{if $orderedItem.count !== $otherOrderedItem.count}logValueChanged{/if}">
            <dt>{t _quantity}:</dt>
            <dd>{$orderedItem.count}&nbsp;</dd>
        </dl>
    {/if}
    
    {if $orderedItem.Shipment.ID !== $otherOrderedItem.Shipment.ID}
        <dl class="{if $orderedItem.Shipment.ID !== $otherOrderedItem.Shipment.ID}logValueChanged{/if}">
            <dt>{t _shipment}:</dt>
            <dd>{$orderedItem.Shipment.ID}&nbsp;</dd>
        </dl>
    {/if}
{else}
    <div class="logNoData">{t _no_data_available}</div>
{/if}