<li>
	{if $order.basketCount}
		{include file="order/miniCartBlock.tpl" hidePanel=true}
	{else}
		<div id="miniCart" class="cartEmpty">
			<p>{t _empty_basket}</p>
		</div>
	{/if}
</li>
