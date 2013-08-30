{% if $cart.ID %}{% set order = $cart %}{% endif %}
{% if $order.isOrderable %}
	<div id="checkoutProgress" class="[[progress]]">
		<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}[[stepOrder]]</span><a href="{link controller=order}" class="{% if $progress != 'progressCart' %}completed{% endif %} {% if $progress == 'progressCart' %}active{% endif %}" id="progressCart"><span><span><span><span>{t _cart}</span></span></span></span></a>

		{% if !'DISABLE_CHECKOUT_ADDRESS_STEP'|config %}
		<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}[[stepOrder]]</span><a href="{link controller=checkout action=selectAddress}" class="{% if $order.isAddressSelected %}completed{% endif %} {% if $progress == 'progressAddress' %}active{% endif %}" id="progressAddress"><span><span><span><span>{t _address}</span></span></span></span></a>
		{% endif %}

		{% if 'ENABLE_CHECKOUTDELIVERYSTEP'|config && !'REQUIRE_SAME_ADDRESS'|config %}
			<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}[[stepOrder]]</span><a href="{link controller=checkout action=selectAddress query="step=shipping"}" class="{% if $order.isAddressSelected %}completed{% endif %} {% if $progress == 'progressShippingAddress' %}active{% endif %}" id="progressShippingAddress"><span><span><span><span>{t _shipping_address}</span></span></span></span></a>
		{% endif %}

		{% if $order.isShippingRequired %}
			<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}[[stepOrder]]</span><a href="{link controller=checkout action=shipping}" class="{% if $order.isAddressSelected %}completed{% endif %} {% if $progress == 'progressShipping' %}active{% endif %}" id="progressShipping"><span><span><span><span>{t _shipping}</span></span></span></span></a>
		{% endif %}

		<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}[[stepOrder]]</span><a href="{link controller=checkout action=pay}" class="{% if $progress == 'progressPayment' %}active{% endif %}" id="progressPayment"><span><span><span><span>{t _payment}</span></span></span></span></a>
	</div>
{% endif %}