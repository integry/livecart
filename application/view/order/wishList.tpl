<h2>{t _wish_list_items}</h2>
<div id="wishList">
<table>
	<thead>
		<tr>
			<th colspan="3" class="cartListTitle"></th>
			<th class="cartPrice">{t _price}</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$cart.wishListItems item="item" name="wishList"}
		<tr>
			<td class="cartControl">
				<a href="[[ url("order/moveToCart/" ~ item.ID, "return=`$return`") ]]">{t _move_to_cart}</a>
				<a href="[[ url("order/delete/" ~ item.ID, "return=`$return`") ]]">{t _remove}</a>
			</td>
			<td class="cartImage">
				{% if $item.Product.DefaultImage.urls.1 %}
				<a href="{productUrl product=$item.Product}">
					{img src=$item.Product.DefaultImage.urls.1 alt=$item.Product.name()|escape}
				</a>
				{% endif %}
			</td>
			<td class="cartName">
				<a href="{productUrl product=$item.Product}">[[item.Product.name()]]</a>
			</td>
			<td class="cartPrice">
				{$item.Product.formattedPrice.$currency}
			</td>
		</tr>
	{/foreach}
	</tbody>
</table>
<a href="[[ url(return) ]]" class="continueShopping">{t _continue_shopping}</a>
</div>