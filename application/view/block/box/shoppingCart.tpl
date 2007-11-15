<div id="smallCart">

	<a href="{link controller=user action=index}">{t _your_account}</a>
	| <a href="{link controller=order returnPath=true}">{t _shopping_cart}</a> (<strong>{$order.basketCount}</strong> items)
	{if $order.isOrderable}
		| <a href="{link controller=checkout returnPath=true}" class="checkout">{t _checkout}</a>	
	{/if}

	{if $user.ID > 0}	
		<div class="logout"><a href="{link controller=user action=logout}">{t _sign_out}</a></div>
	{/if}


</div>