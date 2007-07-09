<table class="orderShipmentsItem_info">
    <tr>
        <td class="orderShipmentsItem_info_sku_td">
           <div class="orderShipmentsItem_info_sku">
               {$item.Product.sku}
           </div>
        </td>
        <td class="orderShipmentsItem_info_name_td">
            <div class="orderShipmentsItem_info_name">
                {$item.Product.name}
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
                <input name="count_{$item.ID}" value="{$item.count}" id="orderShipmentsItem_count_{$item.ID}" class="orderShipmentsItem_count" style="{if $shipped}display: none;{/if}" />
                {if $shipped}{$item.count}{/if}
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

