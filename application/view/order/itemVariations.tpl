{% if $item.Product.variations %}
	<p class="variations">
		[[ partial("order/itemVariationsList.tpl") ]]
	</p>
{% endif %}
