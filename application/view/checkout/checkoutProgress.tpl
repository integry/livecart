<fieldset class="container">
	{if $order.isOrderable}
		<div id="checkoutProgress" class="{$progress}">
			<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=order}" id="progressCart"><span><span><span><span>{t _cart}</span></span></span></span></a>
			<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=checkout action=selectAddress}" {if $order.isAddressSelected}class="addressSelected"{/if} id="progressAddress"><span><span><span><span>{t _address}</span></span></span></span></a>

			{if 'ENABLE_CHECKOUTDELIVERYSTEP'|config}
				<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=checkout action=selectAddress query="step=shipping"}" {if $order.isAddressSelected}class="addressSelected"{/if} id="progressShippingAddress"><span><span><span><span>{t _shipping_address}</span></span></span></span></a>
			{/if}

			<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=checkout action=shipping}" {if $order.isShippingSelected}class="shippingSelected"{/if} id="progressShipping"><span><span><span><span>{t _shipping}</span></span></span></span></a>
			<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=checkout action=pay}" id="progressPayment"><span><span><span><span>{t _payment}</span></span></span></span></a>
		</div>
	{/if}
	<div class="clear"></div>
</fieldset>