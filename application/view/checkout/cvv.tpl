{% extends "layout/frontend.tpl" %}

{% block title %}{t _cvv}{{% endblock %}
{include file="checkout/layout.tpl"}
{% block content %}

	{include file="checkout/cvvHelp.tpl"}

{% endblock %}
