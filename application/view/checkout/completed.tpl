{% extends "layout/frontend.tpl" %}

{% block title %}{t _order_completed}{{% endblock %}
{include file="layout/frontend/layout.tpl" hideLeft=true}
{% block content %}

	{% if $order.isPaid %}
		{t _completed_paid}
	{% else %}
		{t _completed_offline}

		{% if $transactions.0.serializedData.handlerID %}
			{include file="checkout/offlineMethodInfo.tpl" method=$transactions.0.serializedData.handlerID|@substr:-1}
		{% endif %}
	{% endif %}

	{include file="checkout/completeOverview.tpl" nochanges=true}
	[[ partial("checkout/orderDownloads.tpl") ]]

{% endblock %}
