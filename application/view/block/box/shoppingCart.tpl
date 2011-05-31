<div id="smallCart">

	<span class="menuItem yourAccount">
		<a href="{link controller=user action=index}" class="menu_yourAccount">{t _your_account}</a>
	</span>

	{if 'ENABLE_CART'|config}
		<span class="sep">|</span>
		{if ($request.controller == 'product') || ($request.controller == 'category')}{assign var="returnPath" value=true}{/if}

		<span class="menuItem shoppingCart">
			<a href="{link controller=order returnPath=$returnPath}" class="menu_shoppingCart">{t _shopping_cart}</a> <span class="menu_cartItemCount" style="{if !$order.basketCount}display: none;{/if}">(<span>{maketext text="_cart_item_count" params=$order.basketCount}</span>)</span>
		</span>

		<span class="menu_isOrderable" style="{if !$order.isOrderable}display: none;{/if}">
			<span class="sep">|</span>

			<span class="menuItem checkout">
				<a href="{link controller=checkout returnPath=true}" class="checkout">{t _checkout}</a>
			</span>
		</span>
	{/if}

	{if $user.ID > 0}
		<div class="logout">
			<span class="menuItem yourAccount">
				<a href="{link controller=user action=logout}">{t _sign_out}</a>
			</span>
		</div>
	{/if}

</div>

<script type="text/javascript">
	Observer.add('orderSummary', Frontend.SmallCart, 'smallCart');
</script>