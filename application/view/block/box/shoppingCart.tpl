<div id="smallCart">

	<div class="btn-toolbar pull-right">

	<div class="btn-group">
		<a class="btn dropdown-toggle" data-toggle="dropdown disabled" href="{link controller=user action=index}">
			<span class="glyphicon glyphicon-user"></span>
			{t _your_account}
			<span class="caret"></span>
		</a>

		<ul class="dropdown-menu">
			{if $user.ID > 0}
				<li class="logout">
					<a href="{link controller=user action=logout}">{t _sign_out}</a>
				</li>
			{/if}
		</ul>
	</div>

	{if 'ENABLE_CART'|config}
		{if ($request.controller == 'product') || ($request.controller == 'category')}{assign var="returnPath" value=true}{/if}

		<div class="btn-group">
			<a class="btn dropdown-toggle" data-toggle="dropdown disabled" href="{link controller=order returnPath=$returnPath}">
				<span class="glyphicon glyphicon glyphicon-shopping-cart"></span>
				{t _shopping_cart}
				<span class="badge menu_cartItemCount" style="{if !$order.basketCount}display: none;{/if}">{maketext text="_cart_item_count" params=$order.basketCount}</span>
				<span class="caret"></span>
			</a>

			<ul class="dropdown-menu">
				<li class="checkout">
					<a href="{link controller=checkout returnPath=true}" class="checkout">{t _checkout}</a>
				</li>
			</ul>
		</div>
	{/if}
	</div>

</div>

<script type="text/javascript">
	Observer.add('orderSummary', Frontend.SmallCart, 'smallCart');
</script>