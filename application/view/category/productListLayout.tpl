{% if empty(layout) %}
	{% set layout=config('LIST_LAYOUT') %}
{% endif %}

{% if 'GRID' == layout %}
	[[ partial('category/productGrid.tpl') ]]
{% elseif layout == 'TABLE' %}
	[[ partial('category/productTable.tpl') ]]
{% else %}
	[[ partial('category/productList.tpl') ]]
{% endif %}
