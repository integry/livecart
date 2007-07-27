<h2>Wish List items - to buy later</h2>
<table id="wishList">
	<thead>
		<tr>
			<th colspan="3" class="cartListTitle"></th>
			<th class="cartPrice">{t Price}</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$cart.wishListItems item="item" name="wishList"}
		<tr {zebra loop="wishList"}>
			<td class="cartControl">
				<a href="{link controller=order action=moveToCart id=$item.ID query="return=`$return`"}">{t Move to Cart}</a>
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
				<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
			</td>
			<td class="cartPrice">
				{$item.Product.formattedPrice.$currency}
			</td>
		</tr>				
	{/foreach}
	</tbody>
</table>
<a href="{link route=$return}" class="continueShopping">Continue Shopping</a>