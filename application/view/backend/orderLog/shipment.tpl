{if $shipment}
    <dl class="{if $shipment.ID !== $otherShipment.ID}logValueChanged{/if}">
        <dt>{t _shipment_id}:</dt>
        <dd>{$shipment.ID}&nbsp;</dd>
    </dl>
    
    <dl class="{if $shipment.status !== $otherShipment.status}logValueChanged{/if}">
        <dt>{t _status}:</dt>
        <dd>
            {if $shipment.status == 0}{t _new}
            {elseif $shipment.status == 1}{t _pending}
            {elseif $shipment.status == 2}{t _awaiting}
            {elseif $shipment.status == 3}{t _shipped}{/if}
            &nbsp;
        </dd>
    </dl>
      
    {if $shipment.ShippingService}
        <dl class="{if $shipment.ShippingService.serviceID !== $otherShipment.ShippingService.serviceID}logValueChanged{/if}">
            <dt>{t _shipping_service}:</dt>
            <dd>{$shipment.ShippingService.serviceName}&nbsp;</dd>
        </dl>
        
        <dl class="{if $shipment.ShippingService.serviceID !== $otherShipment.ShippingService.serviceID}logValueChanged{/if}">
            <dt>{t _provider_name}:</dt>
            <dd>{$shipment.ShippingService.providerName}&nbsp;</dd>
        </dl>
        
        <dl class="{if $shipment.ShippingService.serviceID !== $otherShipment.ShippingService.serviceID}logValueChanged{/if}">
            <dt>{t _shipment_price}:</dt>
            <dd>
                {if $shipment.ShippingService.formattedPrice[$defaultCurrencyCode]}{$shipment.ShippingService.formattedPrice[$defaultCurrencyCode]}
                {else}{$shipment.ShippingService.formattedPrice|@reset}{/if}
                &nbsp;
            </dd>
        </dl>
        
        <dl class="{if $shipment.ShippingService.serviceID !== $otherShipment.ShippingService.serviceID}logValueChanged{/if}">
            <dt>{t _taxes_price}:</dt>
            <dd>
                {if $shipment.ShippingService.taxPrice[$defaultCurrencyCode]}{$shipment.ShippingService.taxPrice[$defaultCurrencyCode]}
                {else}{$shipment.ShippingService.taxPrice|@reset}{/if}
                &nbsp;
            </dd>
        </dl>
    {else}
        <dl class="{if $shipment.ID !== $otherShipment.ID || $shipment.ShippingService !== $otherShipment.ShippingService}logValueChanged{/if}">
            <dt>{t _shipping_service}:</dt>
            <dd>{t _no_shipping_service_selected}&nbsp;</dd>
        </dl>
    {/if}
{else}
    <div class="logNoData">{t _no_data_available}</div>
{/if}