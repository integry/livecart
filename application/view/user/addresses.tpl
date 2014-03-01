{% extends "layout/frontend.tpl" %}

{% title %}{t _manage_addresses}{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "addressMenu"]) ]]
{% block content %}

	<h2 id="billingAddresses">{t _billing_addresses}</h2>

	<a href="{link controller=user action=addBillingAddress returnPath=true}" class="menu">
		{t _add_billing_address}
	</a>

	<table class="addressSelector">
	{foreach from=billingAddresses item="item"}
		[[ partial("user/address.tpl") ]]
		<div class="addressControl">
			<a href="{link controller=user action=editBillingAddress id=item.ID returnPath=true}">{t _edit_address}</a>
			|
			<a href="{link controller=user action=deleteBillingAddress id=item.ID returnPath=true}">{t _remove_address}</a>
		</div>
	{% endfor %}
	</table>

	<div style="clear: both;"></div>

	<h2 id="shippingAddresses">{t _shipping_addresses}</h2>

	<a href="{link controller=user action=addShippingAddress returnPath=true}" class="menu">
		{t _add_shipping_address}
	</a>

	{foreach from=shippingAddresses item="item"}
		[[ partial("user/address.tpl") ]]
		<div class="addressControl">
			<a href="{link controller=user action=editShippingAddress id=item.ID returnPath=true}">{t _edit_address}</a>
			|
			<a href="{link controller=user action=deleteShippingAddress id=item.ID returnPath=true}">{t _remove_address}</a>
		</div>
	{% endfor %}

{% endblock %}
