<div style="width: 150px; font-size: smaller;" class="smallCart">

	There are <strong>{$order.basketCount}</strong> items
	{if $order.basketCount > 0}
	 (<strong>{$order.formattedTotal.$currency}</strong>)
	{/if}
	<br /> in your <a href="{link controller=order returnPath=true}">shopping cart</a>
	
	<div style="margin-top: 6px;" class="checkout">
	{if $order.basketCount > 0}
		<a href="{link controller=checkout returnPath=true}">{t Complete Purchase}</a>
	{/if}
	</div>

</div>