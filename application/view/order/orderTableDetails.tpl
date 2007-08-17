{foreach from=$shipment.items item="item" name="shipment"}
    <tr{zebra loop="shipment"}>                    
        <td class="productName"><a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a></td>
        <td>{$item.formattedPrice}</td>
        <td>{$item.count}</td>
        <td>{$item.formattedSubTotal}</td>
    </tr>
{/foreach}            

{if $order.isShippingRequired && $shipment.isShippable}
    <tr>
        <td colspan="3" class="subTotalCaption">
            {t _shipping} ({$shipment.ShippingService.name_lang}):
        </td>
        <td>
            {$shipment.selectedRate.formattedPrice[$order.Currency.ID]}
        </td>
    </tr>
{/if}