{% if empty(layout) %}
	{% set layout=config('LIST_LAYOUT') %}
{% endif %}

{% if 'GRID' == layout %}
	[[ partial('category/productGrid.tpl', ['products': products]) ]]
{% elseif layout == 'TABLE' %}
	[[ partial('category/productTable.tpl', ['products': products]) ]]
{% else %}
	[[ partial('category/productList.tpl', ['products': products]) ]]
{% endif %}
