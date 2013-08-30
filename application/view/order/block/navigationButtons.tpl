<a href="[[ url(return) ]]" class="btn btn-primary pull-left continueShopping"><span class="glyphicon glyphicon-arrow-left"></span> {t _continue_shopping}</a>
{% if $cart.isOrderable %}
	<div class="checkoutButtons">
		<a href="[[ url("checkout") ]]" class="btn btn-danger pull-right proceedToCheckout" onclick="return Order.submitCartForm(this);">{t _proceed_checkout} <span class="glyphicon glyphicon-arrow-right"></span></a>

		<div class="clear"></div>

		{% if 'REQUIRE_TOS'|config && !'TOS_OPC_ONLY'|config && !$hideTos %}
			[[ partial("order/block/tos.tpl") ]]
		{% endif %}

		{% if 'ENABLE_MULTIADDRESS'|config %}
			{% if empty(multi) %}
				<a href="[[ url("order/setMultiAddress") ]]" class="multiAddressCheckout">{t _ship_to_multiple}</a>
			{% else %}
				<a href="[[ url("order/setSingleAddress") ]]" class="multiAddressCheckout">{t _ship_to_single}</a>
			{% endif %}
		{% endif %}
	</div>
{% endif %}
