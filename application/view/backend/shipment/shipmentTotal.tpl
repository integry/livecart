<table class="orderShipmentsItem_info orderShipment_info">
    <tr style="display: none;">
        <td class="orderShipmentsItem_info_report_td">
            <div class="orderShipmentsItem_info_report">
                {t _subtotal_price}:
            </div>
        </td>
        <td class="orderShipmentsItem_info_total_td">
            <div class="orderShipmentsItem_info_total">
                <span class="orderShipment_info_subtotal">
                    <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
                    <span class="price">{$shipment.amount}</span>
                    <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
                </span>
            </div>
        </td>
    </tr>
    
    
    <tr>
        <td class="orderShipmentsItem_info_report_td">
            <div class="orderShipmentsItem_info_report">
                {t _shipping} ({t _usps_priority}): <a href="#change">({t _change_usps_priority})</a>
            </div>
        </td>
        <td class="orderShipmentsItem_info_total_td">
            <div class="orderShipmentsItem_info_total">
                <span class="orderShipment_info_shippingAmount">
                    <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
                    <span class="price">{$shipment.shippingAmount}</span>
                    <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
                </span>
            </div>
        </td>
    </tr>
    
    <tr>
        <td class="orderShipmentsItem_info_report_td">
            <div class="orderShipmentsItem_info_report">
                {t _price}:
            </div>
        </td>
        <td class="orderShipmentsItem_info_total_td">
            <div class="orderShipmentsItem_info_total">
                <span class="orderShipment_info_total">
                    <span class="pricePrefix">{if $shipment.shippingAmount !== null && $shipment.amount !== null}{$shipment.AmountCurrency.pricePrefix}{/if}</span>
                    <span class="price">{if $shipment.shippingAmount !== null && $shipment.amount !== null}{math equation="x + y" x=$shipment.shippingAmount y=$shipment.amount}{/if}</span>
                    <span class="priceSuffix">{if $shipment.shippingAmount !== null && $shipment.amount !== null}{$shipment.AmountCurrency.priceSuffix}{/if}</span>
                </span>
            </div>
        </td>
    </tr>
</table>

    
<fieldset class="error" style="text-align: right;">
    <span class="activeForm_progress"></span>
    <input type="submit" class="button submit" value="{t _save}" />
    {t _or}
    <a href="#cancel" class="cancel">{t _cancel}</a>
</fieldset>
