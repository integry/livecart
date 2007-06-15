<div class="orderShipment_info">
    <fieldset class="error">
        <label>{t _subtotal_price}:</label>
        <span class="orderShipment_info_subtotal">
            {$shipment.AmountCurrency.pricePrefix}{$shipment.amount}{$shipment.AmountCurrency.priceSuffix}
        </span>
    </fieldset >
    <fieldset class="error">
        <label>{t _shipping_price}:</label>
        <span class="orderShipment_info_shippingAmount">
            {$shipment.AmountCurrency.pricePrefix}{$shipment.shippingAmount}{$shipment.AmountCurrency.priceSuffix}
        </span>
    </fieldset >
    
    <hr />
    
    <fieldset class="error">
        <label>{t _total_price}:</label>
        <span class="orderShipment_info_total">
        {if $shipment.shippingAmount && $shipment.amount}
            {$shipment.AmountCurrency.pricePrefix}{math equation="x + y" x=$shipment.shippingAmount y=$shipment.amount}{$shipment.AmountCurrency.priceSuffix}
        {/if}            
        </span>
    </fieldset >
</div>