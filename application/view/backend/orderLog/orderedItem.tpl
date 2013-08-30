{% if !empty(orderedItem) %}

	<dl class="{% if $orderedItem.Product.ID !== $otherOrderedItem.Product.ID %}logValueChanged{% endif %}">
		<dt>{t _product_name}:</dt>
		<dd>
			{% if $orderedItem.Product.ID %}
				<a href="{backendProductUrl product=$orderedItem.Product}">[[orderedItem.Product.name]]</a>
			{% else %}
				[[orderedItem.Product.name]]
			{% endif %}
			([[orderedItem.Product.sku]])&nbsp;
		</dd>
	</dl>

	{% if $orderedItem.price !== $otherOrderedItem.price %}
		<dl class="{% if $orderedItem.price !== $otherOrderedItem.price %}logValueChanged{% endif %}">
			<dt>{t _price}:</dt>
			<dd>[[log.Order.Currency.pricePrefix]][[orderedItem.price]][[log.Order.Currency.priceSuffix]]&nbsp;</dd>
		</dl>
	{% endif %}

	{% if $orderedItem.count !== $otherOrderedItem.count %}
		<dl class="{% if $orderedItem.count !== $otherOrderedItem.count %}logValueChanged{% endif %}">
			<dt>{t _quantity}:</dt>
			<dd>[[orderedItem.count]]&nbsp;</dd>
		</dl>
	{% endif %}

	{% if $orderedItem.Shipment.ID !== $otherOrderedItem.Shipment.ID %}
		<dl class="{% if $orderedItem.Shipment.ID !== $otherOrderedItem.Shipment.ID %}logValueChanged{% endif %}">
			<dt>{t _shipment}:</dt>
			<dd>[[orderedItem.Shipment.ID]]&nbsp;</dd>
		</dl>
	{% endif %}
{% else %}
	<div class="logNoData">{t _no_data_available}</div>
{% endif %}