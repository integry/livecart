{% if config('DISPLAY_PRICES') %}
<span class="price">
	[[ product.getFormattedPrice(currency) ]]
	
	{#
	{% if product.formattedListPrice.currency %}
		<span class="listPrice">
			{product.formattedListPrice.currency}
		</span>
	{% endif %}
	#}
</span>
{% endif %}
