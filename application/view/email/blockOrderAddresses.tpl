
{% if $order.BillingAddress %}
<br /><b>{t _billing_address}:</b>
[[ partial('email/blockAddress.tpl', ['address': $order.BillingAddress]) ]]
{% endif %}

{% if $order.ShippingAddress %}
<b>{t _shipping_address}:</b>
[[ partial('email/blockAddress.tpl', ['address': $order.ShippingAddress]) ]]
{% endif %}