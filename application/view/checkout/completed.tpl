{% extends "layout/frontend.tpl" %}

{% block title %}{t _order_completed}{{% endblock %}
[[ partial('layout/frontend/layout.tpl', ['hideLeft': true]) ]]
{% block content %}

	{% if $order.isPaid %}
		{t _completed_paid}
	{% else %}
		{t _completed_offline}

		{% if $transactions.0.serializedData.handlerID %}
			[[ partial('checkout/offlineMethodInfo.tpl', ['method': transactions.0.serializedData.handlerID|@substr:-1]) ]]
		{% endif %}
	{% endif %}

	[[ partial('checkout/completeOverview.tpl', ['nochanges': true]) ]]
	[[ partial("checkout/orderDownloads.tpl") ]]

{% endblock %}
