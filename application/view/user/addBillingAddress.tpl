{% extends "layout/frontend.tpl" %}

{% block title %}{t _add_billing_address}{{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "addressMenu"]) ]]
{% block content %}

	{form action="user/doAddBillingAddress" handle=$form class="form-horizontal"}
		[[ partial("user/addressForm.tpl") ]]

		[[ partial('block/submit.tpl', ['caption': "_continue", 'cancelRoute': $return, 'class': "form-horizontal"]) ]]
	{/form}

{% endblock %}
