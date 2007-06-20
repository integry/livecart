<input type="hidden" name="ID" value="{$shipment.ID}" />
<input type="hidden" name="orderID" value="{$shipment.Order.ID}" />
<input type="hidden" name="shippingServiceID" value="{$shipment.ShippingService.ID}" />

<table class="orderShipmentsItem_info orderShipment_info">
    <tr class="orderShipment_info_first_row" >
        <td class="orderShipmentsItem_info_report_td">
            <div class="orderShipmentsItem_info_report">
                {t _subtotal_price}:
            </div>
        </td>
        <td class="orderShipmentsItem_info_total_td">
            <div class="orderShipmentsItem_info_total">
                <span class="orderShipment_info_subtotal shipment_amount">
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
                {t _taxes}:
            </div>
        </td>
        <td class="orderShipmentsItem_info_total_td">
            <div class="orderShipmentsItem_info_tax">
                <span class="orderShipment_info_subtotal shipment_taxAmount">
                    <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
                    <span class="price">{$shipment.taxAmount}</span>
                    <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
                </span>
            </div>
        </td>
    </tr>
    
    
    <tr>
        <td class="orderShipmentsItem_info_report_td">
            <div class="orderShipmentsItem_info_report">
                {t _shipping} ({t _usps_priority}): 
                <a href="#change" class="orderShipment_change_usps" id="orderShipment_change_usps_{$shipment.ID}"  style="{if $shipment.status == 3}display: none;{/if}">({t _change_usps_priority})</a>
                
                <span class="controls" id="orderShipment_USPS_{$shipment.ID}" style="display: none">
                    <select name="USPS" id="orderShipment_USPS_{$shipment.ID}_select"> </select>
                
                    <span class="activeForm_progress"/>
                    <input type="submit" value="Save" class="button submit"  id="orderShipment_USPS_{$shipment.ID}_submit" />
                    or
                    <a class="cancel" href="#cancel"  id="orderShipment_USPS_{$shipment.ID}_cancel" >Cancel</a>
                </span>
            </div>
        </td>
        <td class="orderShipmentsItem_info_total_td">
            <div class="orderShipmentsItem_info_total">
                <span class="orderShipment_info_shippingAmount shipment_shippingAmount">
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
            <div class="orderShipmentsItem_info_total orderShipment_totalSum">
                <span class="orderShipment_info_total shipment_total">
                    <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
                    <span class="price">{math equation="x + y + z" x=$shipment.shippingAmount|default:0 y=$shipment.amount|default:0 z=$shipment.taxAmount|default:0}</span>
                    <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
                </span>
            </div>
        </td>
    </tr>
</table>

</form>