{if $shipment}
    <dl>
        <dt>{t _shipment_id}:</dt>
        <dd>{$shipment.ID}</dd>
    </dl>
    
    <dl>
        <dt>{t _status}:</dt>
        <dd>
            {if $shipment.status == 0}{t _new}
            {elseif $shipment.status == 1}{t _pending}
            {elseif $shipment.status == 2}{t _awaiting}
            {elseif $shipment.status == 3}{t _shipped}{/if}
        </dd>
    </dl>
      
    {if $shipment.ShippingService}
        <dl>
            <dt>{t _shipping_service}:</dt>
            <dd>{$shipment.ShippingService.serviceName}</dd>
        </dl>
        
        <dl>
            <dt>{t _provider_name}:</dt>
            <dd>{$shipment.ShippingService.providerName}</dd>
        </dl>
        
        <dl>
            <dt>{t _shipment_price}:</dt>
            <dd>
                {if $shipment.ShippingService.formattedPrice[$defaultCurrencyCode]}{$shipment.ShippingService.formattedPrice[$defaultCurrencyCode]}
                {else}{$shipment.ShippingService.formattedPrice|@reset}{/if}
            </dd>
        </dl>
        
        <dl>
            <dt>{t _taxex_price}:</dt>
            <dd>
                {if $shipment.ShippingService.taxPrice[$defaultCurrencyCode]}{$shipment.ShippingService.taxPrice[$defaultCurrencyCode]}
                {else}{$shipment.ShippingService.taxPrice|@reset}{/if}
            </dd>
        </dl>
    {else}
        <dl>
            <dt>{t _shipping_service}:</dt>
            <dd>{t _no_shipping_service_selected}</dd>
        </dl>
    {/if}
{else}
    <div>{t _no_data_available}</div>
{/if}