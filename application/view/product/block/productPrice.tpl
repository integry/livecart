{% if 'DISPLAY_PRICES'|config %}
<span class="price">
	{$product.formattedPrice.$currency}
	{% if $product.formattedListPrice.$currency %}
		<span class="listPrice">
			{$product.formattedListPrice.$currency}
		</span>
	{% endif %}
</span>
{% endif %}
