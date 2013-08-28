{if $samePrice}
	<p>
		<label>{t _price}</label>
		<span class="price">{$product.formattedPrice.$currency}</span>
	</p>

	{if $quantityPricing}
		{include file="product/block/quantityPrice.tpl"}
	{/if}
{/if}

{% set parentProduct = $product %}
{form action="controller=order action=addToCart" handle=$cartForm method="POST" class="purchaseVariations" class="form-horizontal"}
	{foreach $variations.products as $product}
		<h3>{$product.variationNames|@implode:' / '}</h3>

		{if !$samePrice}
			<p>
				<label>{t _price}</label>
				<span class="price">{$product.finalFormattedPrice.$currency}</span>
			</p>

			{if $quantityPricing}
				{include file="product/block/quantityPrice.tpl"}
			{/if}
		{/if}

		<p>
			<label>{t _quantity}</label>
			{include file="product/block/quantity.tpl" field="product_`$product.ID`_count" quantity=$quantities[$product.ID]}
		</p>

		{assign var="optionPrefix" value="product_`$product.ID`_"}
		{block PRODUCT-OPTIONS}
		<input type="hidden" name="productIDs[]" value="[[product.ID]]" />
	{/foreach}

	<div id="productToCart" class="cartLinks">
		{include file="block/submit.tpl" caption="_add_to_cart"}
		{hidden name="return" value=$catRoute}
	</div>

{/form}

{% set product = $parentProduct %}
{block PRODUCT-OVERVIEW}