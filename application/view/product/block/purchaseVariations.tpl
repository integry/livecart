{assign var="parentProduct" value=$product}
{form action="controller=order action=addToCart" handle=$cartForm method="POST" class="purchaseVariations"}
	{foreach $variations.products as $product}
		<h3>{$product.variationNames|@implode:' / '}</h3>
		<p>
			<label>{t _price}</label>
			<span class="price">{$product.finalFormattedPrice.$currency}</span>
		</p>

		{if $quantityPricing}
			{include file="product/block/quantityPrice.tpl"}
		{/if}

		<p>
			<label>{t _quantity}</label>
			{include file="product/block/quantity.tpl" field="product_`$product.ID`_count" quantity=$quantities[$product.ID]}
		</p>

		{assign var="optionPrefix" value="product_`$product.ID`_"}
		{block PRODUCT-OPTIONS}
		<input type="hidden" name="productIDs[]" value="{$product.ID}" />
	{/foreach}

	<div id="productToCart" class="cartLinks">
		<label></label>
		<input type="submit" class="submit" value="{tn _add_to_cart}" />
		{hidden name="return" value=$catRoute}
	</div>

{/form}

{assign var="product" value=$parentProduct}
{block PRODUCT-OVERVIEW}