<div id="smallCart">

	<a href="{link controller=user action=index}" class="menu_yourAccount">{t _your_account}</a>
	<span class="sep">|</span>
	<a href="{link controller=order returnPath=true}" class="menu_shoppingCart">{t _shopping_cart}</a> <span class="menu_cartItemCount">(<strong>{$order.basketCount}</strong> items)</span>
	{if $order.isOrderable}
		<span class="sep">|</span> <a href="{link controller=checkout returnPath=true}" class="checkout">{t _checkout}</a>
	{/if}

	{if $user.ID > 0}
		<div class="logout"><a href="{link controller=user action=logout}">{t _sign_out}</a></div>
	{/if}


</div>