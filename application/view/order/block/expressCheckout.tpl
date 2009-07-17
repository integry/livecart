{if $expressMethods && $cart.isOrderable && !$cart.isMultiAddress}
	<div id="expressCheckoutMethods">
		{foreach from=$expressMethods item=method}
			<a href="{link controller=checkout action=express id=$method}"><img src="image/payment/{$method}.gif" /></a>
		{/foreach}
	</div>
{/if}
