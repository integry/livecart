<a href="{link route=$return}" class="continueShopping"><span><span><span><span>{t _continue_shopping}</span></span></span></span></a>
{if $cart.isOrderable}
	<div class="checkoutButtons">
		<a href="{link controller=checkout}" class="proceedToCheckout" onclick="return Order.submitCartForm(this);"><span><span><span><span>{t _proceed_checkout}</span></span></span></span></a>

		<div class="clear"></div>

		{if 'REQUIRE_TOS'|config && !'TOS_OPC_ONLY'|config && !$hideTos}
			{include file="order/block/tos.tpl"}
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
