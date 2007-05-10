<div id="smallCart">

	<a href="{link controller=user action=index}">Your Account</a>
	| <a href="{link controller=order returnPath=true}">Shopping Cart</a> (<strong>{$order.basketCount}</strong> items)
	{if $order.basketCount > 0}
        | <a href="{link controller=checkout returnPath=true}">{t Checkout}</a> <img src="image/silk/cart_go.png" style="vertical-align: middle;" />    
	{/if}

</div>