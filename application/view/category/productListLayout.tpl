{% if empty(layout) %}
	{assign var=layout value='LIST_LAYOUT'|config}
{% endif %}

{% if 'GRID' == $layout %}
	[[ partial('category/productGrid.tpl', ['products': products]) ]]
{% elseif $layout == 'TABLE' %}
	[[ partial('category/productTable.tpl', ['products': products]) ]]
{% else %}
	[[ partial('category/productList.tpl', ['products': products]) ]]
{% endif %}
