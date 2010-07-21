<div id="smallCart">

	<a href="{link controller=user action=index}" class="menu_yourAccount">{t _your_account}</a>

	{if 'ENABLE_CART'|config}
		<span class="sep">|</span>
		{if ($request.controller == 'product') || ($request.controller == 'category')}{assign var="returnPath" value=true}{/if}

		{if !'SKIP_CART'|config}
			<a href="{link controller=order returnPath=$returnPath}" class="menu_shoppingCart">{t _shopping_cart}</a> <span class="menu_cartItemCount" style="{if !$order.basketCount}display: none;{/if}">(<span>{maketext text="_cart_item_count" params=$order.basketCount}</span>)</span>
		{/if}

		<span class="menu_isOrderable" style="{if !$order.isOrderable}display: none;{/if}">
			<span class="sep">|</span> <a href="{link controller=checkout returnPath=true}" class="checkout">{t _checkout}</a>
		</span>
	{/if}

	{if $user.ID > 0}
		<div class="logout"><a href="{link controller=user action=logout}">{t _sign_out}</a></div>
	{/if}

</div>

<script type="text/javascript">
	Observer.add('orderSummary', Frontend.SmallCart, 'smallCart');
</script>