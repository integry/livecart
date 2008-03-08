
<div id="smallCart">

	<div class="top_upper">
	<a href="{link controller=user action=index}"  class="account_top"> {t _your_account}</a>
	&nbsp;&nbsp;&nbsp; <a href="{link controller=order returnPath=true}" class="cart_top">{t _shopping_cart}</a> (<strong>{$order.basketCount}</strong> items)
	{if $order.isOrderable}
		&nbsp;&nbsp;&nbsp; <a href="{link controller=checkout returnPath=true}" class="checkout">{t _checkout}</a>	
	{/if}

	{if $user.ID > 0}	
		 &nbsp;&nbsp;&nbsp; <a href="{link controller=user action=logout}" class="signout">{t _sign_out}</a> 
	{/if}
	</div>


</div>