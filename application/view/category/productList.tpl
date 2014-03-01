<div class="productList">
	{foreach from=products item=product name="productList"}
		[[ partial("category/productListItem.tpl") ]]
	{% endfor %}
</div>
