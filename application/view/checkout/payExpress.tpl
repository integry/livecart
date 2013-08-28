{% extends "layout/frontend.tpl" %}

{% block title %}{t _pay}{{% endblock %}
[[ partial("checkout/layout.tpl") ]]
{% block content %}

	<div id="payTotal">
		<div>
			Order total: <span class="subTotal">{$order.formattedTotal.$currency}</span>
		</div>
	</div>

	<div class="clear"></div>

	<form action="{link controller=checkout action=payExpressComplete}" method="post" id="expressComplete" class="form-horizontal">

		{include file="block/submit.tpl" caption="_complete_now"}

	</form>

	<div class="clear"></div>

	[[ partial("checkout/orderOverview.tpl") ]]

{% endblock %}
