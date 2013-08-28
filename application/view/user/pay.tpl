{% extends "layout/frontend.tpl" %}

{% block title %}{t _pay} #[[order.invoiceNumber]] ([[order.formatted_dateCompleted.date_long]]){{% endblock %}
{include file="checkout/layout.tpl"}
{% block content %}

	<div id="payTotal">
		<div>
			{t _amount_due}: <span class="subTotal">[[order.formatted_amountDue]]</span>
		</div>
	</div>

	{include file="checkout/completeOverview.tpl" productsInSeparateLine=true}

	<div class="paymentMethods">
		{include file="checkout/paymentMethods.tpl"}
		{include file="checkout/offlinePaymentMethods.tpl"}
	</div>

	<div class="clear"></div>
{% endblock %}



</div>
