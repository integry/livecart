{% if config('DISPLAY_PRICES') %}
<span class="price">
	[[ product.getFormattedPrice(currency) ]]
	
	{% if product.getListPrice(currency) %}
		<span class="listPrice">
			[[ product.getFormattedListPrice(currency) ]]
		</span>
	{% endif %}
</span>
{% endif %}
