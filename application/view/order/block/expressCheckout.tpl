{% if $expressMethods && $cart.isOrderable && !$cart.isMultiAddress %}
	<div id="expressCheckoutMethods">
		{foreach from=$expressMethods item=method}
			<a href="[[ url("checkout/express/" ~ method) ]]"><img src="{s image/payment/[[method]].gif}" /></a>
		{/foreach}
	</div>
{% endif %}
