<fieldset class="container">
	{if $order.isOrderable}
		<div id="checkoutProgress" class="{$progress}">
			<span class="progressOrder">1</span><a href="{link controller=order}" id="progressCart"><span><span><span><span>{t _cart}</span></span></span></span></a>
			<span class="progressOrder">2</span><a href="{link controller=checkout action=selectAddress}" {if $order.isAddressSelected}class="addressSelected"{/if} id="progressAddress"><span><span><span><span>{t _address}</span></span></span></span></a>
			<span class="progressOrder">3</span><a href="{link controller=checkout action=shipping}" {if $order.isShippingSelected}class="shippingSelected"{/if} id="progressShipping"><span><span><span><span>{t _shipping}</span></span></span></span></a>
			<span class="progressOrder">4</span><a href="{link controller=checkout action=pay}" id="progressPayment"><span><span><span><span>{t _payment}</span></span></span></span></a>
		</div>
	{/if}
	<div class="clear"></div>
</fieldset>