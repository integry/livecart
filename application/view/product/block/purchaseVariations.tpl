{% if $samePrice %}
	<p>
		<label>{t _price}</label>
		<span class="price">{$product.formattedPrice.$currency}</span>
	</p>

	{% if $quantityPricing %}
		[[ partial("product/block/quantityPrice.tpl") ]]
	{% endif %}
{% endif %}

{% set parentProduct = $product %}
{form action="order/addToCart" handle=$cartForm method="POST" class="purchaseVariations" class="form-horizontal"}
	{foreach $variations.products as $product}
		<h3>{$product.variationNames|@implode:' / '}</h3>

		{% if !$samePrice %}
			<p>
				<label>{t _price}</label>
				<span class="price">{$product.finalFormattedPrice.$currency}</span>
			</p>

			{% if $quantityPricing %}
				[[ partial("product/block/quantityPrice.tpl") ]]
			{% endif %}
		{% endif %}

		<p>
			<label>{t _quantity}</label>
			[[ partial('product/block/quantity.tpl', ['field': "product_`$product.ID`_count", 'quantity': quantities[$product.ID]]) ]]
		</p>

		{assign var="optionPrefix" value="product_`$product.ID`_"}
		{block PRODUCT-OPTIONS}
		<input type="hidden" name="productIDs[]" value="[[product.ID]]" />
	{/foreach}

	<div id="productToCart" class="cartLinks">
		[[ partial('block/submit.tpl', ['caption': "_add_to_cart"]) ]]
		{hidden name="return" value=$catRoute}
	</div>

{/form}

{% set product = $parentProduct %}
{block PRODUCT-OVERVIEW}