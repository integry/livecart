<fieldset class="container" style="position: relative;">

	{include file="product/block/smallImage.tpl"}

	<div class="descr">

		<div class="pricingInfo"><div><div>
			{include file="product/block/cartButton.tpl"}

			{if 'DISPLAY_PRICES'|config}
				<span>{t _our_price}:</span>
				{include file="product/block/productPrice.tpl"}
			{/if}

			<br class="clear" />
		</div></div></div>

		<div class="title">
			<a href="{productUrl product=$product filterChainHandle=$filterChainHandle}">{$product.name_lang}</a>
		</div>

		{if $product.attributes}
			{include file="category/block/productListAttributes.tpl"}
		{/if}

		<div class="shortDescr">
			{$product.shortDescription_lang}
		</div>

		<div class="order">
			<div class="orderingControls">
				{assign var=sep value=false}
				{if $product.rating && 'ENABLE_RATINGS'|config}
					{include file="category/productListRating.tpl"}
					{assign var=sep value=true}
				{/if}

				{if 'ENABLE_WISHLISTS'|config}
					{if $sep}<span class="listItemSeparator">|</span>{/if}
					<a href="{link controller=order action=addToWishList id=$product.ID returnPath=true}" rel="nofollow" class="addToWishList">{t _add_to_wishlist}</a>
					{assign var=sep value=false}
				{/if}

				{if 'ENABLE_PRODUCT_COMPARE'|config}
					{if $sep}<span class="listItemSeparator">|</span>{/if}
					<span class="listItemSeparator">|</span>
					{include file="compare/block/compareLink.tpl"}
				{/if}
			</div>
		</div>

	</div>

</fieldset>