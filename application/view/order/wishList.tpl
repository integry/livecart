<div class="clear"></div>
<h2>{t _wish_list_items}</h2>
<fieldset class="container" id="wishList">
<table>
	<thead>
		<tr>
			<th colspan="3" class="cartListTitle"></th>
			<th class="cartPrice">{t _price}</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$cart.wishListItems item="item" name="wishList"}
		<tr class="{zebra loop="wishList"}">
			<td class="cartControl">
				<a href="{link controller=order action=moveToCart id=$item.ID query="return=`$return`"}">{t _move_to_cart}</a>
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
				<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
			</td>
			<td class="cartPrice">
				{$item.Product.formattedPrice.$currency}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>
<a href="{link route=$return}" class="continueShopping">{t _continue_shopping}</a>
</fieldset>