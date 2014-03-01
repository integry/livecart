<ul class="compactProductList">
	{% for product in products %}
		<li>
			[[ partial("block/box/menuProductListItem.tpl") ]]
		</li>
	{% endfor %}
</ul>