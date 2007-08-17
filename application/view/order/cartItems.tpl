{form action="controller=order action=update" method="POST" handle=$form}
<h2 style="margin: 0;">Shopping Cart items - to buy now</h2>
<table id="cart">
	<thead>
		<tr>
			<th colspan="3" class="cartListTitle"></th>
			<th class="cartPrice">{t Price}</th>
			<th class="cartQuant">{t Quantity}</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$cart.cartItems item="item" name="cart"}
		<tr {zebra loop="cart"}>
			<td class="cartControl">
				<a href="{link controller=order action=moveToWishList id=$item.ID query="return=`$return`"}">{t Move to Wish List}</a>
				<a href="{link controller=order action=delete id=$item.ID query="return=`$return`"}">{t Remove}</a>
			</td>
			
			<td class="cartImage">
			    {if $item.Product.DefaultImage.paths.1}
                <a href="{productUrl product=$item.Product}">
			       {img src=$item.Product.DefaultImage.paths.1 alt=$item.Product.name_lang|escape}	  
                </a>
                {/if}
            </td>
			
			<td class="cartName">
				
				<div style="float: left;">
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
			    <td id="cartUpdate"><input type="submit" class="submit" value="{tn Update}" /></td>
			</tr>

		<tr>
			<td colspan="4"></td>
			<td class="cartQuant"></td>
		</tr>
		<tr>
			<td colspan="5" style="text-align: right;">
				<a href="{link route=$return}" class="continueShopping">Continue Shopping</a>
				<a href="{link controller=checkout}" class="proceedToCheckout">Proceed to Checkout</a>
			</td>
		</tr>
	</tbody>
</table>
<input type="hidden" name="return" value="{$return}" />	
{/form}

{if $expressMethods}
    <div id="expressCheckoutMethods" style="padding: 20px; text-align: right;">
        {foreach from=$expressMethods item=method}
            <a href="{link controller=checkout action=express id=$method}"><img src="image/payment/{$method}.gif" /></a>
        {/foreach}
    </div>
{/if}