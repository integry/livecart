{if 'DISPLAY_PRICES'|config && $product.type != 3} {* Product::TYPE_RECURRING = 3 *}
	<p id="productPrice" class="valign">
		<span class="param">{t _price}:</span>
		{include file="product/block/productPagePrice.tpl"}
		{include file="product/block/stockWarning.tpl"}
	</p>
{/if}
