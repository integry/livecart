{% extends "layout/frontend.tpl" %}

{% block title %}{t _add_billing_address}{{% endblock %}
{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="addressMenu"}
{% block content %}

	{form action="controller=user action=doAddBillingAddress" handle=$form class="form-horizontal"}
		{include file="user/addressForm.tpl"}

		{include file="block/submit.tpl" caption="_continue" cancelRoute=$return class="form-horizontal"}
	{/form}

{% endblock %}
