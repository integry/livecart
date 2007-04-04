{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>My Shopping Basket</h1>
	
	<p id="cartStats" style="display: none;">
		{maketext text="There are [quant,_1,item,items,no items] in your shopping basket." params=$cart.basketCount}
	</p>
	
	{if !$cart.cartItems && !$cart.wishListItems}
		{t Your shopping basket is empty}. <a href="{link route=$return}">{t Continue shopping}</a>.
	{else}
		<p>
			<a href="{link route=$return}">{t Continue shopping}</a> or <a href="{link controller=checkout}">{t Checkout}</a>	
		</p>
		
		{if $cart.cartItems}		
		{form action="controller=order action=update" method="POST" handle=$form}
		<table id="cart">
			<thead>
				<tr>
					<th colspan="2" class="cartListTitle">
						<h2>Shopping Cart items - to buy now</h2>
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
					</td>
					<td class="cartPrice">
						{$item.Product.formattedPrice.$currency}
					</td>
					<td class="cartQuant">
						{textfield name="item_`$item.ID`" class="text"}
					</td>
				</tr>	
			{/foreach}
    				<tr>
    				    <td colspan="2"></td>
    				    <td>{$orderTotal}</td>
    				    <td></td>
    				</tr>

				<tr>
					<td colspan="3"></td>
					<td class="cartQuant"><input type="submit" value="{tn Update}" />
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