{form action="controller=order action=update" method="POST" handle=$form id="cartItems"}
<h2>{t _cart_items}</h2>
<table id="cart">
	<thead>
		<tr>
			<th colspan="3" class="cartListTitle"></th>
			<th class="cartPrice">{t _price}</th>
			<th class="cartQuant">{t _quantity}</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$cart.cartItems item="item" name="cart"}
		<tr class="{zebra loop="cart"}{if $smarty.foreach.cart.first} first{/if}{if $smarty.foreach.cart.last} last{/if}">
			<td class="cartControl">
				{if 'ENABLE_WISHLISTS'|config}
					<a href="{link controller=order action=moveToWishList id=$item.ID query="return=`$return`"}">{t _move_to_wishlist}</a>
				{/if}
				<a href="{link controller=order action=delete id=$item.ID query="return=`$return`"}">{t _remove}</a>
			</td>

			<td class="cartImage">
				{if $item.Product.DefaultImage.paths.1}
				<a href="{productUrl product=$item.Product}">
				   {img src=$item.Product.DefaultImage.paths.1 alt=$item.Product.name_lang|escape}
				</a>
				{/if}
			</td>

			<td class="cartName">
				<div>
					<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
					<small>({$item.Product.Category.name_lang})</small>
				</div>
			</td>
			<td class="cartPrice">
				{$item.formattedSubTotal}
				{if $item.count != 1}
					<div class="subTotalCalc">
						{$item.count} x {$item.formattedPrice}
					</div>
				{/if}
			</td>
			<td class="cartQuant">
				{textfield name="item_`$item.ID`" class="text"}
			</td>
		</tr>
	{/foreach}
			<tr>
				<td colspan="3" class="subTotalCaption">{t _subtotal}:</td>
				<td class="subTotal">{$orderTotal}</td>
				<td id="cartUpdate"><input type="submit" class="submit" value="{tn _update}" /></td>
			</tr>

		<tr>
			<td colspan="4"></td>
			<td class="cartQuant"></td>
		</tr>
		<tr>
			<td colspan="5">
				<a href="{link route=$return}" class="continueShopping">{t _continue_shopping}</a>
				{if $order.isOrderable}
					<a href="{link controller=checkout}" class="proceedToCheckout"><span><span><span><span>{t _proceed_checkout}</span></span></span></span></a>
				{/if}
			</td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="return" value="{$return}" />
{/form}

{if $expressMethods && $order.isOrderable}
	<div id="expressCheckoutMethods">
		{foreach from=$expressMethods item=method}
			<a href="{link controller=checkout action=express id=$method}"><img src="image/payment/{$method}.gif" /></a>
		{/foreach}
	</div>
{/if}