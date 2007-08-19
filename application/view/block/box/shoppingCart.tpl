<div id="smallCart">

	<div style="float: left;">
		<a href="{link controller=user action=index}">Your Account</a>&nbsp;
		{if $user.ID > 0}	
			<div class="logout"><a href="{link controller=user action=logout}">{t Sign out}</a></div>
		{/if}
	</div>
	| <a href="{link controller=order returnPath=true}">Shopping Cart</a> (<strong>{$order.basketCount}</strong> items)
	{if $order.basketCount > 0}
        | <a href="{link controller=checkout returnPath=true}" class="checkout">{t Checkout}</a>    
	{/if}

</div>