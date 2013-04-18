{if 'DISPLAY_PRICES'|config && $product.type != 3} {* Product::TYPE_RECURRING = 3 *}
	<p id="productPrice">
		<span class="param">{t _price}:</span>
		<span class="price">
			{include file="product/block/productPagePrice.tpl"}
		</span>
		{include file="product/block/stockWarning.tpl"}
	</p>
{/if}
