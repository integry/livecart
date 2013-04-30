{capture assign="body"}
	{include file="order/changeMessages.tpl"}

	{if !$cart.cartItems}
		<div class="emptyBasket">
			{t _empty_basket}. <a href="{link route=$return}">{t _continue_shopping}</a>.
		</div>
	{else}
		{include file="order/cartItems.tpl" hideNav=true}
	{/if}
{/capture}

{capture assign="footer"}
	{include file="order/block/navigationButtons.tpl" hideTos=true}
{/capture}

{include file="block/modal.tpl" title="_your_basket" body=$body footer=$footer}
