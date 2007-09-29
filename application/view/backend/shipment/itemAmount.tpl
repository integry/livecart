<table class="orderShipmentsItem_info">
    <tr>
        <td class="orderShipmentsItem_info_sku_td">
           <div class="orderShipmentsItem_info_sku">
               {$item.Product.sku}
           </div>
        </td>
        <td class="orderShipmentsItem_info_name_td">
            <div class="orderShipmentsItem_info_name">
                <a href="{backendProductUrl product=$item.Product}">{$item.Product.name_lang}</a>
            </div>
        </td>
        <td class="orderShipmentsItem_info_price_td">
            <div class="orderShipmentsItem_info_price">
                <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
                <span class="price">{$item.price}</span>
                <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
            </div>
        </td>
        <td class="orderShipmentsItem_info_count_td">
            <div class="orderShipmentsItem_info_count">
                <span class="progressIndicator" style="display: none;"></span>
                <input name="count_{$item.ID}" value="{$item.count}" id="orderShipmentsItem_count_{$item.ID}" class="orderShipmentsItem_count" style="{if $item.Shipment.status == 3 || $shipment.status == 3}display: none;{/if}" {denied role='order.update'}readonly="readonly"{/denied}  />
                <span class="itemCountText">{$item.count}</span>
            </div>
        </td>
        <td class="orderShipmentsItem_info_total_td ">
            <div class="orderShipmentsItem_info_total item_subtotal">
                <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
                <span class="price">{math equation="x * y" x=$item.price|default:0 y=$item.count|default:0}</span>
                <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
            </div> 
        </td>
    </tr>
</table>

