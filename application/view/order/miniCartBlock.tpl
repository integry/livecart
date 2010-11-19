<div id="miniCart">
	{if $order.basketCount}
	<div class="box miniCart">
		<div class="title"><div><a href="{link controller=order}">{t _shopping_cart}</a></div></div>
		<div class="content">
			<ul id="miniCartContents">
			{foreach from=$order.cartItems item="item" name="cart"}
				<li><span class="miniCartCount">{$item.count}</span> x <a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a></li>
			{/foreach}
			</ul>

			<div class="miniCartTotal">
				<div>{t _total}: <span class="miniCartTotalAmount">{$order.formattedTotal[$order.Currency.ID]}</span></div>
				<a href="{link controller=checkout}" class="checkout">{t _checkout}</a>
			</div>
		</div>
	</div>
	{/if}
</div>

<script type="text/javascript">
	Observer.add('miniCart', Frontend.MiniCart, 'miniCart');
</script>