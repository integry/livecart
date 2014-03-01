{% if !empty(shipment) %}
	{% if shipment.ID !== otherShipment.ID %}
		<dl class="{% if shipment.ID !== otherShipment.ID %}logValueChanged{% endif %}">
			<dt>{t _shipment_id}:</dt>
			<dd>[[shipment.ID]]&nbsp;</dd>
		</dl>
	{% endif %}
	
	{% if shipment.status !== otherShipment.status %}
		<dl class="{% if shipment.status !== otherShipment.status %}logValueChanged{% endif %}">
			<dt>{t _status}:</dt>
			<dd>
				{% if shipment.status == 0 %}{t _new}
				{% elseif shipment.status == 1 %}{t _shipment_pending}
				{% elseif shipment.status == 2 %}{t _shipment_awaiting}
				{% elseif shipment.status == 3 %}{t _shipment_shipped}{% endif %}
				&nbsp;
			</dd>
		</dl>
	{% endif %}
	  
	{% if shipment.ShippingService %}
		{% if shipment.ShippingService.serviceID !== otherShipment.ShippingService.serviceID %}
			<dl class="{% if shipment.ShippingService.serviceID !== otherShipment.ShippingService.serviceID %}logValueChanged{% endif %}">
				<dt>{t _shipping_service}:</dt>
				<dd>[[shipment.ShippingService.serviceName]]&nbsp;</dd>
			</dl>
		{% endif %}
		
		{% if shipment.ShippingService.providerName !== shipment.ShippingService.providerName %}
			<dl class="{% if shipment.ShippingService.providerName !== shipment.ShippingService.providerName %}logValueChanged{% endif %}">
				<dt>{t _provider_name}:</dt>
				<dd>[[shipment.ShippingService.providerName]]&nbsp;</dd>
			</dl>
		{% endif %}
		
		{% if shipment.ShippingService.serviceID !== otherShipment.ShippingService.serviceID %}
			<dl class="{% if shipment.ShippingService.serviceID !== otherShipment.ShippingService.serviceID %}logValueChanged{% endif %}">
				<dt>{t _shipment_price}:</dt>
				<dd>
					{% if shipment.ShippingService.formattedPrice[defaultCurrencyCode] %}{shipment.ShippingService.formattedPrice[defaultCurrencyCode]}
					{% else %}{shipment.ShippingService.formattedPrice|@reset}{% endif %}
					&nbsp;
				</dd>
			</dl>
		{% endif %}
		
		{% if shipment.ShippingService.serviceID !== otherShipment.ShippingService.serviceID %}
			<dl class="{% if shipment.ShippingService.serviceID !== otherShipment.ShippingService.serviceID %}logValueChanged{% endif %}">
				<dt>{t _taxes_price}:</dt>
				<dd>
					{% if shipment.ShippingService.taxPrice[defaultCurrencyCode] %}{shipment.ShippingService.taxPrice[defaultCurrencyCode]}
					{% else %}{shipment.ShippingService.taxPrice|@reset}{% endif %}
					&nbsp;
				</dd>
			</dl>
		{% endif %}
	{% else %}
		{% if shipment.ID !== otherShipment.ID || shipment.ShippingService !== otherShipment.ShippingService %}
			<dl class="{% if shipment.ID !== otherShipment.ID || shipment.ShippingService !== otherShipment.ShippingService %}logValueChanged{% endif %}">
				<dt>{t _shipping_service}:</dt>
				<dd>{t _no_shipping_service_selected}&nbsp;</dd>
			</dl>
		{% endif %}
	{% endif %}
	
	
	{% if log.items|@count > 0 %}
		<dl class="logValueChanged">
			<dt>{t _products}:</dt>
			<dd>
				<ol class="removedProducts">
					{foreach item="item" from=log.items}
						<li>
							<span class="removedProductSKU">
								([[item.oldValue.Product.sku]])
							 </span>
							<span class="removedProductPrice">
								[[[item.oldValue.count]] X [[log.Order.Currency.pricePrefix]][[item.oldValue.price]][[log.Order.Currency.priceSuffix]]]
							</span> <br />
							<span class="removedProductName">
								[[item.oldValue.Product.name]]
							</span>
						</li>
					{% endfor %}
				</ol>
			</dd>
		</dl>

	{% endif %}
	
{% else %}
	<div class="logNoData">{t _no_data_available}</div>
{% endif %}