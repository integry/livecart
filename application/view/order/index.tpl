{loadJs form=true}
{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right orderIndex">
	
    <div class="checkoutHeader">
    	<h1>Your Shopping Basket</h1>
    	
    	{if $cart.cartItems}
        	{include file="checkout/checkoutProgress.tpl" progress="progressCart"}
        {/if}
    </div>
	
	<p id="cartStats" style="display: none;">
		{maketext text="There are [quant,_1,item,items,no items] in your shopping basket." params=$cart.basketCount}
	</p>
	
	{if !$cart.cartItems && !$cart.wishListItems}
		{t Your shopping basket is empty}. <a href="{link route=$return}">{t Continue shopping}</a>.
	{else}
		
	{if $cart.cartItems}			
		{include file="order/cartItems.tpl"}
    {/if}
	
	{if $cart.wishListItems}		
		{include file="order/wishList.tpl"}
	{/if}
	
	{/if}
	
	<div class="clear"></div>
	
</div>

{include file="layout/frontend/footer.tpl"}