<div class="image">
	<a href="{productUrl product=$product}">
	{% if $product.DefaultImage.urls.1 %}
		{img src=$product.DefaultImage.urls.1 alt=$product.name_lang|escape}
	{% else %}
		{img src='MISSING_IMG_THUMB'|config alt=$product.name_lang|escape}
	{% endif %}
	</a>
</div>

<div class="productInfo">
	{% if !empty(productInfoTemplate) %}
		[[ partial(productInfoTemplate) ]]
	{% endif %}
	<a href="{productUrl product=$product}" class="productName">[[product.name_lang]]</a>
</div>

<div class="pricingInfo">
	<span class="price">
		{$product.formattedPrice.$currency}
		{% if $product.formattedListPrice.$currency %}
				<span class="listPrice">
					{$product.formattedListPrice.$currency}
				</span>
		{% endif %}
	</span>
</div>

<div class="clear"></div>
