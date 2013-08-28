{% extends "layout/frontend.tpl" %}

{% block title %}{t _edit_billing_address}{{% endblock %}
[[ partial("user/layout.tpl") ]]
{include file="user/userMenu.tpl" current="addressMenu"}
{% block content %}

	{form action="controller=user action=saveBillingAddress id=`$addressType.ID`" class="form-horizontal" handle=$form}
		[[ partial("user/addressForm.tpl") ]]

		{include file="block/submit.tpl" caption="_continue" cancelRoute=$return}
	{/form}

{% endblock %}
