<tr>
	<td colspan="4"></td>
	<td class="cartQuant"></td>
</tr>
<tr>
	<td colspan="5">
		<a href="{link route=$return}" class="continueShopping"><span><span><span><span>{t _continue_shopping}</span></span></span></span></a>
		{if $cart.isOrderable}
			<div class="checkoutButtons">
				<a href="{link controller=checkout}" class="proceedToCheckout" onclick="return Order.submitCartForm(this);"><span><span><span><span>{t _proceed_checkout}</span></span></span></span></a>

				{if 'ENABLE_MULTIADDRESS'|config}
					{if !$multi}
						<a href="{link controller=order action=setMultiAddress}" class="multiAddressCheckout">{t _ship_to_multiple}</a>
					{else}
						<a href="{link controller=order action=setSingleAddress}" class="multiAddressCheckout">{t _ship_to_single}</a>
					{/if}
				{/if}
			</div>
		{/if}
	</td>
</tr>
