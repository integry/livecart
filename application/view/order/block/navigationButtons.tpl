<a href="{link route=$return}" class="btn btn-primary pull-left continueShopping"><span class="glyphicon glyphicon-arrow-left"></span> {t _continue_shopping}</a>
{if $cart.isOrderable}
	<div class="checkoutButtons">
		<a href="{link controller=checkout}" class="btn btn-danger pull-right proceedToCheckout" onclick="return Order.submitCartForm(this);">{t _proceed_checkout} <span class="glyphicon glyphicon-arrow-right"></span></a>

		<div class="clear"></div>

		{if 'REQUIRE_TOS'|config && !'TOS_OPC_ONLY'|config && !$hideTos}
			[[ partial("order/block/tos.tpl") ]]
		{/if}

		{if 'ENABLE_MULTIADDRESS'|config}
			{if !$multi}
				<a href="{link controller=order action=setMultiAddress}" class="multiAddressCheckout">{t _ship_to_multiple}</a>
			{else}
				<a href="{link controller=order action=setSingleAddress}" class="multiAddressCheckout">{t _ship_to_single}</a>
			{/if}
		{/if}
	</div>
{/if}
