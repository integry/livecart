<div class="orderShipment_info">
    <fieldset class="error">
        <label>{t _subtotal_price}:</label>
        <span class="orderShipment_info_subtotal">
            <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
            <span class="price">{$shipment.amount}</span>
            <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
        </span>
    </fieldset >
    <fieldset class="error">
        <label>{t _shipping_price}:</label>
        <span class="orderShipment_info_shippingAmount">
            <span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
            <span class="price">{$shipment.shippingAmount}</span>
            <span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
        </span>
    </fieldset >
    
    <hr />
    
    <fieldset class="error">
        <label>{t _total_price}:</label>
        <span class="orderShipment_info_total">
            <span class="pricePrefix">{if $shipment.shippingAmount !== null && $shipment.amount !== null}{$shipment.AmountCurrency.pricePrefix}{/if}</span>
            <span class="price">{if $shipment.shippingAmount !== null && $shipment.amount !== null}{math equation="x + y" x=$shipment.shippingAmount y=$shipment.amount}{/if}</span>
            <span class="priceSuffix">{if $shipment.shippingAmount !== null && $shipment.amount !== null}{$shipment.AmountCurrency.priceSuffix}{/if}</span>
        </span>
    </fieldset >
</div>