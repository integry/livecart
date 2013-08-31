{% extends "layout/frontend.tpl" %}

{% title %}{t _edit_shipping_address}{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "addressMenu"]) ]]
{% block content %}

	{form action="controller=user action=saveShippingAddress id=`$addressType.ID`" class="form-horizontal" handle=$form}
		[[ partial("user/addressForm.tpl") ]]

		[[ partial('block/submit.tpl', ['caption': "_continue", 'cancelRoute': return]) ]]

	{/form}

{% endblock %}
