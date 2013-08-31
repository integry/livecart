{% extends "layout/frontend.tpl" %}

{% title %}{t _add_shipping_address}{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "addressMenu"]) ]]
{% block content %}

	{form action="user/doAddShippingAddress" handle=$form class="form-horizontal"}
		[[ partial("user/addressForm.tpl") ]]
		[[ partial('block/submit.tpl', ['caption': "_continue", 'cancelRoute': return]) ]]
	{/form}

{% endblock %}
