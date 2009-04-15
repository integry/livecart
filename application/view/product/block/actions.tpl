<tr id="productToWishList">
	<td class="param"></td>
	<td class="value cartLinks addToWishList">
		{if 'ENABLE_WISHLISTS'|config}
			<a href="{link controller=order action=addToWishList id=$product.ID query="return=`$catRoute`"}" rel="nofollow">{t _add_to_wishlist}</a>
		{/if}

		{if 'ENABLE_PRODUCT_COMPARE'|config}
			<div class="compare">
				{include file="compare/block/compareLink.tpl"}
			</div>
		{/if}
	</td>
</tr>