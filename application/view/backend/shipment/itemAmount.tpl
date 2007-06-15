<table class="orderShipmentsItem_info">
    <tr>
        <td >
           <div class="orderShipmentsItem_info_sku">
               {$item.Product.sku}
           </div>
        </td>
        <td>
            <div class="orderShipmentsItem_info_name">
                {$item.Product.name}
            </div>
        </td>
        <td>
            <div class="orderShipmentsItem_info_price">
                <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
                <span class="price">{$item.price}</span>
                <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
            </div>
        </td>
        <td>
            <div class="orderShipmentsItem_info_count">
                {$item.count}
            </div>
        </td>
        <td>
            <div class="orderShipmentsItem_info_total">
                <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
                <span class="price">{math equation="x * y" x=$item.price y=$item.count}</span>
                <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
            </div> 
        </td>
    </tr>
</table>