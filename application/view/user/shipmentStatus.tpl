{% if shipment.isReturned %}
	<span class="returned">{t _returned}</span>
{% elseif shipment.isShipped %}
	<span class="mailed">{t _shipped}</span>
{% elseif shipment.isAwaitingShipment %}
	<span class="mailed">{t _awaiting}</span>
{% else %}
	<span class="processing">{t _processing}</span>
{% endif %}