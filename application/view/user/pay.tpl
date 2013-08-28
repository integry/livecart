{% extends "layout/frontend.tpl" %}

{% block title %}{t _pay} #[[order.invoiceNumber]] ([[order.formatted_dateCompleted.date_long]]){{% endblock %}
[[ partial("checkout/layout.tpl") ]]
{% block content %}

	<div id="payTotal">
		<div>
			{t _amount_due}: <span class="subTotal">[[order.formatted_amountDue]]</span>
		</div>
	</div>

	{include file="checkout/completeOverview.tpl" productsInSeparateLine=true}

	<div class="paymentMethods">
		[[ partial("checkout/paymentMethods.tpl") ]]
		[[ partial("checkout/offlinePaymentMethods.tpl") ]]
	</div>

	<div class="clear"></div>
{% endblock %}



</div>
