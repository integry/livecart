{if $cart.ID}{assign var="order" value=$cart}{/if}
<fieldset class="container">
	{if $order.isOrderable}
		<div id="checkoutProgress" class="{$progress}">
			<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=order}" class="{if $progress != 'progressCart'}completed{/if} {if $progress == 'progressCart'}active{/if}" id="progressCart"><span><span><span><span>{t _cart}</span></span></span></span></a>

			{if !'DISABLE_CHECKOUT_ADDRESS_STEP'|config}
			<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=checkout action=selectAddress}" class="{if $order.isAddressSelected}completed{/if} {if $progress == 'progressAddress'}active{/if}" id="progressAddress"><span><span><span><span>{t _address}</span></span></span></span></a>
			{/if}

			{if 'ENABLE_CHECKOUTDELIVERYSTEP'|config}
				<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=checkout action=selectAddress query="step=shipping"}" class="{if $order.isAddressSelected}completed{/if} {if $progress == 'progressShippingAddress'}active{/if}" id="progressShippingAddress"><span><span><span><span>{t _shipping_address}</span></span></span></span></a>
			{/if}

			{if $order.isShippingRequired}
				<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=checkout action=shipping}" class="{if $order.isAddressSelected}completed{/if} {if $progress == 'progressShipping'}active{/if}" id="progressShipping"><span><span><span><span>{t _shipping}</span></span></span></span></a>
			{/if}

			<span class="progressOrder">{assign var="stepOrder" value="`$stepOrder+1`"}{$stepOrder}</span><a href="{link controller=checkout action=pay}" class="{if $progress == 'progressPayment'}active{/if}" id="progressPayment"><span><span><span><span>{t _payment}</span></span></span></span></a>
		</div>
	{/if}
	<div class="clear"></div>
</fieldset>