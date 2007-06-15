<div class="orderShipmentsItem_info">
    <dl>
        <dt>{t _price}: </dt>
    	<dd>
    	    {$item.count}
            x 
            {$shipment.AmountCurrency.pricePrefix}{$item.price}{$shipment.AmountCurrency.priceSuffix}
            = 
            {$shipment.AmountCurrency.pricePrefix}{math equation="x * y" x=$item.price y=$item.count}{$shipment.AmountCurrency.priceSuffix}
        </dd>
    </dl>
</div >