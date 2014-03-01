{*
	<dl class="{% if order.ID !== otherOrder.ID %}logValueChanged{% endif %}">
		<dt>{t _order_id}:</dt>
		<dd>[[order.invoiceNumber]]</dd>
	</dl>
*}

{% if order.status !== otherOrder.status %}
	<dl class="{% if order.status !== otherOrder.status %}logValueChanged{% endif %}">
		<dt>{t _status}:</dt>
		<dd>
			{% if order.status == 0 %}{t _new}
			{% elseif order.status == 1 %}{t _shipment_pending}
			{% elseif order.status == 2 %}{t _shipment_awaiting}
			{% elseif order.status == 3 %}{t _shipment_shipped}
			{% elseif order.status == 4 %}{t _shipment_returned}{% endif %}
		</dd>
	</dl>
{% endif %}

{% if order.isCancelled !== otherOrder.isCancelled %}
	<dl class="{% if order.isCancelled !== otherOrder.isCancelled %}logValueChanged{% endif %}">
		<dt>{t _canceled}:</dt>
		<dd>
			{% if order.isCancelled == 0 %}{t _false}
			{% elseif order.isCancelled == 1 %}{t _true}{% endif %}
		</dd>
	</dl>
{% endif %}