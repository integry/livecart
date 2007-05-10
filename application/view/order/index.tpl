{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>Your Shopping Basket</h1>
	
	<p id="cartStats" style="display: none;">
		{maketext text="There are [quant,_1,item,items,no items] in your shopping basket." params=$cart.basketCount}
	</p>
	
	{if !$cart.cartItems && !$cart.wishListItems}
		{t Your shopping basket is empty}. <a href="{link route=$return}">{t Continue shopping}</a>.
	{else}
		
		{if $cart.cartItems}		
		{form action="controller=order action=update" method="POST" handle=$form}
		<table id="cart">
			<thead>
				<tr>
					<th colspan="2" class="cartListTitle">
						<h2 style="margin: 0;">Shopping Cart items - to buy now</h2>
					</th>
					<th class="cartPrice">{t Price}</th>
					<th class="cartQuant">{t Quantity}</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$cart.cartItems item="item"}
				<tr>
					<td class="cartControl">
						<a href="{link controller=order action=moveToWishList id=$item.ID query="return=`$return`"}">{t Move to Wish List}</a>
						<a href="{link controller=order action=delete id=$item.ID query="return=`$return`"}">{t Remove}</a>
					</td>
					<td class="cartName">
						<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
						{$item.Product.Category}
					</td>
					<td class="cartPrice">
						{$item.formattedSubTotal.$currency}
						{if $item.count != 1}
							<div class="subTotalCalc">
								{$item.count} x {$item.Product.formattedPrice.$currency}
							</div>
						{/if}
					</td>
					<td class="cartQuant">
						{textfield name="item_`$item.ID`" class="text"}
					</td>
				</tr>	
			{/foreach}
    				<tr>
    				    <td colspan="2" class="subTotalCaption">{t _subtotal}:</td>
    				    <td class="subTotal">{$orderTotal}</td>
    				    <td><input type="submit" class="submit" value="{tn Update}" /></td>
    				</tr>

				<tr>
					<td colspan="3"></td>
					<td class="cartQuant"></td>
				</tr>
				<tr>
					<td colspan="4" style="text-align: right;">
						<a href="{link route=$return}" style="float: left;">&lt;&lt; Continue Shopping</a>
						<a href="{link controller=checkout}" style="float: right; font-weight: bold;">Proceed to Checkout &gt;&gt;</a>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="return" value="{$return}" />	
		{/form}
        {/if}
	
		{if $cart.wishListItems}		
			<table id="wishList">
				<thead>
					<tr>
						<th colspan="2" class="cartListTitle">
							<h2>Wish List items - to buy later</h2>
						</th>
						<th class="cartPrice">{t Price}</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$cart.wishListItems item="item"}
					<tr>
						<td class="cartControl">
							<a href="{link controller=order action=moveToCart id=$item.ID query="return=`$return`"}">{t Move to Cart}</a>
							<a href="{link controller=order action=delete id=$item.ID query="return=`$return`"}">{t Remove}</a>
						</td>
						<td class="cartName">
							<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
						</td>
						<td class="cartPrice">
							{$item.Product.formattedPrice.$currency}
						</td>
					</tr>				
				{/foreach}
				</tbody>
			</table>
		{/if}
	{/if}
</div>

{include file="layout/frontend/footer.tpl"}