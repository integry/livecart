{foreach from=$cart.cartItems item="item" name="cart"}
	<tr class="{zebra loop="cart"}">
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

		{if 'SHOW_SKU_CART'|config}
			<td>{$item.Product.sku|escape}</td>
		{/if}

		<td class="cartName">
			<div>
				{if $item.Product.ID}
					<a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a>
				{else}
					<span>{$item.Product.name_lang}</span>
				{/if}
				<small class="categoryName">(&rlm;{$item.Product.Category.name_lang})</small>
			</div>

			{include file="order/itemVariations.tpl"}
			{include file="order/block/itemOptions.tpl"}
			
			{include file="order/block/recurringItem.tpl"}

			{if $multi}
				{include file="order/selectItemAddress.tpl" item=$item}
			{/if}
		</td>

		<td class="cartPrice {if $item.itemBasePrice != $item.itemPrice}discount{/if}">
			{if $item.count == 1}
				<span class="basePrice">{$item.formattedBasePrice}</span><span class="actualPrice">{$item.formattedPrice}</span>
			{else}
				{$item.formattedDisplaySubTotal}
				<div class="subTotalCalc">
					<span class="itemCount">
						{$item.count} x
					</span>
					<span class="basePrice">{$item.formattedBasePrice}</span><span class="actualPrice">{$item.formattedPrice}</span>
				</div>
			{/if}
		</td>

		<td class="cartQuant">
			{textfield name="item_`$item.ID`" class="text"}
		</td>
	</tr>
{/foreach}
