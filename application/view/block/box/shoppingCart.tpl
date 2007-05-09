<div style="width: 150px; font-size: smaller;" class="smallCart">

	There are <strong>{$order.basketCount}</strong> items in your <a href="{link controller=order returnPath=true}">shopping cart</a>
	
	<div style="margin-top: 6px; text-align: right;" class="checkout">
	{if $order.basketCount > 0}
		<a href="{link controller=checkout returnPath=true}">{t Checkout}</a> <img src="image/silk/cart_go.png" style="vertical-align: middle;" />
	{/if}
	</div>

</div>