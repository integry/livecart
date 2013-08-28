{% extends "layout/frontend.tpl" %}

{% block title %}{t _pay}{{% endblock %}
{include file="checkout/layout.tpl"}
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

	{include file="checkout/orderOverview.tpl"}

{% endblock %}
